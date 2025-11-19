<?php
// Cargar Autoload y la Vista
require __DIR__ . '/vendor/autoload.php';

// Los reportes suelen requerir datos de varios orígenes (Habitaciones, Consumos, etc.)
// En este caso, simularemos los datos del reporte de Limpieza y Daños.

// Datos simulados (basados en el Mockup de Limpieza y Daños, Página 5)
$reportes = [
    [
        'habitacion' => 'Hab 103',
        'detalle'    => 'Luz baño no funciona',
        'estado'     => 'Resuelto',
        'color'      => 'success',
        'fecha'      => date('Y-m-d', strtotime('-2 days'))
    ],
    [
        'habitacion' => 'Hab 201',
        'detalle'    => 'TV dañada',
        'estado'     => 'En proceso',
        'color'      => 'warning',
        'fecha'      => date('Y-m-d', strtotime('-1 day'))
    ],
    [
        'habitacion' => 'Hab 305',
        'detalle'    => 'Minibar vacío',
        'estado'     => 'Resuelto',
        'color'      => 'success',
        'fecha'      => date('Y-m-d')
    ],
    [
        'habitacion' => 'Hab 205',
        'detalle'    => 'Sábanas manchadas',
        'estado'     => 'En proceso',
        'color'      => 'warning',
        'fecha'      => date('Y-m-d')
    ],
];

// Iniciar la Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>document.getElementById('page-title').innerText = 'Reportes de Limpieza y Daños';</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Reportes</li>
                </ol>
            </nav>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
    
    <div class="card-body">
        <h5 class="card-title mb-4">Reportes de Limpieza y Daños</h5>

        <div class="mb-3 text-end">
            <button class="btn btn-warning">
                <i class="fa-solid fa-plus"></i> Nuevo Reporte
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Hab.</th>
                        <th>Detalle</th>
                        <th>Fecha Reporte</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportes)): ?>
                        <tr><td colspan="5" class="text-center">No hay reportes de limpieza o daños pendientes.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportes as $reporte): ?>
                        <tr>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($reporte['habitacion']) ?></span></td>
                            <td><?= htmlspecialchars($reporte['detalle']) ?></td>
                            <td><?= htmlspecialchars($reporte['fecha']) ?></td>
                            <td><span class="badge bg-<?= $reporte['color'] ?>"><?= htmlspecialchars($reporte['estado']) ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-eye"></i> Ver</button>
                                <button class="btn btn-sm btn-outline-success"><i class="fa-solid fa-check"></i> Marcar Resuelto</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>