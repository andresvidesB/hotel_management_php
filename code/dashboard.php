<?php
// 1. Incluir el Header
require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="row g-4">
    
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm card-hover">
            <div class="card-body d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded me-3">
                    <i class="fa-solid fa-users fa-2x text-primary"></i>
                </div>
                <div>
                    <h5 class="card-title mb-1">Usuarios</h5>
                    <p class="card-text text-muted small">Gestionar usuarios del sistema</p>
                    <a href="usuarios.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm card-hover">
            <div class="card-body d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded me-3">
                    <i class="fa-solid fa-bed fa-2x text-success"></i>
                </div>
                <div>
                    <h5 class="card-title mb-1">Habitaciones</h5>
                    <p class="card-text text-muted small">Gestionar habitaciones del hotel</p>
                    <a href="habitaciones.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm card-hover">
            <div class="card-body d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded me-3">
                    <i class="fa-solid fa-chart-line fa-2x text-warning"></i>
                </div>
                <div>
                    <h5 class="card-title mb-1">Reportes</h5>
                    <p class="card-text text-muted small">Ver reportes del sistema</p>
                    <a href="#" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm card-hover">
            <div class="card-body d-flex align-items-center">
                <div class="bg-danger bg-opacity-10 p-3 rounded me-3">
                    <i class="fa-solid fa-calendar-check fa-2x text-danger"></i>
                </div>
                <div>
                    <h5 class="card-title mb-1">Reservas</h5>
                    <p class="card-text text-muted small">Gestionar reservas</p>
                    <a href="reservas.php" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
// 2. Incluir el Footer
require_once __DIR__ . '/views/layouts/footer.php';
?>