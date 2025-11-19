<?php
// File: code/test_productos.php

require __DIR__ . '/vendor/autoload.php';

use Src\Products\Infrastructure\Services\ProductsController;

echo "--- Iniciando Prueba de Productos --- \n";

try {
    // 1. Vamos a AÑADIR un producto nuevo
    $nuevoProducto = [
        "product_id" => "test-prod-" . uniqid(), // ID de prueba, será reemplazado
        "product_name" => "Tour de Prueba por la Ciudad",
        "product_price" => 80.50
    ];

    echo "Intentando agregar producto: 'Tour de Prueba por la Ciudad' \n";
    
    ProductsController::addProduct($nuevoProducto);
    
    echo "¡Producto agregado con éxito! \n";
    echo "---------------------------------------- \n";

    // 2. Vamos a OBTENER todos los productos
    echo "Obteniendo todos los productos de la BD: \n";
    
    $productos = ProductsController::getProducts();

    print_r($productos);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR DURANTE LA PRUEBA: \n";
    echo $e->getMessage();
}