<?php

// 1. Incluimos el autoloader de Composer
// Esto asume que estás en la carpeta 'code/'
require __DIR__ . '/vendor/autoload.php';

// 2. Importamos el controlador y la clase para crear IDs
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

echo "--- Iniciando Prueba de Habitaciones --- \n";

try {
    // 3. Vamos a AÑADIR una habitación nueva
    
    // Creamos un ID aleatorio para la prueba
    $idCreator = new UuidIdentifierCreator();
    // NOTA: El ID que genera tu clase está vacío (''), 
    // así que lo forzaremos para la prueba.
    // $nuevoId = $idCreator->createIdentifier()->getValue();
    
    // Vamos a usar un ID aleatorio de PHP por ahora
    $nuevoId = 'test-id-' . uniqid();


    $nuevaHabitacion = [
        "room_id" => $nuevoId,
        "room_name" => "Suite Presidencial de Prueba",
        "room_type" => "suite",
        "room_price" => 499.99,
        "room_capacity" => 6
    ];

    echo "Intentando agregar habitación: 'Suite Presidencial de Prueba' (ID: $nuevoId) \n";
    
    // 4. Llamamos al controlador (que usará nuestro repositorio REAL)
    RoomsController::addRoom($nuevaHabitacion); // <--- ERROR INTENCIONAL PARA ARREGLAR
    
    echo "¡Habitación agregada con éxito! \n";
    echo "---------------------------------------- \n";

    // 5. Vamos a OBTENER todas las habitaciones
    echo "Obteniendo todas las habitaciones de la BD: \n";
    
    $habitaciones = RoomsController::getRooms();

    // 6. Imprimimos los resultados
    print_r($habitaciones);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR DURANTE LA PRUEBA: \n";
    echo $e->getMessage();
}