<?php
require __DIR__ . '/vendor/autoload.php';

use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;

// Obtener ID del usuario logueado desde la sesión
if (session_status() === PHP_SESSION_NONE) session_start();
$myUserId = $_SESSION['user_id'] ?? '';

if (empty($myUserId)) {
    header('Location: login.php');
    exit;
}

$misReservas = [];
try {
    // 1. Buscar reservas asociadas a este usuario (Como Huésped)
    $rels = ReservationGuestsController::getReservationsByGuest($myUserId);
    
    foreach ($rels as $rel) {
        $resId = $rel['reservation_guest_reservation_id'];
        
        // Detalles Reserva
        $rooms = ReservationRoomsController::getRoomsByReservation($resId);
        if (empty($rooms)) continue;
        
        $roomData = $rooms[0]; // Asumimos 1 hab por reserva
        
        // Nombre Habitación
        try {
            $r = RoomsController::getRoomById($roomData['reservation_room_room_id']);
            $roomName = $r['room_name'];
        } catch(Exception $e) { $roomName = "Habitación"; }

        // Estado
        $statuses = ReservationStatusController::getStatusesByReservation($resId);
        $lastStatus = !empty($statuses) ? end($statuses) : null;
        $estado = 'Pendiente';
        if ($lastStatus) {
            // Mapeo manual rápido de IDs a nombres para mostrar
            $sId = $lastStatus['reservation_status_status_id'];
            if ($sId == '10') $estado = 'Confirmada';
            if ($sId == '20') $estado = 'Activa (En Hotel)';
            if ($sId == '30') $estado = 'Cancelada';
            if ($sId == '40') $estado = 'Finalizada';
        }

        $misReservas[] = [
            'id' => $resId,
            'hab' => $roomName,
            'inicio' => date('d M, Y', $roomData['reservation_room_start_date']),
            'fin' => date('d M, Y', $roomData['reservation_room_end_date']),
            'estado' => $estado
        ];
    }

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
}

require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <h3>Mis Reservas</h3>
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <?php if(empty($misReservas)): ?>
                <div class="text-center py-5">
                    <i class="fa-solid fa-suitcase-rolling fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Aún no tienes reservas.</p>
                    <a href="catalogo.php" class="btn btn-primary">Explorar Habitaciones</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Habitación</th>
                                <th>Fechas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($misReservas as $res): 
                                $bg = 'secondary';
                                if($res['estado'] == 'Confirmada') $bg = 'success';
                                if($res['estado'] == 'Cancelada') $bg = 'danger';
                            ?>
                            <tr>
                                <td class="fw-bold"><?= htmlspecialchars($res['hab']) ?></td>
                                <td><?= $res['inicio'] ?> <i class="fa-solid fa-arrow-right small text-muted"></i> <?= $res['fin'] ?></td>
                                <td><span class="badge bg-<?= $bg ?>"><?= $res['estado'] ?></span></td>
                                <td>
                                    <?php if($res['estado'] == 'Confirmada'): ?>
                                        <button class="btn btn-sm btn-outline-danger" onclick="alert('Para cancelar, por favor contacte a recepción.')">
                                            Cancelar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>