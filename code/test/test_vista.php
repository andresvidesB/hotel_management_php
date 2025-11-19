<?php
// Archivo: code/test_vista.php
require __DIR__ . '/vendor/autoload.php';

use Src\Reservations\Infrastructure\Services\ReservationsController;

echo "--- Probando VISTA SQL desde PHP --- \n";

try {
    // 1. Buscamos cualquier reserva para probar
    $reservas = ReservationsController::getReservations();
    if (empty($reservas)) throw new Exception("No hay reservas.");
    
    $idReserva = $reservas[0]['reservation_id'];
    echo "Consultando detalle completo de Reserva ID: $idReserva \n";
    echo "-------------------------------------------------------\n";

    // 2. Llamamos a nuestro nuevo método que usa la VISTA
    $detalle = ReservationsController::getReservaCompleta($idReserva);

    // 3. Mostramos el resultado
    print_r($detalle);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}