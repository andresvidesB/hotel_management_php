<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Reservations\Infrastructure\Services\CheckoutController;

$id = $_GET['id'] ?? '';
if (empty($id)) die("Error: ID de reserva requerido.");

try {
    $info = ReservationsController::getReservaCompleta($id);
    $cuenta = CheckoutController::calculateAccount($id);
} catch (Exception $e) { die("Error: " . $e->getMessage()); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cuenta #<?= substr($id, 0, 6) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 40px; font-family: sans-serif; }
        .invoice { max-width: 850px; margin: auto; background: #fff; padding: 40px; border: 1px solid #ddd; box-shadow: 0 0 15px rgba(0,0,0,0.05); }
        .header { border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: bold; color: #0d6efd; }
        .table th { background: #f8f9fa; }
        .paid-item { color: #198754; font-size: 0.9em; font-style: italic; }
        @media print { body { background: #fff; padding: 0; } .invoice { border: none; box-shadow: none; } .no-print { display: none; } }
    </style>
</head>
<body>

<div class="invoice">
    <div class="row header align-items-end">
        <div class="col-6">
            <div class="logo"><i class="fa-solid fa-hotel"></i> HOTEL SYSTEM</div>
            <div class="text-muted">Calle Principal 123, Ciudad<br>NIT: 900.123.456-7<br>Tel: (601) 123-4567</div>
        </div>
        <div class="col-6 text-end">
            <h3 class="mb-0">CUENTA DE COBRO</h3>
            <div class="fw-bold">No. <?= substr($id, 0, 8) ?></div>
            <div>Fecha: <?= date('d/m/Y H:i') ?></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h6 class="fw-bold text-uppercase text-secondary mb-1">Cliente</h6>
            <div class="fs-5"><?= htmlspecialchars($info['NombreUsuario']) ?></div>
            <div><?= htmlspecialchars($info['CorreoElectronico'] ?? '') ?></div>
        </div>
        <div class="col-6 text-end">
            <h6 class="fw-bold text-uppercase text-secondary mb-1">Estadía</h6>
            <div><strong>Habitación:</strong> <?= htmlspecialchars($info['NombreHabitacion']) ?></div>
            <div>Entrada: <?= $info['FechaInicio'] ?></div>
            <div>Salida: <?= $info['FechaFin'] ?></div>
        </div>
    </div>

    <h5 class="mb-3">Detalle de Cargos</h5>
    <table class="table table-bordered mb-4">
        <thead class="table-light">
            <tr>
                <th>Descripción</th>
                <th class="text-center" width="100">Cant.</th>
                <th class="text-end" width="150">Valor Unit.</th>
                <th class="text-end" width="150">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($cuenta['room_details'] as $item): ?>
            <tr>
                <td>Alojamiento: <?= htmlspecialchars($item['room_name']) ?></td>
                <td class="text-center"><?= $item['nights'] ?> Noches</td>
                <td class="text-end">$ <?= number_format($item['price'], 2) ?></td>
                <td class="text-end">$ <?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>

            <?php foreach($cuenta['products_details'] as $item): ?>
            <tr>
                <td>
                    Consumo: <?= htmlspecialchars($item['product_name']) ?>
                    <?php if($item['status'] === 'Pagado'): ?>
                        <span class="paid-item ms-2"><i class="fa-solid fa-check"></i> Pagado</span>
                    <?php endif; ?>
                </td>
                <td class="text-center"><?= $item['quantity'] ?></td>
                <td class="text-end">$ <?= number_format($item['price'], 2) ?></td>
                <td class="text-end">$ <?= number_format($item['subtotal'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">TOTAL CARGOS</th>
                <th class="text-end">$ <?= number_format($cuenta['grand_total'], 2) ?></th>
            </tr>
        </tfoot>
    </table>

    <div class="row">
        <div class="col-7">
            <h6 class="fw-bold">Historial de Pagos y Abonos</h6>
            <table class="table table-sm table-borderless text-muted small">
                <?php foreach($cuenta['payments_details'] as $pay): ?>
                <tr>
                    <td><?= $pay['date'] ?></td>
                    <td><?= htmlspecialchars($pay['method']) ?></td>
                    <td class="text-end">$ <?= number_format($pay['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($cuenta['payments_details'])): ?>
                    <tr><td>No hay pagos registrados.</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="col-5">
            <div class="bg-light p-3 rounded border">
                <div class="d-flex justify-content-between mb-2">
                    <span>Total Cargos:</span>
                    <strong>$ <?= number_format($cuenta['grand_total'], 2) ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-2 text-success">
                    <span>(-) Total Pagado:</span>
                    <strong>$ <?= number_format($cuenta['paid_total'], 2) ?></strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="fs-5 fw-bold">SALDO:</span>
                    <span class="fs-4 fw-bold <?= $cuenta['balance'] > 0 ? 'text-danger' : 'text-dark' ?>">
                        $ <?= number_format($cuenta['balance'], 2) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5 text-center text-muted small no-print">
        <p>Gracias por su preferencia.</p>
        <button onclick="window.print()" class="btn btn-primary btn-lg px-5">
            <i class="fa-solid fa-print"></i> Imprimir / PDF
        </button>
        <a href="reservas.php" class="btn btn-link text-decoration-none">Volver al sistema</a>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

</body>
</html>