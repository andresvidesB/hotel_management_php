<?php
require __DIR__ . '/vendor/autoload.php';

use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;
use Src\Shared\Infrastructure\Services\EmailService;
use Src\Users\Infrastructure\Services\UsersController;

// Iniciar sesión y verificar usuario
if (session_status() === PHP_SESSION_NONE) session_start();
$userId = $_SESSION['user_id'] ?? '';
if (empty($userId)) { header('Location: login.php'); exit; }

$message = null;
$error = null;

// --- MANEJO DE ACCIONES ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // SOLICITAR CANCELACIÓN (ENVÍO DE CORREO)
        if ($_POST['action'] === 'request_cancel') {
            $resId = $_POST['reservation_id'];
            $reason = trim($_POST['reason']);
            
            if(empty($reason)) throw new Exception("Debe indicar un motivo.");

            // Obtener nombre del cliente para el correo
            $userData = UsersController::getUserByIdPerson($userId);
            // Buscamos el nombre real en la lista (usando la vista)
            $clientName = 'Cliente';
            foreach(UsersController::getUsers() as $u) {
                if($u['user_id_person'] === $userId) { $clientName = $u['NombreCompleto']; break; }
            }

            // Enviar Correo
            $sent = EmailService::sendCancellationRequest($resId, $clientName, $reason);
            
            if ($sent) {
                $message = "✅ Solicitud enviada a la administración. Nos pondremos en contacto pronto.";
            } else {
                throw new Exception("No se pudo enviar el correo. Intente más tarde o contacte por teléfono.");
            }
        }

        // MODIFICAR FECHAS (CON VALIDACIÓN)
        elseif ($_POST['action'] === 'modify_dates') {
            $resId = $_POST['reservation_id'];
            $roomId = $_POST['room_id'];
            $newStart = $_POST['start_date'];
            $newEnd = $_POST['end_date'];

            // Validaciones
            if ($newStart >= $newEnd) throw new Exception("Fechas inválidas: Salida debe ser después de llegada.");
            if ($newStart < date('Y-m-d')) throw new Exception("La fecha de llegada no puede ser en el pasado.");

            // Verificar Disponibilidad (Excluyendo la reserva actual para permitir moverse a sí misma)
            $isAvailable = ReservationRoomsController::isRoomAvailable($roomId, $newStart, $newEnd, $resId);

            if (!$isAvailable) {
                throw new Exception("🚫 Lo sentimos, la habitación no está disponible para esas nuevas fechas.");
            }

            // Actualizar en BD (Borrar fechas viejas e insertar nuevas)
            ReservationRoomsController::deleteReservationRoom($resId, $roomId);
            ReservationRoomsController::addReservationRoom([
                'reservation_room_reservation_id' => $resId,
                'reservation_room_room_id' => $roomId,
                'reservation_room_start_date' => $newStart,
                'reservation_room_end_date' => $newEnd
            ]);

            $message = "✅ ¡Fechas modificadas con éxito!";
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// --- LISTAR RESERVAS ---
$misReservas = [];
try {
    $rels = ReservationGuestsController::getReservationsByGuest($userId);
    foreach ($rels as $rel) {
        $resId = $rel['reservation_guest_reservation_id'];
        
        $rooms = ReservationRoomsController::getRoomsByReservation($resId);
        if (empty($rooms)) continue;
        $roomData = $rooms[0];
        
        try {
            $r = RoomsController::getRoomById($roomData['reservation_room_room_id']);
            $roomName = $r['room_name'];
            $roomType = ucfirst($r['room_type']);
        } catch(Exception $e) { $roomName = "Habitación"; $roomType=""; }

        $statuses = ReservationStatusController::getStatusesByReservation($resId);
        $lastStatus = !empty($statuses) ? end($statuses) : null;
        $estado = 'Pendiente';
        $isModifiable = true;

        if ($lastStatus) {
            $sId = $lastStatus['reservation_status_status_id'];
            if ($sId == '10') $estado = 'Confirmada';
            if ($sId == '20') { $estado = 'Activa'; $isModifiable = false; } // No modificar si ya está en el hotel
            if ($sId == '30') { $estado = 'Cancelada'; $isModifiable = false; }
            if ($sId == '40') { $estado = 'Finalizada'; $isModifiable = false; }
        }

        $misReservas[] = [
            'id' => $resId,
            'room_id' => $roomData['reservation_room_room_id'],
            'room_name' => $roomName,
            'room_type' => $roomType,
            'start' => date('Y-m-d', $roomData['reservation_room_start_date']),
            'end' => date('Y-m-d', $roomData['reservation_room_end_date']),
            'estado' => $estado,
            'can_modify' => $isModifiable
        ];
    }
} catch (Exception $e) {}

require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Mis Reservas';

    function openCancelModal(id) {
        document.getElementById('cancel_res_id').value = id;
    }

    function openModifyModal(id, roomId, start, end) {
        document.getElementById('mod_res_id').value = id;
        document.getElementById('mod_room_id').value = roomId;
        document.getElementById('mod_start').value = start;
        document.getElementById('mod_end').value = end;
    }
</script>

<div class="container mt-4">
    <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <?php if(empty($misReservas)): ?>
        <div class="text-center py-5">
            <i class="fa-solid fa-suitcase-rolling fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No tienes reservas activas.</h4>
            <a href="catalogo.php" class="btn btn-primary mt-3">Explorar Habitaciones</a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($misReservas as $res): 
                $bg = 'secondary';
                if($res['estado'] == 'Confirmada') $bg = 'success';
                if($res['estado'] == 'Cancelada') $bg = 'danger';
            ?>
            <div class="col-md-6 mb-3">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <h5 class="card-title fw-bold text-primary">Habitación <?= htmlspecialchars($res['room_name']) ?></h5>
                            <span class="badge bg-<?= $bg ?>"><?= $res['estado'] ?></span>
                        </div>
                        <h6 class="card-subtitle mb-3 text-muted"><?= $res['room_type'] ?></h6>
                        
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-4">
                                <small class="text-muted d-block">Llegada</small>
                                <strong><?= date('d M Y', strtotime($res['start'])) ?></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Salida</small>
                                <strong><?= date('d M Y', strtotime($res['end'])) ?></strong>
                            </div>
                        </div>

                        <?php if($res['can_modify']): ?>
                            <div class="d-flex gap-2">
                                <button class="btn btn-outline-primary btn-sm w-50" 
                                    data-bs-toggle="modal" data-bs-target="#modalModify"
                                    onclick="openModifyModal('<?= $res['id'] ?>', '<?= $res['room_id'] ?>', '<?= $res['start'] ?>', '<?= $res['end'] ?>')">
                                    <i class="fa-solid fa-calendar-days"></i> Cambiar Fechas
                                </button>
                                
                                <button class="btn btn-outline-danger btn-sm w-50" 
                                    data-bs-toggle="modal" data-bs-target="#modalCancel"
                                    onclick="openCancelModal('<?= $res['id'] ?>')">
                                    <i class="fa-solid fa-ban"></i> Cancelar
                                </button>
                            </div>
                        <?php else: ?>
                            <small class="text-muted fst-italic">Esta reserva no se puede modificar.</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalCancel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="request_cancel">
                <input type="hidden" name="reservation_id" id="cancel_res_id">
                
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Solicitar Cancelación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Lamentamos que no puedas venir. Por favor, indícanos el motivo para procesar tu solicitud:</p>
                    <textarea class="form-control" name="reason" rows="3" placeholder="Ej: Cambio de planes, enfermedad..." required></textarea>
                    <div class="alert alert-warning small mt-3 mb-0">
                        <i class="fa-solid fa-info-circle"></i> Se enviará un correo a la administración.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Volver</button>
                    <button type="submit" class="btn btn-danger">Enviar Solicitud</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalModify" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="modify_dates">
                <input type="hidden" name="reservation_id" id="mod_res_id">
                <input type="hidden" name="room_id" id="mod_room_id">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Modificar Fechas</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Selecciona las nuevas fechas. El sistema verificará disponibilidad automáticamente.</p>
                    <div class="mb-3">
                        <label class="form-label">Nueva Llegada</label>
                        <input type="date" class="form-control" name="start_date" id="mod_start" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Salida</label>
                        <input type="date" class="form-control" name="end_date" id="mod_end" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Verificar y Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>