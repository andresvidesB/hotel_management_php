<?php
require __DIR__ . '/vendor/autoload.php';

use Src\Guests\Infrastructure\Services\GuestsController;
use Src\Users\Infrastructure\Services\UsersController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;
use Src\ReservationPayments\Infrastructure\Services\ReservationPaymentsController;
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Statuses\Infrastructure\Services\StatusesController;

$message = null;
$error = null;

// Cargar listas para los selectores
try {
    $habitaciones = RoomsController::getRooms();
    $usuarios = UsersController::getUsers();
    $estados = StatusesController::getStatuses(); 
} catch (Exception $e) { 
    $error = "Error cargando datos iniciales: " . $e->getMessage(); 
}

// --- MANEJO DE ACCIONES (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        
        // === ACCIÓN 1: AGREGAR NUEVO HUÉSPED ===
        if ($_POST['action'] === 'add_guest') {
            $nombres  = trim($_POST['nombres']);
            $apellidos= trim($_POST['apellidos']);
            $docType  = $_POST['doc_type'];
            $docNum   = trim($_POST['doc_num']);
            $email    = trim($_POST['email']);
            $country  = trim($_POST['country']);

            if(empty($nombres) || empty($docNum)) throw new Exception("Nombre y Documento son obligatorios.");

            $idCreator = new UuidIdentifierCreator();
            $personaId = $idCreator->createIdentifier()->getValue();

            // 1. Crear Usuario de Acceso (Rol Cliente)
            UsersController::addUser([
                'user_id_person' => $personaId,
                'user_password' => '12345', 
                'user_role_id' => '3' 
            ]);

            // 2. Guardar Datos Personales
            UsersController::savePersonData($personaId, $nombres, $apellidos, $email);

            // 3. Crear Registro de Huésped
            GuestsController::addGuest([
                'guest_id_person' => $personaId,
                'guest_document_type' => $docType,
                'guest_document_number' => $docNum,
                'guest_country' => $country
            ]);

            $message = "✅ Huésped registrado correctamente.";
        }

        // === ACCIÓN 2: EDITAR HUÉSPED ===
        elseif ($_POST['action'] === 'edit_guest') {
            $id = $_POST['guest_id'];
            $nombres  = trim($_POST['nombres']);
            $apellidos= trim($_POST['apellidos']);
            $docType  = $_POST['doc_type'];
            $docNum   = trim($_POST['doc_num']);
            $email    = trim($_POST['email']);
            $country  = trim($_POST['country']);

            GuestsController::updateGuest([
                'guest_id_person' => $id,
                'guest_document_type' => $docType,
                'guest_document_number' => $docNum,
                'guest_country' => $country
            ]);

            UsersController::savePersonData($id, $nombres, $apellidos, $email);
            $message = "✏️ Datos actualizados.";
        }

        // === ACCIÓN 3: ELIMINAR HUÉSPED ===
        elseif ($_POST['action'] === 'delete_guest') {
            GuestsController::deleteGuest($_POST['guest_id']);
            $message = "🗑️ Huésped eliminado.";
        }

        // === ACCIÓN 4: ASIGNAR HABITACIÓN (CHECK-IN) ===
        elseif ($_POST['action'] === 'assign_room') {
            $userId = $_POST['user_id'];
            $roomId = $_POST['room_id'];
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            $hasPayment = isset($_POST['has_payment']);
            $paymentAmount = floatval($_POST['payment_amount'] ?? 0);
            $paymentMethod = $_POST['payment_method'] ?? 'Efectivo';

            // Validaciones
            if (empty($userId) || empty($roomId) || empty($startDate) || empty($endDate)) {
                throw new Exception("Todos los campos son obligatorios.");
            }
            if (!ReservationRoomsController::isRoomAvailable($roomId, $startDate, $endDate)) {
                throw new Exception("🚫 La habitación NO está disponible en esas fechas.");
            }

            // 1. Asegurar que el Usuario sea Huésped
            $huespedExistente = GuestsController::getGuestById($userId);
            if (empty($huespedExistente)) {
                GuestsController::addGuest([
                    'guest_id_person' => $userId,
                    'guest_document_type' => 'CC',
                    'guest_document_number' => 'S/D',
                    'guest_country' => 'Desconocido'
                ]);
            }

            // 2. Crear Reserva
            $idCreator = new UuidIdentifierCreator();
            $reservaId = $idCreator->createIdentifier()->getValue(); // ID Temporal

            $resIdentifier = ReservationsController::addReservation([
                'reservation_id' => $reservaId, 
                'reservation_source' => 'Recepción (Directa)',
                'reservation_user_id' => $userId,
                'reservation_created_at' => time()
            ]);
            $reservaIdReal = $resIdentifier->getValue();

            // 3. Asignar Habitación
            ReservationRoomsController::addReservationRoom([
                'reservation_room_reservation_id' => $reservaIdReal,
                'reservation_room_room_id' => $roomId,
                'reservation_room_start_date' => $startDate,
                'reservation_room_end_date' => $endDate
            ]);

            // 4. ACTUALIZAR ESTADO FÍSICO DE LA HABITACIÓN A "OCUPADA"
            RoomsController::updateRoomState($roomId, 'Ocupada');

            // 5. Asignar Estado de Reserva
            $estadoId = '20'; // Default 'Ocupada'
            foreach($estados as $s) { if(stripos($s['status_name'], 'Ocupad') !== false) $estadoId = $s['status_id']; }
            
            ReservationStatusController::addReservationStatus([
                'reservation_status_reservation_id' => $reservaIdReal,
                'reservation_status_status_id' => $estadoId,
                'reservation_status_changed_at' => time()
            ]);

            // 6. Vincular Huésped
            ReservationGuestsController::addReservationGuest([
                'reservation_guest_reservation_id' => $reservaIdReal,
                'reservation_guest_guest_id' => $userId
            ]);

            // 7. Registrar Pago
            if ($hasPayment && $paymentAmount > 0) {
                ReservationPaymentsController::addReservationPayment([
                    'reservation_payment_reservation_id' => $reservaIdReal,
                    'reservation_payment_amount' => $paymentAmount,
                    'reservation_payment_date' => date('Y-m-d'),
                    'reservation_payment_method' => $paymentMethod
                ]);
            }

            $message = "✅ Habitación asignada, estado actualizado a 'Ocupada' y pago registrado.";
        }
        
    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// --- CARGA DE DATOS DE LA LISTA ---
