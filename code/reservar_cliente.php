<?php
require __DIR__ . '/vendor/autoload.php';
// ... (Imports necesarios: Reservations, Rooms, etc.) ...
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;
use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

session_start();
$userId = $_SESSION['user_id'] ?? '';
if(empty($userId)) { header('Location: login.php'); exit; }

$roomId = $_GET['room_id'] ?? '';
$message = null;
$error = null;

// Obtener datos habitación
try {
    $room = RoomsController::getRoomById($roomId);
} catch(Exception $e) { die("Habitación no válida"); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];

        if ($start >= $end) throw new Exception("Fechas inválidas.");
        
        // Validar Disponibilidad
        if (!ReservationRoomsController::isRoomAvailable($roomId, $start, $end)) {
            throw new Exception("Lo sentimos, esta habitación ya está reservada para esas fechas.");
        }

        // Crear Reserva (Misma lógica que admin pero fija para este usuario)
        $idCreator = new UuidIdentifierCreator();
        $reservaId = $idCreator->createIdentifier()->getValue(); // ID Temporal

        $resIdentifier = ReservationsController::addReservation([
            'reservation_id' => $reservaId,
            'reservation_source' => 'Web Cliente',
            'reservation_user_id' => $userId,
            'reservation_created_at' => time()
        ]);
        $realId = $resIdentifier->getValue();

        ReservationRoomsController::addReservationRoom([
            'reservation_room_reservation_id' => $realId,
            'reservation_room_room_id' => $roomId,
            'reservation_room_start_date' => $start,
            'reservation_room_end_date' => $end
        ]);

        // Estado: Confirmada (ID 10)
        ReservationStatusController::addReservationStatus([
            'reservation_status_reservation_id' => $realId,
            'reservation_status_status_id' => '10', 
            'reservation_status_changed_at' => time()
        ]);

        // Vincular Huésped (El usuario mismo)
        ReservationGuestsController::addReservationGuest([
            'reservation_guest_reservation_id' => $realId,
            'reservation_guest_guest_id' => $userId
        ]);

        $message = "¡Reserva realizada con éxito! Te esperamos.";

    } catch (Exception $e) { $error = $e->getMessage(); }
}

require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Confirmar Reserva</h5>
        </div>
        <div class="card-body">
            <?php if($message): ?>
                <div class="alert alert-success"><?= $message ?> <br> <a href="mis_reservas.php" class="fw-bold">Ver mis reservas</a></div>
            <?php elseif($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>

            <?php if(!$message): ?>
            <h4 class="fw-bold"><?= htmlspecialchars($room['room_name']) ?></h4>
            <p class="text-muted"><?= ucfirst($room['room_type']) ?> - $<?= number_format($room['room_price']) ?> / noche</p>
            <hr>
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label">Llegada</label>
                        <input type="date" class="form-control" name="start_date" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Salida</label>
                        <input type="date" class="form-control" name="end_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success btn-lg">Confirmar Reserva</button>
                    <a href="catalogo.php" class="btn btn-link">Cancelar</a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>