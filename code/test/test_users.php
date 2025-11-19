<?php
// Archivo: code/test_users.php

require __DIR__ . '/vendor/autoload.php';

use Src\Users\Infrastructure\Services\UsersController;
use Src\Roles\Infrastructure\Services\RolesController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

echo "--- Iniciando Prueba de Usuarios --- \n";

try {
    // 1. Necesitamos un Rol válido. Vamos a buscar uno existente.
    $rolesExistentes = RolesController::getRoles();
    
    if (empty($rolesExistentes)) {
        throw new Exception("Error: No hay roles en la base de datos. Ejecuta primero test_roles.php");
    }

    // Tomamos el primer rol que encontremos
    $rolValido = $rolesExistentes[0]; 
    $idRol = $rolValido['role_id'];
    echo "Usando Rol ID existente: " . $idRol . " (" . $rolValido['role_name'] . ") \n";

    // 2. Generamos un ID para el nuevo usuario
    $idCreator = new UuidIdentifierCreator();
    $nuevoId = $idCreator->createIdentifier()->getValue();

    $nuevoUsuario = [
        "user_id_person" => $nuevoId,
        "user_password"  => "super_secret_123", // En la realidad, esto iría hasheado
        "user_role_id"   => $idRol
    ];

    echo "Intentando agregar usuario con ID: $nuevoId \n";
    
    UsersController::addUser($nuevoUsuario);
    
    echo "¡Usuario agregado con éxito! \n";
    echo "---------------------------------------- \n";

    // 3. Obtenemos todos los usuarios
    echo "Obteniendo todos los usuarios de la BD: \n";
    
    $usuarios = UsersController::getUsers();

    print_r($usuarios);

    echo "\n --- Prueba Finalizada --- \n";

} catch (Exception $e) {
    echo "\n !!! ERROR DURANTE LA PRUEBA: \n";
    echo $e->getMessage();
}