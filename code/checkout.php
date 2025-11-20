<?php
require __DIR__ . '/vendor/autoload.php';

use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\Reservations\Infrastructure\Services\CheckoutController;
use Src\ReservationPayments\Infrastructure\Services\ReservationPaymentsController;

$reservationId = $_GET['id'] ?? '';
$message = null; $error = null;

if (empty($reservationId)) { header('Location: reservas.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalize') {
    try {
        $balance = floatval($_POST['balance']);
        $paymentMethod = $_POST['payment_method'];
        if ($balance > 0) {
            ReservationPaymentsController::addReservationPayment([
                'reservation_payment_reservation_id' => $reservationId,
                'reservation_payment_amount' => $balance,
                'reservation_payment_date' => date('Y-m-d'),
                'reservation_payment_method' => $paymentMethod
            ]);
        }
        CheckoutController::finalizeCheckOut($reservationId);
        $message = "✅ Reserva finalizada correctamente.";
    } catch (Exception $e) { $error = "Error: " . $e->getMessage(); }
}

try {
    $reservaInfo = ReservationsController::getReservaCompleta($reservationId);
    $cuenta = CheckoutController::calculateAccount($reservationId);
} catch (Exception $e) { die("Error: " . $e->getMessage()); }

require_once __DIR__ . '/views/layouts/header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fa-solid fa-cash-register"></i> Estado de Cuenta</h3>
        <a href="reservas.php" class="btn btn-outline-secondary">Volver</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-success text-center py-4 shadow-sm">
            <h2><i class="fa-solid fa-check-circle"></i> ¡Check-Out Exitoso!</h2>
            <p><?= $message ?></p>
            <div class="mt-3">
                <a href="factura.php?id=<?= $reservationId ?>" target="_blank" class="btn btn-primary btn-lg"><i class="fa-solid fa-print"></i> Imprimir Factura</a>
                <a href="habitaciones.php" class="btn btn-outline-success btn-lg">Ir a Habitaciones</a>
            </div>
        </div>
    <?php else: ?>
        
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">Reserva #<?= substr($reservationId, 0, 6) ?></h5>
                            <p class="mb-0 fw-bold"><?= htmlspecialchars($reservaInfo['NombreUsuario']) ?></p>
                            <small class="text-muted"><?= htmlspecialchars($reservaInfo['CorreoElectronico'] ?? '') ?></small>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <h5 class="text-success">Habitación <?= htmlspecialchars($reservaInfo['NombreHabitacion']) ?></h5>
                            <span class="badge bg-light text-dark border"><?= $reservaInfo['FechaInicio'] ?> <i class="fa-solid fa-arrow-right mx-1"></i> <?= $reservaInfo['FechaFin'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">🏨 Alojamiento</div>
                <table class="table table-hover mb-0">
                    <thead><tr><th>Concepto</th><th class="text-center">Noches</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        <?php foreach($cuenta['room_details'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['room_name']) ?></td>
                            <td class="text-center"><?= $item['nights'] ?></td>
                            <td class="text-end fw-bold">$<?= number_format($item['subtotal'], 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-header bg-light fw-bold">🍹 Consumos y Servicios</div>
                <table class="table table-hover mb-0">
                    <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th>Estado</th><th class="text-end">Total</th></tr></thead>
                    <tbody>
                        <?php foreach($cuenta['products_details'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                            <td class="text-center"><?= $item['quantity'] ?></td>
                            <td>
                                <?php if($item['status'] === 'Pagado'): ?>
                                    <span class="badge bg-success-subtle text-success">Pagado</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning">A la cuenta</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">$<?= number_format($item['subtotal'], 0) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($cuenta['products_details'])): ?>
                            <tr><td colspan="4" class="text-center text-muted small">Sin consumos registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success bg-opacity-10 text-success fw-bold">💰 Pagos y Abonos Recibidos</div>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Fecha</th><th>Método</th><th class="text-end">Monto</th></tr></thead>
                    <tbody>
                        <?php foreach($cuenta['payments_details'] as $pay): ?>
                        <tr class="<?= $pay['amount'] < 0 ? 'text-danger' : '' ?>">
                            <td><?= $pay['date'] ?></td>
                            <td><?= htmlspecialchars($pay['method']) ?></td>
                            <td class="text-end fw-bold">
                                <?= $pay['amount'] < 0 ? 'Devolución ' : '' ?>
                                $<?= number_format($pay['amount'], 0) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow sticky-top" style="top: 20px;">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4">Resumen de Cuenta</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Alojamiento:</span>
                        <span>$<?= number_format($cuenta['room_total'], 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Consumos:</span>
                        <span>$<?= number_format($cuenta['products_total'], 0) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-3 fs-5 fw-bold">
                        <span>GRAN TOTAL:</span>
                        <span>$<?= number_format($cuenta['grand_total'], 0) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-3 text-success">
                        <span>(-) Total Pagado:</span>
                        <span>$<?= number_format($cuenta['paid_total'], 0) ?></span>
                    </div>
                    
                    <div class="alert <?= $cuenta['balance'] > 0 ? 'alert-danger' : 'alert-success' ?> text-center">
                        <small class="text-uppercase fw-bold">Saldo Pendiente</small>
                        <h2 class="fw-bold mb-0">$<?= number_format($cuenta['balance'], 0) ?></h2>
                    </div>

                    <form method="POST" onsubmit="return confirm('¿Cerrar cuenta y liberar habitación?');">
                        <input type="hidden" name="action" value="finalize">
                        <input type="hidden" name="balance" value="<?= $cuenta['balance'] ?>">
                        
                        <?php if ($cuenta['balance'] > 0): ?>
                            <div class="mb-3">
                                <label class="form-label fw-bold small">MÉTODO DE PAGO FINAL</label>
                                <select class="form-select" name="payment_method">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                    <option value="Transferencia">Transferencia</option>
                                </select>
                            </div>
                            <button class="btn btn-success w-100 py-2 fw-bold">COBRAR Y SALIDA</button>
                        <?php else: ?>
                            <input type="hidden" name="payment_method" value="N/A">
                            <button class="btn btn-primary w-100 py-2 fw-bold">REGISTRAR SALIDA</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>