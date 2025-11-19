<?php
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

$message = null;
$error = null;

// Cargar datos
try {
    $usuarios = UsersController::getUsers();
    $habitaciones = RoomsController::getRooms();
    $estados = StatusesController::getStatuses();
    $roles = RolesController::getRoles(); 
} catch (Exception $e) {
    $error = "Error cargando listas: " . $e->getMessage();
    $usuarios = []; $habitaciones = []; $estados = []; $roles = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_reservation') {
            $userId = '';
            
            // --- CASO 1: CLIENTE NUEVO ---
            if (isset($_POST['client_type']) && $_POST['client_type'] === 'new') {
                
                $newNombre = trim($_POST['new_nombre'] ?? '');
                $newApellido = trim($_POST['new_apellido'] ?? '');
                $newCC = trim($_POST['new_cc'] ?? '');      // Nuevo campo CC
                $newEmail = trim($_POST['new_email'] ?? ''); // Nuevo campo Email
                
                if(empty($newNombre) || empty($newApellido)) {
                    throw new Exception("Nombre y Apellido son obligatorios para clientes nuevos.");
                }

                // 1. Generar ID
                $idCreator = new UuidIdentifierCreator();
                $userId = $idCreator->createIdentifier()->getValue();

                // 2. Crear Usuario (Login)
                $roleId = '3'; // Rol Cliente
                UsersController::addUser([
                    'user_id_person' => $userId,
                    'user_password' => '12345', 
                    'user_role_id' => $roleId
                ]);
                
                // 3. ACTUALIZAR DATOS REALES DE PERSONA (Terceros)
                UsersController::savePersonData($userId, $newNombre, $newApellido, $newEmail);

                // 4. SI HAY CC, CREAR TAMBIÉN COMO HUÉSPED
                if (!empty($newCC)) {
                    // Si no se especifica tipo, asumimos CC o DNI
                    $docType = 'CC'; 
                    GuestsController::addGuest([
                        'guest_id_person' => $userId,
                        'guest_document_type' => $docType,
                        'guest_document_number' => $newCC,
                        'guest_country' => 'Desconocido' // Default
                    ]);
                }
                
            } else {
                // --- CASO 2: CLIENTE EXISTENTE ---
                $userId = $_POST['user_id'] ?? '';
                if (empty($userId)) throw new Exception("Debe seleccionar un cliente.");
            }

            // --- CONTINUAR CON LA RESERVA ---
            $roomId = $_POST['room_id'] ?? '';
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $statusId = $_POST['status_id'] ?? '';

            if (empty($roomId) || empty($startDate) || empty($endDate)) {
                throw new Exception("Habitación y fechas son obligatorias.");
            }

            // B. Crear Reserva
            
            // 1. Generamos un ID temporal para pasar la validación de la Fábrica
            // (El UseCase ignorará este y creará el definitivo, pero necesitamos enviar algo que no esté vacío)
            $idCreator = new UuidIdentifierCreator();
            $tempId = $idCreator->createIdentifier()->getValue();

            // 2. Llamamos al controlador enviando el ID temporal
            $reservaIdentifier = ReservationsController::addReservation([
                'reservation_id' => $tempId, // <--- AQUÍ ESTABA EL ERROR (antes era '')
                'reservation_source' => 'Recepción',
                'reservation_user_id' => $userId,
                'reservation_created_at' => time()
            ]);
            
            // 3. Capturamos el ID REAL y DEFINITIVO que generó la base de datos
            $reservaId = $reservaIdentifier->getValue();

            ReservationRoomsController::addReservationRoom([
                'reservation_room_reservation_id' => $reservaId,
                'reservation_room_room_id' => $roomId,
                'reservation_room_start_date' => $startDate,
                'reservation_room_end_date' => $endDate
            ]);

            if (!empty($statusId)) {
                ReservationStatusController::addReservationStatus([
                    'reservation_status_reservation_id' => $reservaId,
                    'reservation_status_status_id' => $statusId,
                    'reservation_status_changed_at' => time()
                ]);
            }

            $message = "✅ Reserva y Cliente creados exitosamente.";
        }
        // ... (Logica Edit y Delete se mantiene igual, omitida por brevedad) ...
        elseif ($_POST['action'] === 'delete_reservation') {
             $id = $_POST['reservation_id'];
             ReservationsController::deleteReservation($id);
             $message = "🗑️ Reserva eliminada.";
        }
        
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// ... (Lógica de listado de reservas se mantiene igual, omitida por brevedad. Usa la versión anterior mejorada) ...
// COPIAR AQUÍ LA LÓGICA DE LECTURA QUE TE DI EN LA RESPUESTA ANTERIOR ($listaReservas = ...)

