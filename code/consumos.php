<?php
// Cargar Autoload y el Controlador
require __DIR__ . '/vendor/autoload.php';
use Src\ReservationProducts\Infrastructure\Services\ReservationProductsController;

// Obtener los datos reales de la BD
try {
    $consumos = ReservationProductsController::getReservationProducts();
    $error = null;
} catch (Exception $e) {
    $error = "Error al cargar consumos: " . $e->getMessage();
    $consumos = [];
}

// Iniciar la Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>document.getElementById('page-title').innerText = 'Gestión de Consumos';</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Consumos</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalConsumo">
            <i class="fa-solid fa-plus"></i> Añadir Consumo
        </button>
    </div>
    
    <div class="card-body">
        <div class="row mb-3 align-items-center">
            <div class="col-md-6">
                <input type="text" class="form-control" placeholder="Buscar consumos">
            </div>
            <div class="col-md-6 text-end">
                <a href="#" class="btn btn-outline-warning">
                    <i class="fa-solid fa-exclamation-triangle"></i> Reportar Daños
                </a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Reserva ID</th>
                        <th>Hab (Sim.)</th>
                        <th>Producto ID</th>
                        <th>Cantidad</th>
                        <th>Monto (Sim.)</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($consumos)): ?>
                        <tr><td colspan="7" class="text-center">No hay consumos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($consumos as $consumo): 
                            // LÓGICA DE SIMULACIÓN VISUAL (Monto y Hab)
                            $monto = $consumo['reservation_product_quantity'] * 70000; 
                            $estado = (rand(0,1) == 0) ? 'Pendiente' : 'Pagado';
                            $estado_color = ($estado == 'Pendiente') ? 'warning' : 'success';
                            $habSimulada = intval(substr($consumo['reservation_product_reservation_id'], -2)) * 10 + 100;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($consumo['reservation_product_reservation_id']) ?></td>
                            <td><span class="badge bg-secondary">Hab <?= $habSimulada ?></span></td>
                            <td><?= htmlspecialchars($consumo['reservation_product_product_id']) ?></td>
                            <td><?= htmlspecialchars($consumo['reservation_product_quantity']) ?></td>
                            <td>$ <?= number_format($monto, 0) ?> COP</td>
                            <td><span class="badge bg-<?= $estado_color ?>"><?= $estado ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1"><i class="fa-solid fa-pen"></i> Editar</button>
                                <button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> Eliminar</button>
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