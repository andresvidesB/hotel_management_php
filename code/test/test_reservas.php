<?php
// Archivo: code/test_reservas.php

require __DIR__ . '/vendor/autoload.php';

use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Users\Infrastructure\Services\UsersController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

echo "--- Iniciando Prueba de Reservas --- \n";

try {
    // 1. Necesitamos un Usuario válido.
    $users = UsersController::getUsers();
    
    if (empty($users)) {
        throw new Exception("Error: No hay usuarios en la base de datos. Ejecuta primero test_users.php");
    }

    $userValido = $users[0];
    $idUsuario = $userValido['user_id_person'];
    echo "Usando Usuario ID existente: " . $idUsuario . "\n";

    // 2. Crear ID para la reserva
    $idCreator = new UuidIdentifierCreator();
    $nuevoIdReserva = $idCreator->createIdentifier()->getValue();

    $nuevaReserva = [
        "reservation_id" => $nuevoIdReserva,
        "reservation_source" => "Pagina Web",
        "reservation_user_id" => $idUsuario,
        "reservation_created_at" => time() // Timestamp actual
    ];

    echo "Intentando crear reserva con ID: $nuevoIdReserva \n";
    
    ReservationsController::addReservation($nuevaReserva);
    
    echo "¡Reserva creada con éxito! \n";
    echo "---------------------------------------- \n";

    // 3. Obtener reservas
    echo "Obteniendo todas las reservas de la BD: \n";
    
    $reservas = ReservationsController::getReservations();

    print_r($reservas);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR DURANTE LA PRUEBA: \n";
    echo $e->getMessage();
}