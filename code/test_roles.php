<?php
// Archivo: code/test_roles.php

require __DIR__ . '/vendor/autoload.php';

use Src\Roles\Infrastructure\Services\RolesController;

echo "--- Iniciando Prueba de Roles --- \n";

try {
    // 1. Vamos a AÑADIR un Rol nuevo
    $nuevoRol = [
        "role_id" => "test-rol-" . uniqid(),
        "role_name" => "Super Admin de Prueba"
    ];

    echo "Intentando agregar rol: 'Super Admin de Prueba' \n";
    
    RolesController::addRole($nuevoRol);
    
    echo "¡Rol agregado con éxito! \n";
    echo "---------------------------------------- \n";

    // 2. Vamos a OBTENER todos los roles
    echo "Obteniendo todos los roles de la BD: \n";
    
    $roles = RolesController::getRoles();

    print_r($roles);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR DURANTE LA PRUEBA: \n";
    echo $e->getMessage();
}