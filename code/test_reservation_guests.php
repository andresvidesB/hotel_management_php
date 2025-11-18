<?php
// Archivo: code/test_reservation_guests.php

require __DIR__ . '/vendor/autoload.php';

use Src\ReservationGuests\Infrastructure\Services\ReservationGuestsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Guests\Infrastructure\Services\GuestsController;

echo "--- Iniciando Prueba de Asignación de Huésped a Reserva --- \n";

try {
    // 1. Buscar una Reserva existente
    $reservas = ReservationsController::getReservations();
    if (empty($reservas)) {
        throw new Exception("Error: No hay reservas. Ejecuta test_reservas.php primero.");
    }
    $reservaId = $reservas[0]['reservation_id'];
    echo "Usando Reserva ID: " . $reservaId . "\n";

    // 2. Buscar un Huésped existente
    $huespedes = GuestsController::getGuests();
    if (empty($huespedes)) {
        throw new Exception("Error: No hay huéspedes. Ejecuta test_guests.php primero.");
    }
    // Tomamos el primero
    $huespedId = $huespedes[0]['guest_id_person'];
    // Ojo: el nombre no viene en getGuests (solo IDs y docs), así que solo mostramos el ID
    echo "Usando Huésped ID: " . $huespedId . "\n";

    // 3. Asignar el Huésped a la Reserva
    $data = [
        'reservation_guest_reservation_id' => $reservaId,
        'reservation_guest_guest_id'       => $huespedId
    ];

    echo "Asignando huésped a la reserva... \n";
    
    // Intentamos agregar. Puede fallar si ya existe la relación (Duplicate entry), es normal.
    ReservationGuestsController::addReservationGuest($data);
    
    echo "¡Huésped asignado con éxito! \n";
    echo "---------------------------------------- \n";

    // 4. Verificar: Obtener los huéspedes de esa reserva
    echo "Consultando huéspedes de la reserva $reservaId: \n";
    
    $items = ReservationGuestsController::getGuestsByReservation($reservaId);

    print_r($items);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR: \n";
    echo $e->getMessage();
}