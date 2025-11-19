<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Hotelero</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card-hover:hover { transform: translateY(-5px); transition: 0.3s; shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important; }
        .sidebar { min-height: 100vh; background: white; border-right: 1px solid #dee2e6; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-3 sidebar d-none d-md-block">
            <h4 class="text-center mb-4 text-primary"><i class="fa-solid fa-hotel"></i> Hotel System</h4>
            <div class="nav flex-column nav-pills">
                <a href="/dashboard.php" class="nav-link active mb-2"><i class="fa-solid fa-home me-2"></i> Inicio</a>
                <a href="/usuarios.php" class="nav-link link-dark mb-2"><i class="fa-solid fa-users me-2"></i> Usuarios</a>
                <a href="/habitaciones.php" class="nav-link link-dark mb-2"><i class="fa-solid fa-bed me-2"></i> Habitaciones</a>
                <a href="/reservas.php" class="nav-link link-dark mb-2"><i class="fa-solid fa-calendar-check me-2"></i> Reservas</a>
                <a href="/huespedes.php" class="nav-link link-dark mb-2"><i class="fa-solid fa-user-tag me-2"></i> Huéspedes</a>
                <a href="/productos.php" class="nav-link link-dark mb-2"><i class="fa-solid fa-box me-2"></i> Productos</a>
            </div>
        </div>

        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="h4 text-muted" id="page-title">Panel Principal</h2>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-circle"></i> Admin
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#">Salir</a></li>
                    </ul>
                </div>
            </div>