$huespedesList = [];
try {
    // Trae el array completo con nombres desde el Repositorio actualizado
    $huespedesBase = GuestsController::getGuests();
    
    foreach ($huespedesBase as $h) {
        $id = $h['guest_id_person'];
        $reservaInfo = null;
        
        try {
            // Buscar historial de reservas
            $reservas = ReservationGuestsController::getReservationsByGuest($id);
            
            if (!empty($reservas)) {
                $lastResRel = end($reservas);
                $resId = $lastResRel['reservation_guest_reservation_id'];
                
                // Verificar si está finalizada/cancelada para NO mostrarla
                $statuses = ReservationStatusController::getStatusesByReservation($resId);
                $lastStatus = !empty($statuses) ? end($statuses) : null;
                $isFinalized = false;
                if ($lastStatus) {
                    $sId = $lastStatus['reservation_status_status_id'];
                    // ID 30=Cancelada, 40=Finalizada (Ajusta según tus IDs reales)
                    if ($sId == '30' || $sId == '40') $isFinalized = true;
                }

                if (!$isFinalized) {
                    $rooms = ReservationRoomsController::getRoomsByReservation($resId);
                    if (!empty($rooms)) {
                        $roomData = $rooms[0];
                        // Verificar vigencia de fechas
                        if ($roomData['reservation_room_end_date'] >= strtotime('today')) {
                            $reservaInfo = [
                                'id' => $resId,
                                'start' => date('d/m', $roomData['reservation_room_start_date']),
                                'end' => date('d/m', $roomData['reservation_room_end_date']),
                                'room_id' => $roomData['reservation_room_room_id']
                            ];
                            // Nombre de habitación
                            try {
                                $rDet = RoomsController::getRoomById($reservaInfo['room_id']);
                                $reservaInfo['room_name'] = $rDet['room_name'] ?? $reservaInfo['room_id'];
                            } catch(Exception $ex) { $reservaInfo['room_name'] = 'Hab ' . $reservaInfo['room_id']; }
                        }
                    }
                }
            }
        } catch (Exception $e) { }

        $h['reserva_activa'] = $reservaInfo;
        $huespedesList[] = $h;
    }
} catch (Exception $e) {
    $error = "Error cargando lista: " . $e->getMessage();
}

