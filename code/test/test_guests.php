<?php
// Archivo: code/test_guests.php

require __DIR__ . '/vendor/autoload.php';

use Src\Guests\Infrastructure\Services\GuestsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

echo "--- Iniciando Prueba de Huéspedes --- \n";

try {
    // 1. Generamos un ID válido (UUID)
    // Es importante que sea un UUID válido para que tu Identifier no se queje
    $idCreator = new UuidIdentifierCreator();
    $nuevoId = $idCreator->createIdentifier()->getValue();

    $nuevoHuesped = [
        "guest_id_person" => $nuevoId,
        "guest_document_type" => "PASAPORTE",
        "guest_document_number" => "AB-987654",
        "guest_country" => "Mexico"
    ];

    echo "Intentando agregar huésped con ID: $nuevoId \n";
    
    GuestsController::addGuest($nuevoHuesped);
    
    echo "¡Huésped agregado con éxito! \n";
    echo "---------------------------------------- \n";

    // 2. Obtenemos todos los huéspedes
    echo "Obteniendo todos los huéspedes de la BD: \n";
    
    $guests = GuestsController::getGuests();

    print_r($guests);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR DURANTE LA PRUEBA: \n";
    echo $e->getMessage();
}