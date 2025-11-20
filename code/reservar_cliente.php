<?php
require __DIR__ . '/vendor/autoload.php';

use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;
use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

// Iniciar sesión para acceder a variables $_SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userId = $_SESSION['user_id'] ?? '';
$roomId = $_GET['room_id'] ?? '';

// --- REDIRECCIÓN INTELIGENTE ---
// Si no está logueado, lo mandamos al login pero le decimos que vuelva aquí después
if (empty($userId)) {
    $nextUrl = urlencode("reservar_cliente.php?room_id=" . $roomId);
    header("Location: login.php?next=" . $nextUrl);
    exit;
}

$message = null;
$error = null;
$room = null;

// Obtener datos de la habitación
try {
    if (empty($roomId)) throw new Exception("No se especificó una habitación.");
    $room = RoomsController::getRoomById($roomId);
    if (empty($room)) throw new Exception("Habitación no encontrada.");
} catch(Exception $e) { 
    die("<div class='alert alert-danger m-5'>" . $e->getMessage() . " <a href='catalogo.php'>Volver</a></div>"); 
}

// --- PROCESAR RESERVA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $start = $_POST['start_date'];
        $end = $_POST['end_date'];

        // 1. Validaciones
        if (empty($start) || empty($end)) throw new Exception("Seleccione fechas de llegada y salida.");
        if ($start >= $end) throw new Exception("La fecha de salida debe ser posterior a la llegada.");
        if ($start < date('Y-m-d')) throw new Exception("La fecha de llegada no puede ser en el pasado.");
        
        // 2. Validar Disponibilidad (Anti-Overbooking)
        if (!ReservationRoomsController::isRoomAvailable($roomId, $start, $end)) {
            throw new Exception("🚫 Lo sentimos, esta habitación ya está reservada para esas fechas.");
        }

        // 3. Crear Reserva Base
        $idCreator = new UuidIdentifierCreator();
        $tempId = $idCreator->createIdentifier()->getValue(); 

        $resIdentifier = ReservationsController::addReservation([
            'reservation_id' => $tempId,
            'reservation_source' => 'Web Cliente',
            'reservation_user_id' => $userId,
            'reservation_created_at' => time()
        ]);
        $realId = $resIdentifier->getValue();

        // 4. Asignar Habitación y Fechas
        ReservationRoomsController::addReservationRoom([
            'reservation_room_reservation_id' => $realId,
            'reservation_room_room_id' => $roomId,
            'reservation_room_start_date' => $start,
            'reservation_room_end_date' => $end
        ]);

        // 5. Asignar Estado 'Confirmada' (ID 10)
        // Nota: No cambiamos el estado físico de la habitación a 'Ocupada' todavía,
        // eso se hace en el Check-in físico. Aquí solo apartamos las fechas.
        ReservationStatusController::addReservationStatus([
            'reservation_status_reservation_id' => $realId,
            'reservation_status_status_id' => '10', 
            'reservation_status_changed_at' => time()
        ]);

        // 6. Vincular al Usuario como Huésped
        ReservationGuestsController::addReservationGuest([
            'reservation_guest_reservation_id' => $realId,
            'reservation_guest_guest_id' => $userId
        ]);

        $message = "¡Reserva realizada con éxito! Gracias por preferirnos.";

    } catch (Exception $e) { 
        $error = $e->getMessage(); 
    }
}

require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-primary text-white p-4 text-center">
                    <h4 class="mb-0 fw-bold"><i class="fa-solid fa-calendar-check me-2"></i> Confirmar Reserva</h4>
                </div>
                
                <div class="card-body p-4">
                    <?php if($message): ?>
                        <div class="text-center py-4">
                            <div class="mb-3 text-success"><i class="fa-solid fa-circle-check fa-4x"></i></div>
                            <h3 class="text-success fw-bold">¡Reserva Exitosa!</h3>
                            <p class="text-muted"><?= $message ?></p>
                            <hr>
                            <div class="d-grid gap-2">
                                <a href="mis_reservas.php" class="btn btn-primary btn-lg">Ver Mis Reservas</a>
                                <a href="catalogo.php" class="btn btn-outline-secondary">Volver al Catálogo</a>
                            </div>
                        </div>
                    <?php else: ?>

                        <?php if($error): ?>
                            <div class="alert alert-danger d-flex align-items-center">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                                <div><?= $error ?></div>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <h3 class="fw-bold text-dark"><?= htmlspecialchars($room['room_name']) ?></h3>
                            <span class="badge bg-info text-dark px-3 py-2 rounded-pill mb-2">
                                <?= ucfirst($room['room_type']) ?>
                            </span>
                            <h2 class="text-primary fw-bold mt-2">
                                $<?= number_format($room['room_price'], 0) ?> <small class="fs-6 text-muted">/ noche</small>
                            </h2>
                        </div>

                        <hr class="mb-4">

                        <form method="POST">
                            <div class="row g-3 mb-4">
                                <div class="col-6">
                                    <label class="form-label fw-bold text-muted small">LLEGADA</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-calendar-plus text-success"></i></span>
                                        <input type="date" class="form-control form-control-lg border-start-0" name="start_date" required min="<?= date('Y-m-d') ?>">
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold text-muted small">SALIDA</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-calendar-minus text-danger"></i></span>
                                        <input type="date" class="form-control form-control-lg border-start-0" name="end_date" required min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-light border small text-muted mb-4">
                                <i class="fa-solid fa-info-circle me-1"></i> Su reserva quedará confirmada inmediatamente. El pago se realizará en el hotel.
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success btn-lg py-3 fw-bold shadow-sm">
                                    CONFIRMAR RESERVA
                                </button>
                                <a href="catalogo.php" class="btn btn-link text-decoration-none text-muted">Cancelar y volver</a>
                            </div>
                        </form>

                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>