// Para que el ejemplo funcione completo, pego la lectura básica aquí:
$listaReservas = [];
try {
    $reservasBase = ReservationsController::getReservations();
    foreach ($reservasBase as $res) {
        try {
            $id = $res['reservation_id'];
            if(empty($id)) continue;
            
            $rooms = ReservationRoomsController::getRoomsByReservation($id);
            $roomData = !empty($rooms) ? $rooms[0] : null;
            $roomName = 'Sin Asignar';
            if ($roomData) {
                foreach ($habitaciones as $h) {
                    if ($h['room_id'] == $roomData['reservation_room_room_id']) { $roomName = $h['room_name']; break; }
                }
            }
            
            $clientName = 'Desconocido';
            foreach ($usuarios as $u) {
                if ($u['user_id_person'] === $res['reservation_user_id']) {
                    $clientName = $u['NombreCompleto'] ?? ('ID: ' . substr($u['user_id_person'], 0, 5));
                    break;
                }
            }
            
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
                        if (stripos($statusName, 'Confirmada') !== false) $statusColor = 'success';
                        elseif (stripos($statusName, 'Cancelada') !== false) $statusColor = 'danger';
                        elseif (stripos($statusName, 'Ocupada') !== false) $statusColor = 'primary';
                        break;
                    }
                }
            }

            $listaReservas[] = [
                'id' => $id, 'cliente' => $clientName, 'user_id' => $res['reservation_user_id'],
                'room_id' => $roomData ? $roomData['reservation_room_room_id'] : '',
                'room_name' => $roomName,
                'start_date' => $roomData ? date('Y-m-d', $roomData['reservation_room_start_date']) : '',
                'end_date' => $roomData ? date('Y-m-d', $roomData['reservation_room_end_date']) : '',
                'status_name' => $statusName, 'status_color' => $statusColor, 'status_id' => $statusId
            ];
        } catch(Exception $e) { continue; }
    }
} catch (Exception $e) {}


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
    function confirmDelete(id) {
        if(confirm('¿Eliminar reserva?')) {
            document.getElementById('delete_reservation_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado</h5>
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
                    <tr><th>ID/Cliente</th><th>Habitación</th><th>Entrada</th><th>Salida</th><th>Estado</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($listaReservas as $res): ?>
                    <tr>
                        <td>
                            <small class="text-muted">#<?= substr($res['id'], 0, 6) ?></small><br>
                            <strong><?= htmlspecialchars($res['cliente']) ?></strong>
                        </td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($res['room_name']) ?></span></td>
                        <td><?= $res['start_date'] ?></td>
                        <td><?= $res['end_date'] ?></td>
                        <td><span class="badge bg-<?= $res['status_color'] ?>"><?= $res['status_name'] ?></span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $res['id'] ?>')"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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
                            <h6 class="text-primary mb-3">1. Cliente</h6>
                            <div class="btn-group w-100 mb-3" role="group">
                                <input type="radio" class="btn-check" name="client_type" id="type_existing" value="existing" checked onclick="toggleClientType('existing')">
                                <label class="btn btn-outline-primary" for="type_existing">Existente</label>
                                <input type="radio" class="btn-check" name="client_type" id="type_new" value="new" onclick="toggleClientType('new')">
                                <label class="btn btn-outline-primary" for="type_new">Nuevo (+)</label>
                            </div>

                            <div id="existing_client_div">
                                <select class="form-select" name="user_id" id="user_id_select">
                                    <option value="">-- Buscar --</option>
                                    <?php foreach ($usuarios as $u): ?>
                                        <option value="<?= $u['user_id_person'] ?>"><?= htmlspecialchars($u['NombreCompleto'] ?? $u['user_id_person']) ?></option>
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
                            <h6 class="text-success mb-3">2. Estadía</h6>
                            <div class="mb-3">
                                <label class="form-label">Habitación</label>
                                <select class="form-select" name="room_id" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($habitaciones as $h): ?>
                                        <option value="<?= $h['room_id'] ?>">Hab <?= htmlspecialchars($h['room_name']) ?></option>
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
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="status_id">
                                    <?php foreach ($estados as $s): ?>
                                        <option value="<?= $s['status_id'] ?>"><?= htmlspecialchars($s['status_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Reserva</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;"><input type="hidden" name="action" value="delete_reservation"><input type="hidden" name="reservation_id" id="delete_reservation_id"></form>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>