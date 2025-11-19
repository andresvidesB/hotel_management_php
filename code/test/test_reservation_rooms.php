<?php
// Archivo: code/test_reservation_rooms.php

require __DIR__ . '/vendor/autoload.php';

use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Rooms\Infrastructure\Services\RoomsController;

echo "--- Iniciando Prueba de Asignación de Habitación a Reserva --- \n";

try {
    // 1. Buscar una Reserva existente
    $reservas = ReservationsController::getReservations();
    if (empty($reservas)) {
        throw new Exception("Error: No hay reservas. Ejecuta test_reservas.php primero.");
    }
    $reservaId = $reservas[0]['reservation_id'];
    echo "Usando Reserva ID: " . $reservaId . "\n";

    // 2. Buscar una Habitación existente
    $habitaciones = RoomsController::getRooms();
    if (empty($habitaciones)) {
        throw new Exception("Error: No hay habitaciones. Ejecuta test_habitaciones.php primero.");
    }
    $habitacionId = $habitaciones[0]['room_id'];
    echo "Usando Habitación ID: " . $habitacionId . " (" . $habitaciones[0]['room_name'] . ")\n";

    // 3. Asignar la Habitación a la Reserva
    // Definimos fechas para la estancia (hoy y mañana)
    $fechaInicio = date('Y-m-d');
    $fechaFin = date('Y-m-d', strtotime('+3 days'));

    $data = [
        'reservation_room_reservation_id' => $reservaId,
        'reservation_room_room_id'        => $habitacionId,
        'reservation_room_start_date'     => $fechaInicio,
        'reservation_room_end_date'       => $fechaFin
    ];

    echo "Asignando habitación... \n";
    
    // Intentamos agregar. Si ya existe la relación, MySQL podría dar error de clave duplicada.
    // En un caso real manejarías esa excepción, aquí dejamos que falle para saberlo.
    ReservationRoomsController::addReservationRoom($data);
    
    echo "¡Habitación asignada con éxito! \n";
    echo "---------------------------------------- \n";

    // 4. Verificar: Obtener las habitaciones de esa reserva
    echo "Consultando habitaciones de la reserva $reservaId: \n";
    
    $items = ReservationRoomsController::getRoomsByReservation($reservaId);

    print_r($items);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR: \n";
    echo $e->getMessage();
    // Si el error es "Duplicate entry", significa que ya corriste la prueba y la relación existe.
}