require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestión de Huéspedes';

    function prepareEdit(id, nombre, apellido, docType, docNum, email, country) {
        document.getElementById('edit_guest_id').value = id;
        document.getElementById('edit_nombres').value = nombre;
        document.getElementById('edit_apellidos').value = apellido;
        document.getElementById('edit_doc_type').value = docType;
        document.getElementById('edit_doc_num').value = docNum;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_country').value = country;
    }

    function togglePayment() {
        var check = document.getElementById('has_payment');
        var div = document.getElementById('payment_details');
        div.style.display = check.checked ? 'block' : 'none';
        document.getElementById('payment_amount').required = check.checked;
    }

    function confirmDelete(id) {
        if(confirm('¿Eliminar este huésped? Se borrará su usuario y acceso.')) {
            document.getElementById('delete_guest_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Directorio</h5>
        <div>
            <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalAdd">
                <i class="fa-solid fa-user-plus"></i> Nuevo Huésped
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAssign">
                <i class="fa-solid fa-key"></i> Asignar Habitación
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nombre</th>
                        <th>Doc</th>
                        <th>Contacto</th>
                        <th>Reserva Actual</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($huespedesList)): ?>
                        <tr><td colspan="5" class="text-center py-3">No hay huéspedes registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($huespedesList as $h): ?>
                        <tr>
                            <td class="fw-bold">
                                <i class="fa-solid fa-user text-secondary me-2"></i> 
                                <?= htmlspecialchars($h['NombreCompleto']) ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($h['guest_document_type']) ?></span> 
                                <?= htmlspecialchars($h['guest_document_number']) ?>
                            </td>
                            <td>
                                <small class="d-block"><?= htmlspecialchars($h['CorreoElectronico']) ?></small>
                                <small class="text-muted"><?= htmlspecialchars($h['guest_country']) ?></small>
                            </td>
                            <td>
                                <?php if ($h['reserva_activa']): ?>
                                    <div class="badge bg-success p-2">
                                        <i class="fa-solid fa-bed"></i> <?= $h['reserva_activa']['room_name'] ?>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <a href="checkout.php?id=<?= $h['reserva_activa']['id'] ?>" class="text-decoration-none text-success fw-bold">
                                            Ir a Check-Out
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalEdit"
                                        onclick="prepareEdit(
                                            '<?= $h['guest_id_person'] ?>', 
                                            '<?= htmlspecialchars($h['Nombres']) ?>',
                                            '<?= htmlspecialchars($h['Apellidos']) ?>',
                                            '<?= htmlspecialchars($h['guest_document_type']) ?>',
                                            '<?= htmlspecialchars($h['guest_document_number']) ?>',
                                            '<?= htmlspecialchars($h['CorreoElectronico']) ?>',
                                            '<?= htmlspecialchars($h['guest_country']) ?>'
                                        )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $h['guest_id_person'] ?>')">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_guest">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Nuevo Huésped</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6"><label>Nombres *</label><input type="text" class="form-control" name="nombres" required></div>
                        <div class="col-6"><label>Apellidos</label><input type="text" class="form-control" name="apellidos"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><label>Tipo Doc</label><select class="form-select" name="doc_type"><option value="CC">CC</option><option value="PASAPORTE">Pasaporte</option><option value="DNI">DNI</option></select></div>
                        <div class="col-8"><label>Número *</label><input type="text" class="form-control" name="doc_num" required></div>
                    </div>
                    <div class="mb-3"><label>País</label><input type="text" class="form-control" name="country"></div>
                    <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email"></div>
                    <div class="alert alert-info small">Se creará un usuario de sistema automáticamente.</div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssign" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="assign_room">
                <div class="modal-header bg-success text-white"><h5 class="modal-title">Asignar Habitación</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="fw-bold">Usuario</label>
                        <select class="form-select" name="user_id" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($usuarios as $u): ?>
                                <option value="<?= $u['user_id_person'] ?>"><?= htmlspecialchars($u['NombreCompleto'] ?? $u['user_id_person']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="fw-bold">Habitación</label>
                        <select class="form-select" name="room_id" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($habitaciones as $h): 
                                $disabled = ($h['room_state'] !== 'Disponible') ? 'disabled' : '';
                                $txt = "Hab " . $h['room_name'] . " (" . $h['room_type'] . ") - $" . $h['room_price'];
                                if($disabled) $txt .= " [" . $h['room_state'] . "]";
                            ?>
                                <option value="<?= $h['room_id'] ?>" <?= $disabled ?>><?= htmlspecialchars($txt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6"><label>Entrada</label><input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d') ?>" required></div>
                        <div class="col-6"><label>Salida</label><input type="date" class="form-control" name="end_date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required></div>
                    </div>
                    <hr>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="has_payment" name="has_payment" onclick="togglePayment()">
                        <label class="form-check-label fw-bold" for="has_payment">¿Pago Inmediato?</label>
                    </div>
                    <div id="payment_details" style="display:none;" class="bg-light p-3 rounded border">
                        <div class="mb-3"><label>Monto</label><input type="number" class="form-control" name="payment_amount" id="payment_amount" min="0" step="0.01"></div>
                        <div class="mb-3"><label>Método</label>
                            <select class="form-select" name="payment_method">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Transferencia">Transferencia</option>
                                <option value="Tarjeta">Tarjeta</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success">Confirmar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit_guest">
                <input type="hidden" name="guest_id" id="edit_guest_id">
                <div class="modal-header"><h5 class="modal-title">Editar Huésped</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6"><label>Nombres</label><input type="text" class="form-control" name="nombres" id="edit_nombres" required></div>
                        <div class="col-6"><label>Apellidos</label><input type="text" class="form-control" name="apellidos" id="edit_apellidos"></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><label>Tipo Doc</label><select class="form-select" name="doc_type" id="edit_doc_type"><option value="CC">CC</option><option value="PASAPORTE">Pasaporte</option></select></div>
                        <div class="col-8"><label>Número</label><input type="text" class="form-control" name="doc_num" id="edit_doc_num" required></div>
                    </div>
                    <div class="mb-3"><label>País</label><input type="text" class="form-control" name="country" id="edit_country"></div>
                    <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="email" id="edit_email"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-warning">Actualizar</button></div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;"><input type="hidden" name="action" value="delete_guest"><input type="hidden" name="guest_id" id="delete_guest_id"></form>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>