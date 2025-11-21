<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Control de Roles
// Definimos roles: 1=Admin, 2=Recepcionista, 3=Cliente
$role = $_SESSION['role_id'];

// Obtenemos el nombre del archivo actual
$current_script = basename($_SERVER['PHP_SELF']);

//  REGLAS DE ACCESO

// Páginas exclusivas de Admin y Recepción
$admin_pages = ['dashboard.php', 'usuarios.php', 'huespedes.php', 'consumos.php', 'reportes.php', 'checkout.php'];

// Si un cliente intenta entrar a páginas de admin, lo mandamos a su catálogo
if ($role == '3' && in_array($current_script, $admin_pages)) {
    header('Location: catalogo.php');
    exit;
}
?>