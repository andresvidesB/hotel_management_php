<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Shared\Infrastructure\Services\ReportService;

$error = null;
$totalCaja = 0;
$ventaHospedaje = 0;
$ventaConsumo = 0;
$statsHabitaciones = [];
$ultimosPagos = [];

// Fechas
$fechaInicio = $_GET['start'] ?? date('Y-m-01');
$fechaFin = $_GET['end'] ?? date('Y-m-t');

try {
    $service = new ReportService();
    
    // 1. Obtener Datos Desglosados
    $totalCaja = $service->getCashFlow($fechaInicio, $fechaFin);
    $ventaHospedaje = $service->getAccommodationSales($fechaInicio, $fechaFin);
    $ventaConsumo = $service->getConsumptionSales($fechaInicio, $fechaFin);
    
    $statsHabitaciones = $service->getRoomStats();
    $ultimosPagos = $service->getRecentPayments($fechaInicio, $fechaFin);

    // Gráfico
    $labels = []; $data = []; $colors = [];
    foreach ($statsHabitaciones as $stat) {
        $labels[] = $stat['Estado'];
        $data[] = $stat['Total'];
        if ($stat['Estado'] === 'Disponible') $colors[] = '#198754';
        elseif ($stat['Estado'] === 'Ocupada') $colors[] = '#0d6efd';
        elseif ($stat['Estado'] === 'Mantenimiento') $colors[] = '#dc3545';
        else $colors[] = '#ffc107';
    }

} catch (Exception $e) { $error = "Error: " . $e->getMessage(); }

require_once __DIR__ . '/views/layouts/header.php';
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    @media print {
        .sidebar, .btn, form, .no-print { display: none !important; }
        .col-md-10 { width: 100% !important; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    }
</style>

<script>document.getElementById('page-title').innerText = 'Reportes Financieros';</script>

<div class="container-fluid">
    <div class="card mb-4 border-0 shadow-sm no-print">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3"><label class="small fw-bold text-muted">Desde</label><input type="date" class="form-control" name="start" value="<?= $fechaInicio ?>"></div>
                <div class="col-md-3"><label class="small fw-bold text-muted">Hasta</label><input type="date" class="form-control" name="end" value="<?= $fechaFin ?>"></div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i> Filtrar</button>
                    <button type="button" onclick="window.print()" class="btn btn-outline-danger w-100"><i class="fa-solid fa-file-pdf"></i> PDF</button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-none d-print-block mb-4">
        <h2>Reporte de Gestión</h2>
        <p>Periodo: <?= date('d/m/Y', strtotime($fechaInicio)) ?> - <?= date('d/m/Y', strtotime($fechaFin)) ?></p>
        <hr>
    </div>

    <div class="row mb-4">
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success fw-bold text-uppercase mb-1">Caja Total (Recuado)</h6>
                            <h2 class="mb-0 fw-bold text-success">$<?= number_format($totalCaja, 0) ?></h2>
                        </div>
                        <div class="bg-white p-3 rounded-circle text-success shadow-sm"><i class="fa-solid fa-sack-dollar fa-2x"></i></div>
                    </div>
                    <small class="text-muted">Dinero real ingresado (Pagos)</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-primary fw-bold text-uppercase mb-1">Ventas Hospedaje</h6>
                            <h2 class="mb-0 fw-bold text-primary">$<?= number_format($ventaHospedaje, 0) ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary"><i class="fa-solid fa-bed fa-2x"></i></div>
                    </div>
                    <small class="text-muted">Valor de noches vendidas</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning fw-bold text-uppercase mb-1">Ventas Consumo</h6>
                            <h2 class="mb-0 fw-bold text-dark">$<?= number_format($ventaConsumo, 0) ?></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning"><i class="fa-solid fa-martini-glass fa-2x"></i></div>
                    </div>
                    <small class="text-muted">Cafeteria y servicios vendidos</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Ocupación Actual</div>
                <div class="card-body"><canvas id="roomChart" style="max-height: 250px;"></canvas></div>
            </div>
        </div>

        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Detalle de Ingresos (Caja)</div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light"><tr><th>Fecha</th><th>Ref</th><th>Método</th><th class="text-end">Monto</th></tr></thead>
                        <tbody>
                            <?php foreach ($ultimosPagos as $pago): ?>
                            <tr>
                                <td><?= date('d/m H:i', strtotime($pago['FechaPago'])) ?></td>
                                <td><?= htmlspecialchars($pago['Habitacion'] ?? 'Varios') ?></td>
                                <td><small><?= htmlspecialchars($pago['MetodoPago']) ?></small></td>
                                <td class="text-end fw-bold <?= $pago['Cantidad'] < 0 ? 'text-danger' : 'text-success' ?>">
                                    $<?= number_format($pago['Cantidad'], 0) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    new Chart(document.getElementById('roomChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{ data: <?= json_encode($data) ?>, backgroundColor: <?= json_encode($colors) ?> }]
        },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right' } } }
    });
</script>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>