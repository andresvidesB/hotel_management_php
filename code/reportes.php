<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Shared\Infrastructure\Services\ReportService;

$error = null;
$statsIngresos = ['range_total' => 0, 'today' => 0];
$statsHabitaciones = [];
$ultimosPagos = [];

// Definir rango de fechas (Por defecto: Mes actual)
$fechaInicio = $_GET['start'] ?? date('Y-m-01');
$fechaFin = $_GET['end'] ?? date('Y-m-t');

try {
    $service = new ReportService();
    
    // Cargar datos filtrados
    $statsIngresos = $service->getIncomeStats($fechaInicio, $fechaFin);
    $statsHabitaciones = $service->getRoomStats(); // Estado actual
    $ultimosPagos = $service->getRecentPayments($fechaInicio, $fechaFin);

    // Datos para Gráfico
    $labels = []; $data = []; $colors = [];
    foreach ($statsHabitaciones as $stat) {
        $labels[] = $stat['Estado'];
        $data[] = $stat['Total'];
        if ($stat['Estado'] === 'Disponible') $colors[] = '#198754';
        elseif ($stat['Estado'] === 'Ocupada') $colors[] = '#0d6efd';
        elseif ($stat['Estado'] === 'Mantenimiento') $colors[] = '#dc3545';
        elseif ($stat['Estado'] === 'Limpieza') $colors[] = '#ffc107';
        else $colors[] = '#6c757d';
    }

} catch (Exception $e) { $error = "Error: " . $e->getMessage(); }

require_once __DIR__ . '/views/layouts/header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    /* ESTILOS SOLO PARA IMPRESIÓN (PDF) */
    @media print {
        @page { size: landscape; margin: 10mm; } /* Hoja horizontal para que quepa mejor */
        body { background: #fff; font-family: Arial, sans-serif; -webkit-print-color-adjust: exact; }
        
        /* Ocultar elementos no deseados en el PDF */
        .sidebar, .btn, form, .no-print, .navbar, header { display: none !important; }
        
        /* Ajustar el contenido al ancho completo */
        .col-md-10 { width: 100% !important; flex: 0 0 100%; max-width: 100%; padding: 0; margin: 0; }
        .card { border: 1px solid #ddd !important; box-shadow: none !important; break-inside: avoid; }
        
        /* Ajustar tamaños para papel */
        h2#page-title { font-size: 24px; margin-bottom: 20px; color: #000; }
        .badge { border: 1px solid #000; color: #000 !important; }
        
        /* Forzar visualización del gráfico */
        canvas { max-height: 300px !important; width: 100% !important; }
    }
</style>

<script>document.getElementById('page-title').innerText = 'Reporte Financiero y Operativo';</script>

<div class="container-fluid">

    <div class="card mb-4 border-0 shadow-sm no-print">
        <div class="card-body py-3">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Fecha Inicio</label>
                    <input type="date" class="form-control" name="start" value="<?= $fechaInicio ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted">Fecha Fin</label>
                    <input type="date" class="form-control" name="end" value="<?= $fechaFin ?>">
                </div>
                <div class="col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                    <button type="button" onclick="window.print()" class="btn btn-outline-danger w-100">
                        <i class="fa-solid fa-file-pdf"></i> Descargar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="d-none d-print-block mb-4">
        <h1>Reporte General del Hotel</h1>
        <p>Periodo: <strong><?= date('d/m/Y', strtotime($fechaInicio)) ?></strong> al <strong><?= date('d/m/Y', strtotime($fechaFin)) ?></strong></p>
        <hr>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Ingresos del Periodo</h6>
                            <h2 class="mb-0 fw-bold text-primary">$<?= number_format($statsIngresos['range_total'], 0) ?></h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary no-print">
                            <i class="fa-solid fa-calendar-days fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Total recaudado en las fechas seleccionadas</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Caja del Día (Hoy)</h6>
                            <h2 class="mb-0 fw-bold text-success">$<?= number_format($statsIngresos['today'], 0) ?></h2>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded-circle text-success no-print">
                            <i class="fa-solid fa-cash-register fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Ingresos registrados hoy <?= date('d/m') ?></small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted text-uppercase mb-1">Total Habitaciones</h6>
                            <h2 class="mb-0 fw-bold text-dark"><?= array_sum($data) ?></h2>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning no-print">
                            <i class="fa-solid fa-bed fa-2x"></i>
                        </div>
                    </div>
                    <small class="text-muted">Inventario físico actual</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Estado Actual de Habitaciones</div>
                <div class="card-body d-flex justify-content-center align-items-center">
                    <div style="width: 100%; max-height: 250px;">
                        <canvas id="roomChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-bold">Detalle de Movimientos (Periodo Seleccionado)</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0 align-middle" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Habitación / Concepto</th>
                                    <th>Método</th>
                                    <th class="text-end">Monto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ultimosPagos as $pago): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($pago['FechaPago'])) ?></td>
                                    <td><?= htmlspecialchars($pago['Habitacion'] ?? 'Varios') ?></td>
                                    <td><?= htmlspecialchars($pago['MetodoPago']) ?></td>
                                    <td class="text-end fw-bold <?= $pago['Cantidad'] < 0 ? 'text-danger' : 'text-success' ?>">
                                        $<?= number_format($pago['Cantidad'], 0) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($ultimosPagos)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No hay movimientos en este rango.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Renderizar Gráfico
    const ctx = document.getElementById('roomChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                data: <?= json_encode($data) ?>,
                backgroundColor: <?= json_encode($colors) ?>,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12 } }
            }
        }
    });
</script>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>