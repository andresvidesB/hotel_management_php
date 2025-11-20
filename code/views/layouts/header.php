<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar estado del usuario
$isLoggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role_id'] ?? 'guest'; // 'guest', '1', '2', '3'
$roleName = $_SESSION['role_name'] ?? 'Invitado';
$current_page = basename($_SERVER['PHP_SELF']);

// Helper para clase activa
function isActive($pageName, $current) {
    return $current === $pageName ? 'active shadow-sm' : 'link-dark';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { min-height: 100vh; background: white; border-right: 1px solid #dee2e6; position: sticky; top: 0; display: flex; flex-direction: column; }
        .nav-link { border-radius: 8px; transition: all 0.3s ease; margin-bottom: 5px; font-weight: 500; display: flex; align-items: center; }
        .nav-link:hover:not(.active) { background-color: #f8f9fa; color: #0d6efd !important; transform: translateX(5px); }
        .nav-link.active { background-color: #0d6efd; color: white !important; font-weight: 600; }
        .nav-link i { width: 25px; text-align: center; }
        .card { border-radius: 12px; border: none; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-3 sidebar d-none d-md-block">
            <div class="text-center mb-4 pt-2">
                <h4 class="text-primary fw-bold"><i class="fa-solid fa-hotel"></i> Hotel System</h4>
            </div>
            <hr class="mb-3 text-secondary opacity-25">

            <div class="nav flex-column nav-pills flex-grow-1">
                
                <?php if ($role === 'guest'): // --- MENÚ INVITADO --- ?>
                    <a href="catalogo.php" class="nav-link <?= isActive('catalogo.php', $current_page) ?>">
                        <i class="fa-solid fa-search me-2"></i> Explorar Habitaciones
                    </a>
                    <div class="mt-4 px-2 text-muted small text-uppercase fw-bold">Acceso</div>
                    <a href="login.php" class="nav-link <?= isActive('login.php', $current_page) ?>">
                        <i class="fa-solid fa-right-to-bracket me-2"></i> Iniciar Sesión
                    </a>
                    <a href="registro.php" class="nav-link <?= isActive('registro.php', $current_page) ?>">
                        <i class="fa-solid fa-user-plus me-2"></i> Registrarse
                    </a>

                <?php elseif ($role === '3'): // --- MENÚ CLIENTE --- ?>
                    <div class="small text-muted text-uppercase fw-bold mb-2 mt-2 px-2">Mi Cuenta</div>
                    <a href="catalogo.php" class="nav-link <?= isActive('catalogo.php', $current_page) ?>">
                        <i class="fa-solid fa-bed me-2"></i> Reservar Habitación
                    </a>
                    <a href="mis_reservas.php" class="nav-link <?= isActive('mis_reservas.php', $current_page) ?>">
                        <i class="fa-solid fa-list me-2"></i> Mis Reservas
                    </a>

                <?php else: // --- MENÚ ADMIN / RECEPCIÓN --- ?>
                    <a href="dashboard.php" class="nav-link <?= isActive('dashboard.php', $current_page) ?>"><i class="fa-solid fa-home me-2"></i> Inicio</a>
                    <?php if($role == '1'): ?>
                        <a href="usuarios.php" class="nav-link <?= isActive('usuarios.php', $current_page) ?>"><i class="fa-solid fa-users me-2"></i> Usuarios</a>
                    <?php endif; ?>
                    <a href="habitaciones.php" class="nav-link <?= isActive('habitaciones.php', $current_page) ?>"><i class="fa-solid fa-bed me-2"></i> Habitaciones</a>
                    <a href="reservas.php" class="nav-link <?= isActive('reservas.php', $current_page) ?>"><i class="fa-solid fa-calendar-check me-2"></i> Reservas</a>
                    <a href="huespedes.php" class="nav-link <?= isActive('huespedes.php', $current_page) ?>"><i class="fa-solid fa-user-tag me-2"></i> Huéspedes</a>
                    <a href="productos.php" class="nav-link <?= isActive('productos.php', $current_page) ?>"><i class="fa-solid fa-box me-2"></i> Productos</a>
                    <a href="consumos.php" class="nav-link <?= isActive('consumos.php', $current_page) ?>"><i class="fa-solid fa-bell-concierge me-2"></i> Consumos</a>
                    <a href="reportes.php" class="nav-link <?= isActive('reportes.php', $current_page) ?>"><i class="fa-solid fa-chart-line me-2"></i> Reportes</a>
                <?php endif; ?>
            </div>

            <div class="mt-auto pt-4 border-top">
                <?php if ($isLoggedIn): ?>
                    <div class="d-flex align-items-center px-2 mb-3">
                        <div class="bg-light rounded-circle p-2 me-2 text-primary"><i class="fa-solid fa-user"></i></div>
                        <div style="line-height: 1.2;">
                            <small class="d-block text-muted" style="font-size: 0.75rem;">Hola,</small>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($roleName) ?></span>
                        </div>
                    </div>
                    <a href="logout.php" class="nav-link link-danger bg-danger bg-opacity-10"><i class="fa-solid fa-sign-out-alt me-2"></i> Salir</a>
                <?php else: ?>
                    <div class="alert alert-info small mb-0">
                        <i class="fa-solid fa-info-circle"></i> Inicia sesión para reservar.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-10 p-4" style="min-height: 100vh;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 text-dark fw-bold mb-0" id="page-title">Bienvenido</h2>
                
                <?php if ($isLoggedIn): ?>
                    <div class="dropdown">
                        <button class="btn btn-white bg-white border dropdown-toggle d-flex align-items-center gap-2 shadow-sm px-3 py-2" type="button" data-bs-toggle="dropdown">
                            <div class="bg-primary text-white rounded-circle d-flex justify-content-center align-items-center" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-user small"></i>
                            </div>
                            <span class="fw-semibold text-dark d-none d-sm-block"><?= htmlspecialchars($roleName) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                            <li><a class="dropdown-item text-danger" href="logout.php"><i class="fa-solid fa-power-off me-2"></i> Salir</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary shadow-sm">Ingresar <i class="fa-solid fa-arrow-right ms-2"></i></a>
                <?php endif; ?>
            </div>