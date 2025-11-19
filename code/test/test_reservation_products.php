<?php
// Archivo: code/test_reservation_products.php

require __DIR__ . '/vendor/autoload.php';

use Src\ReservationProducts\Infrastructure\Services\ReservationProductsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Products\Infrastructure\Services\ProductsController;

echo "--- Iniciando Prueba de Consumo de Productos --- \n";

try {
    // 1. Buscar Reserva
    $reservas = ReservationsController::getReservations();
    if (empty($reservas)) throw new Exception("Error: No hay reservas.");
    $reservaId = $reservas[0]['reservation_id'];
    echo "Reserva ID: " . $reservaId . "\n";

    // 2. Buscar Producto
    $productos = ProductsController::getProducts();
    if (empty($productos)) throw new Exception("Error: No hay productos. Ejecuta test_productos.php.");
    $productoId = $productos[0]['product_id'];
    $productoNombre = $productos[0]['product_name'];
    echo "Producto ID: " . $productoId . " (" . $productoNombre . ")\n";

    // 3. Agregar Producto a la Reserva (ej: 2 unidades consumidas hoy)
    $data = [
        'reservation_product_reservation_id'   => $reservaId,
        'reservation_product_product_id'       => $productoId,
        'reservation_product_quantity'         => 2,
        'reservation_product_consumption_date' => date('Y-m-d')
    ];

    echo "Agregando consumo de 2 unidades de '$productoNombre'... \n";
    
    ReservationProductsController::addReservationProduct($data);
    
    echo "¡Producto agregado con éxito! \n";
    echo "---------------------------------------- \n";

    // 4. Verificar
    echo "Productos consumidos en la reserva: \n";
    $items = ReservationProductsController::getProductsByReservation($reservaId);
    print_r($items);

} catch (Exception $e) {
    echo "\n !!! ERROR: \n";
    echo $e->getMessage();
}