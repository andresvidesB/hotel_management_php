<?php
declare(strict_types=1);

namespace Src\Shared\Infrastructure\Services;

use Src\Users\Infrastructure\Services\UsersController;
use Src\Roles\Infrastructure\Services\RolesController;

final class AuthService
{
    public static function login(string $id, string $password): array
    {
        // 1. Buscar usuario por ID
        $user = UsersController::getUserByIdPerson($id);

        if (empty($user)) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        // 2. Verificar Contraseña
        // NOTA: En un entorno real usaríamos password_verify($password, $hash).
        // Como tus datos de prueba actuales son texto plano (ej: "12345"), 
        // haremos una comprobación híbrida para que no se rompa tu demo.
        
        $dbPass = $user['user_password'];
        $isPasswordCorrect = false;

        // Si la contraseña en BD es igual a la escrita (texto plano) O si el hash coincide
        if ($dbPass === $password || password_verify($password, $dbPass)) {
            $isPasswordCorrect = true;
        }

        if (!$isPasswordCorrect) {
            return ['success' => false, 'message' => 'Contraseña incorrecta.'];
        }

        // 3. Obtener Rol para saber permisos
        $role = RolesController::getRoleById($user['user_role_id']);
        $roleName = $role['role_name'] ?? 'Cliente';

        // 4. Iniciar Sesión PHP
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        $_SESSION['user_id'] = $user['user_id_person'];
        $_SESSION['role_id'] = $user['user_role_id'];
        $_SESSION['role_name'] = $roleName;
        
        // Guardamos el nombre buscando en la Vista o tabla Terceros si es posible, 
        // por ahora usaremos el ID como nombre de sesión si no tenemos el nombre a mano
        $_SESSION['user_name'] = $user['user_id_person']; 

        return ['success' => true, 'role' => $roleName];
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
    }
}