<?php
// Archivo: code/test_reservation_status.php

require __DIR__ . '/vendor/autoload.php';

use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Statuses\Infrastructure\Services\StatusesController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

echo "--- Iniciando Prueba de Estados --- \n";

try {
    // 1. Buscar Reserva
    $reservas = ReservationsController::getReservations();
    if (empty($reservas)) throw new Exception("Error: No hay reservas. Ejecuta primero test_reservas.php");
    $reservaId = $reservas[0]['reservation_id'];
    echo "Reserva ID: " . $reservaId . "\n";

    // 2. Crear un Estado (si no existe)
    $idCreator = new UuidIdentifierCreator();
    $statusId = $idCreator->createIdentifier()->getValue();
    
    $nuevoEstado = [
        'status_id' => $statusId,
        'status_name' => 'Confirmada',
        'status_description' => 'Reserva confirmada por el cliente'
    ];
    
    echo "Creando estado 'Confirmada' (ID: $statusId)... \n";
    StatusesController::addStatus($nuevoEstado);

    // 3. Asignar Estado a la Reserva
    $data = [
        'reservation_status_reservation_id' => $reservaId,
        'reservation_status_status_id'      => $statusId,
        'reservation_status_changed_at'     => time()
    ];

    echo "Asignando estado a la reserva... \n";
    ReservationStatusController::addReservationStatus($data);
    echo "¡Estado asignado con éxito! \n";

    // 4. Verificar
    echo "Historial de estados de la reserva: \n";
    $items = ReservationStatusController::getStatusesByReservation($reservaId);
    print_r($items);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR: " . $e->getMessage() . "\n";
}