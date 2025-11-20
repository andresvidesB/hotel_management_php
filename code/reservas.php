<?php
// 1. Cargar Autoload y Controladores
require __DIR__ . '/vendor/autoload.php';

use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;
use Src\Users\Infrastructure\Services\UsersController;
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Statuses\Infrastructure\Services\StatusesController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Roles\Infrastructure\Services\RolesController;
use Src\Guests\Infrastructure\Services\GuestsController;
use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;

$message = null;
$error = null;

// 2. Cargar datos para los Dropdowns (Bloque Robusto)
$usuarios = [];
$habitaciones = [];
$estados = [];
$roles = [];

try {
    $usuarios = UsersController::getUsers(); 
} catch (Exception $e) { $error .= "Err Users: " . $e->getMessage(); }

try {
    $habitaciones = RoomsController::getRooms();
} catch (Exception $e) { $error .= "Err Rooms: " . $e->getMessage(); }

try {
    $estados = StatusesController::getStatuses();
} catch (Exception $e) { $error .= "Err Status: " . $e->getMessage(); }

try {
    $roles = RolesController::getRoles(); 
} catch (Exception $e) { }


// 3. MANEJO DE ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- CREAR NUEVA RESERVA ---
        if ($_POST['action'] === 'add_reservation') {
            
            $userId = '';
            $roomId = $_POST['room_id'] ?? '';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $statusId = $_POST['status_id'] ?? '';

            // Validaciones Básicas
            if (empty($roomId) || empty($startDate) || empty($endDate)) {
                throw new Exception("Habitación y fechas son obligatorias.");
            }
            if ($startDate >= $endDate) {
                throw new Exception("La fecha de salida debe ser posterior a la entrada.");
            }

            // Validación de Disponibilidad
            $disponible = ReservationRoomsController::isRoomAvailable($roomId, $startDate, $endDate);
            if (!$disponible) {
                throw new Exception("🚫 La habitación seleccionada YA ESTÁ OCUPADA en esas fechas.");
            }
            
            // A. LÓGICA DE CLIENTE: ¿Existente o Nuevo?
            if (isset($_POST['client_type']) && $_POST['client_type'] === 'new') {
                // --- CREAR CLIENTE NUEVO ---
                $newNombre = trim($_POST['new_nombre'] ?? '');
                $newApellido = trim($_POST['new_apellido'] ?? '');
                $newCC = trim($_POST['new_cc'] ?? '');
                $newEmail = trim($_POST['new_email'] ?? '');
                
                if(empty($newNombre) || empty($newApellido)) {
                    throw new Exception("Nombre y Apellido son obligatorios para nuevos clientes.");
                }

                $idCreator = new UuidIdentifierCreator();
                $userId = $idCreator->createIdentifier()->getValue();

                // 1. Crear Usuario (Login)
                UsersController::addUser([
                    'user_id_person' => $userId,
                    'user_password' => '12345', // Default password
                    'user_role_id' => '3' // Rol Cliente
                ]);
                
                // 2. Guardar Datos Personales
                UsersController::savePersonData($userId, $newNombre, $newApellido, $newEmail);

                // 3. Crear Registro de Huésped (si tiene documento)
                $docNum = !empty($newCC) ? $newCC : 'S/D';
                GuestsController::addGuest([
                    'guest_id_person' => $userId,
                    'guest_document_type' => 'CC',
                    'guest_document_number' => $docNum,
                    'guest_country' => 'Desconocido'
                ]);
                
            } else {
                // --- CLIENTE EXISTENTE ---
                $userId = $_POST['user_id'] ?? '';
                if (empty($userId)) throw new Exception("Debe seleccionar un cliente.");

                // Asegurar que exista como Huésped también (para la relación)
                $huespedExistente = GuestsController::getGuestById($userId);
                if (empty($huespedExistente)) {
                    GuestsController::addGuest([
                        'guest_id_person' => $userId,
                        'guest_document_type' => 'CC',
                        'guest_document_number' => 'S/D',
                        'guest_country' => 'Desconocido'
                    ]);
                }
            }

            // B. CREAR RESERVA
            $idCreator = new UuidIdentifierCreator();
            $tempId = $idCreator->createIdentifier()->getValue();

            $reservaIdentifier = ReservationsController::addReservation([
                'reservation_id' => $tempId, 
                'reservation_source' => 'Recepción',
                'reservation_user_id' => $userId,
                'reservation_created_at' => time()
            ]);
            $reservaId = $reservaIdentifier->getValue();

            // C. ASIGNAR HABITACIÓN
            ReservationRoomsController::addReservationRoom([
                'reservation_room_reservation_id' => $reservaId,
                'reservation_room_room_id' => $roomId,
                'reservation_room_start_date' => $startDate,
                'reservation_room_end_date' => $endDate
            ]);

            // D. ASIGNAR ESTADO
            if (empty($statusId)) {
                // Buscar ID de 'Confirmada'
                foreach($estados as $s) { if (stripos($s['status_name'], 'Confirm') !== false) $statusId = $s['status_id']; }
                if(empty($statusId) && !empty($estados)) $statusId = $estados[0]['status_id'];
            }
            
            if (!empty($statusId)) {
                ReservationStatusController::addReservationStatus([
                    'reservation_status_reservation_id' => $reservaId,
                    'reservation_status_status_id' => $statusId,
                    'reservation_status_changed_at' => time()
                ]);
            }

            // E. VINCULAR HUÉSPED A LA RESERVA
            ReservationGuestsController::addReservationGuest([
                'reservation_guest_reservation_id' => $reservaId,
                'reservation_guest_guest_id' => $userId
            ]);

            $message = "✅ Reserva creada exitosamente.";
        }

        // --- EDITAR RESERVA ---
        elseif ($_POST['action'] === 'edit_reservation') {
            $reservaId = $_POST['reservation_id'];
            $roomId = $_POST['room_id'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $statusId = $_POST['status_id'];

            if ($startDate >= $endDate) throw new Exception("Fechas inválidas.");

            // Validar Disponibilidad (si no es cancelación)
            $esCancelacion = false;
            foreach($estados as $s) {
                if ($s['status_id'] == $statusId && stripos($s['status_name'], 'Cancel') !== false) $esCancelacion = true;
            }

            if (!$esCancelacion) {
                $disponible = ReservationRoomsController::isRoomAvailable($roomId, $startDate, $endDate, $reservaId);
                if (!$disponible) throw new Exception("🚫 Conflicto: La habitación ya está ocupada en esas fechas.");
            }

            // Actualizar Fechas
            ReservationRoomsController::deleteReservationRoom($reservaId, $roomId);
            ReservationRoomsController::addReservationRoom([
                'reservation_room_reservation_id' => $reservaId,
                'reservation_room_room_id' => $roomId,
                'reservation_room_start_date' => $startDate,
                'reservation_room_end_date' => $endDate
            ]);

            // Actualizar Estado
            if (!empty($statusId)) {
                ReservationStatusController::addReservationStatus([
                    'reservation_status_reservation_id' => $reservaId,
                    'reservation_status_status_id' => $statusId,
                    'reservation_status_changed_at' => time()
                ]);
            }
            
            $message = "✏️ Reserva modificada correctamente.";
        }

        // --- ELIMINAR RESERVA ---
        elseif ($_POST['action'] === 'delete_reservation') {
            $id = $_POST['reservation_id'];
            ReservationsController::deleteReservation($id);
            $message = "🗑️ Reserva eliminada.";
        }

    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// 4. OBTENER LISTA DE RESERVAS (Display)
$listaReservas = [];
try {
    $reservasBase = ReservationsController::getReservations();
    
    foreach ($reservasBase as $res) {
        try {
            $id = $res['reservation_id'];
            if (empty($id)) continue;

            // Buscar Habitación
            $rooms = ReservationRoomsController::getRoomsByReservation($id);
            $roomData = !empty($rooms) ? $rooms[0] : null;
            
            $roomName = 'Sin Asignar';
            if ($roomData) {
                foreach ($habitaciones as $h) {
                    if ($h['room_id'] == $roomData['reservation_room_room_id']) {
                        $roomName = $h['room_name'];
                        break;
                    }
                }
            }

            // Buscar Cliente
            $clientName = 'Desconocido';
            $clientId = $res['reservation_user_id'];
            foreach ($usuarios as $u) {
                if ($u['user_id_person'] === $clientId) {
                    $clientName = $u['NombreCompleto'] ?? ('ID: ' . substr($clientId, 0, 5));
                    break;
                }
            }

            // Buscar Estado
            $statuses = ReservationStatusController::getStatusesByReservation($id);
            $lastStatus = !empty($statuses) ? end($statuses) : null;
            $statusName = 'Pendiente';
            $statusColor = 'secondary';
            $statusId = '';
            
            if ($lastStatus) {
                $statusId = $lastStatus['reservation_status_status_id'];
                foreach ($estados as $s) {
                    if ($s['status_id'] == $statusId) {
                        $statusName = $s['status_name'];
                        if (stripos($statusName, 'Confirm') !== false) $statusColor = 'success';
                        elseif (stripos($statusName, 'Cancel') !== false) $statusColor = 'danger';
                        elseif (stripos($statusName, 'Ocupad') !== false) $statusColor = 'primary';
                        elseif (stripos($statusName, 'Finaliz') !== false) $statusColor = 'dark';
                        break;
                    }
                }
            }

            $listaReservas[] = [
                'id' => $id,
                'cliente' => $clientName,
                'user_id' => $clientId,
                'room_id' => $roomData ? $roomData['reservation_room_room_id'] : '',
                'room_name' => $roomName,
                'start_date' => $roomData ? date('Y-m-d', $roomData['reservation_room_start_date']) : '',
                'end_date' => $roomData ? date('Y-m-d', $roomData['reservation_room_end_date']) : '',
                'status_name' => $statusName,
                'status_color' => $statusColor,
                'status_id' => $statusId
            ];

        } catch (Exception $e) { continue; }
    }
} catch (Exception $e) {
    $error = "Error al cargar listado: " . $e->getMessage();
}

require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestión de Reservas';

    function toggleClientType(type) {
        if (type === 'existing') {
            document.getElementById('existing_client_div').style.display = 'block';
            document.getElementById('new_client_div').style.display = 'none';
            document.getElementById('user_id_select').required = true;
            document.getElementById('new_nombre').required = false;
            document.getElementById('new_apellido').required = false;
        } else {
            document.getElementById('existing_client_div').style.display = 'none';
            document.getElementById('new_client_div').style.display = 'block';
            document.getElementById('user_id_select').required = false;
            document.getElementById('new_nombre').required = true;
            document.getElementById('new_apellido').required = true;
        }
    }

    function prepareEdit(id, roomId, start, end, statusId) {
        document.getElementById('edit_reservation_id').value = id;
        document.getElementById('edit_room_id').value = roomId;
        document.getElementById('edit_start_date').value = start;
        document.getElementById('edit_end_date').value = end;
        document.getElementById('edit_status_id').value = statusId;
    }

    function confirmDelete(id) {
        if(confirm('¿Eliminar esta reserva permanentemente?')) {
            document.getElementById('delete_reservation_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div class="d-none d-md-block small text-muted">
            <span class="me-2"><i class="fa-solid fa-circle text-success"></i> Confirmada</span>
            <span class="me-2"><i class="fa-solid fa-circle text-primary"></i> Ocupada</span>
            <span class="me-2"><i class="fa-solid fa-circle text-danger"></i> Cancelada</span>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd">
            <i class="fa-solid fa-plus"></i> Nueva Reserva
        </button>
    </div>
    
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID / Cliente</th>
                        <th>Habitación</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($listaReservas)): ?>
                        <tr><td colspan="6" class="text-center">No hay reservas registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($listaReservas as $res): ?>
                        <tr>
                            <td>
                                <small class="text-muted">#<?= substr($res['id'], 0, 6) ?></small><br>
                                <i class="fa-solid fa-user text-primary"></i> <strong><?= htmlspecialchars($res['cliente']) ?></strong>
                            </td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($res['room_name']) ?></span></td>
                            <td><?= $res['start_date'] ?></td>
                            <td><?= $res['end_date'] ?></td>
                            <td><span class="badge bg-<?= $res['status_color'] ?>"><?= $res['status_name'] ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" data-bs-target="#modalEdit"
                                        onclick="prepareEdit(
                                            '<?= $res['id'] ?>', 
                                            '<?= $res['room_id'] ?>',
                                            '<?= $res['start_date'] ?>', 
                                            '<?= $res['end_date'] ?>', 
                                            '<?= $res['status_id'] ?>'
                                        )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                
                                <a href="checkout.php?id=<?= $res['id'] ?>" class="btn btn-sm btn-outline-success me-1" title="Check-Out / Pagar">
                                    <i class="fa-solid fa-file-invoice-dollar"></i>
                                </a>

                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $res['id'] ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_reservation">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nueva Reserva</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 border-end">
                            <h6 class="text-primary mb-3 fw-bold">1. Datos del Cliente</h6>
                            
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="client_type" id="type_existing" value="existing" checked onclick="toggleClientType('existing')">
                                <label class="btn btn-outline-primary" for="type_existing">Existente</label>

                                <input type="radio" class="btn-check" name="client_type" id="type_new" value="new" onclick="toggleClientType('new')">
                                <label class="btn btn-outline-primary" for="type_new">Nuevo (+)</label>
                            </div>

                            <div id="existing_client_div">
                                <label class="form-label">Buscar Cliente</label>
                                <select class="form-select" name="user_id" id="user_id_select">
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($usuarios as $u): ?>
                                        <option value="<?= $u['user_id_person'] ?>">
                                            <?= htmlspecialchars($u['NombreCompleto'] ?? $u['user_id_person']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div id="new_client_div" style="display:none;">
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label class="form-label small">Nombre *</label>
                                        <input type="text" class="form-control" name="new_nombre" id="new_nombre">
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label class="form-label small">Apellido *</label>
                                        <input type="text" class="form-control" name="new_apellido" id="new_apellido">
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">CC / Documento (Opcional)</label>
                                    <input type="text" class="form-control" name="new_cc" placeholder="Ej: 123456789">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Email (Opcional)</label>
                                    <input type="email" class="form-control" name="new_email">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-success mb-3 fw-bold">2. Datos de Estadía</h6>
                            
                            <div class="mb-3">
                                <label class="form-label">Habitación</label>
                                <select class="form-select" name="room_id" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($habitaciones as $h): 
                                         // Opcional: Deshabilitar si está en mantenimiento
                                         $disabled = ($h['room_state'] !== 'Disponible') ? 'disabled' : '';
                                         $txt = "Hab " . $h['room_name'] . " (" . $h['room_type'] . ")";
                                         if($disabled) $txt .= " [" . $h['room_state'] . "]";
                                    ?>
                                        <option value="<?= $h['room_id'] ?>" <?= $disabled ?>>
                                            <?= htmlspecialchars($txt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="form-label">Entrada</label>
                                    <input type="date" class="form-control" name="start_date" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="form-label">Salida</label>
                                    <input type="date" class="form-control" name="end_date" required value="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Estado Inicial</label>
                                <select class="form-select" name="status_id">
                                    <?php foreach ($estados as $s): ?>
                                        <option value="<?= $s['status_id'] ?>" <?= (stripos($s['status_name'], 'Confirm') !== false) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($s['status_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Confirmar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit_reservation">
                <input type="hidden" name="reservation_id" id="edit_reservation_id">
                <input type="hidden" name="room_id" id="edit_room_id">

                <div class="modal-header">
                    <h5 class="modal-title">Editar Reserva</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Entrada</label>
                            <input type="date" class="form-control" name="start_date" id="edit_start_date" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Salida</label>
                            <input type="date" class="form-control" name="end_date" id="edit_end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select class="form-select" name="status_id" id="edit_status_id">
                            <?php foreach ($estados as $s): ?>
                                <option value="<?= $s['status_id'] ?>"><?= htmlspecialchars($s['status_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Seleccione "Cancelada" para liberar la habitación.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_reservation">
    <input type="hidden" name="reservation_id" id="delete_reservation_id">
</form>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>