<?php
require __DIR__ . '/vendor/autoload.php';

use Src\ReservationProducts\Infrastructure\Services\ReservationProductsController;
use Src\Products\Infrastructure\Services\ProductsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;
use Src\ReservationRooms\Infrastructure\Services\ReservationRoomsController;
use Src\Rooms\Infrastructure\Services\RoomsController;
use Src\ReservationStatus\Infrastructure\Services\ReservationStatusController;

$message = null;
$error = null;

// 1. CARGAR DATOS
try {
    $productos = ProductsController::getProducts();
    $reservasActivas = [];
    $allReservations = ReservationsController::getReservations();
    
    foreach ($allReservations as $res) {
        $resId = $res['reservation_id'];
        if(empty($resId)) continue;

        $statuses = ReservationStatusController::getStatusesByReservation($resId);
        $lastStatus = !empty($statuses) ? end($statuses) : null;
        $isActive = false;
        if ($lastStatus) {
            $sId = $lastStatus['reservation_status_status_id'];
            if ($sId == '10' || $sId == '20') $isActive = true;
        }

        if ($isActive) {
            $rooms = ReservationRoomsController::getRoomsByReservation($resId);
            if (!empty($rooms)) {
                $roomData = $rooms[0];
                $roomName = 'Hab ' . $roomData['reservation_room_room_id'];
                foreach(RoomsController::getRooms() as $h) {
                    if($h['room_id'] == $roomData['reservation_room_room_id']) {
                        $roomName = $h['room_name']; break;
                    }
                }
                $reservasActivas[] = ['id' => $resId, 'room_name' => $roomName];
            }
        }
    }
} catch (Exception $e) { $error = "Error datos: " . $e->getMessage(); }

// 2. ACCIONES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- AGREGAR ---
        if ($_POST['action'] === 'add_consumption') {
            $reservaId = $_POST['reservation_id'];
            $productId = $_POST['product_id'];
            $qty = intval($_POST['quantity']);
            $payNow = isset($_POST['pay_now']); 
            $method = $_POST['payment_method'] ?? 'Efectivo';

            if (empty($reservaId) || empty($productId) || $qty < 1) throw new Exception("Datos incompletos.");

            ReservationProductsController::addConsumption($reservaId, $productId, $qty, $payNow, $method);
            $message = "✅ Consumo registrado.";
        }
        // --- ELIMINAR ---
        elseif ($_POST['action'] === 'delete_consumption') {
            ReservationProductsController::deleteReservationProduct($_POST['reservation_id'], $_POST['product_id']);
            $message = "🗑️ Consumo eliminado/anulado.";
        }
        // --- PAGAR PENDIENTE (MODAL) ---
        elseif ($_POST['action'] === 'pay_consumption') {
            $reservaId = $_POST['reservation_id'];
            $productId = $_POST['product_id'];
            $qty = intval($_POST['quantity']);
            $method = $_POST['payment_method_late']; // Viene del modal
            
            ReservationProductsController::payConsumption($reservaId, $productId, $qty, $method);
            $message = "✅ Pago registrado correctamente ($method).";
        }

    } catch (Exception $e) { $error = "❌ Error: " . $e->getMessage(); }
}

// 3. LISTA
$listaConsumos = [];
try {
    $consumosRaw = ReservationProductsController::getReservationProducts();
    foreach ($consumosRaw as $c) {
        $prodName = 'Desconocido'; $prodPrice = 0;
        foreach ($productos as $p) {
            if ($p['product_id'] == $c['reservation_product_product_id']) {
                $prodName = $p['product_name']; $prodPrice = $p['product_price']; break;
            }
        }
        $habName = substr($c['reservation_product_reservation_id'], 0, 4) . '...';
        foreach ($reservasActivas as $ra) {
            if ($ra['id'] == $c['reservation_product_reservation_id']) { $habName = $ra['room_name']; break; }
        }

        $listaConsumos[] = [
            'reserva_id' => $c['reservation_product_reservation_id'],
            'product_id' => $c['reservation_product_product_id'],
            'hab_name' => $habName,
            'prod_name' => $prodName,
            'qty' => $c['reservation_product_quantity'],
            'total' => $c['reservation_product_quantity'] * $prodPrice,
            'fecha' => $c['reservation_product_consumption_date'],
            'is_paid' => $c['is_paid']
        ];
    }
} catch (Exception $e) {}

require_once __DIR__ . '/views/layouts/header.php';
?>
<script>
    document.getElementById('page-title').innerText = 'Gestión de Consumos';
    
    function confirmDelete(resId, prodId) {
        if(confirm('¿Eliminar este consumo? Si estaba pagado, se generará una anulación.')) {
            document.getElementById('del_res_id').value = resId;
            document.getElementById('del_prod_id').value = prodId;
            document.getElementById('deleteForm').submit();
        }
    }

    function togglePaymentMethod() {
        var check = document.getElementById('pay_now');
        var div = document.getElementById('payment_method_div');
        div.style.display = check.checked ? 'block' : 'none';
    }

    // Función para abrir el Modal de Pago Tardío
    function openPayModal(resId, prodId, qty, prodName, total) {
        document.getElementById('pay_res_id').value = resId;
        document.getElementById('pay_prod_id').value = prodId;
        document.getElementById('pay_qty').value = qty;
        
        // Texto informativo en el modal
        document.getElementById('pay_info_text').innerHTML = 
            "Pagando: <strong>" + qty + " x " + prodName + "</strong><br>" +
            "Total: <span class='text-success fw-bold'>$" + new Intl.NumberFormat().format(total) + "</span>";
        
        // Abrir modal (Bootstrap 5)
        var myModal = new bootstrap.Modal(document.getElementById('modalPayLate'));
        myModal.show();
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Consumos</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="fa-solid fa-cart-plus"></i> Agregar</button>
    </div>
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light"><tr><th>Habitación</th><th>Producto</th><th>Cant.</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php foreach ($listaConsumos as $row): ?>
                    <tr>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($row['hab_name']) ?></span></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['prod_name']) ?></td>
                        <td><?= $row['qty'] ?></td>
                        <td class="text-success fw-bold">$ <?= number_format($row['total'], 2) ?></td>
                        <td>
                            <?php if($row['is_paid']): ?>
                                <span class="badge bg-success">Pagado</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark">A la Cuenta</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if(!$row['is_paid']): ?>
                                <button class="btn btn-sm btn-success me-1" title="Pagar Ahora"
                                        onclick="openPayModal('<?= $row['reserva_id'] ?>', '<?= $row['product_id'] ?>', <?= $row['qty'] ?>, '<?= htmlspecialchars($row['prod_name']) ?>', <?= $row['total'] ?>)">
                                    <i class="fa-solid fa-cash-register"></i>
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $row['reserva_id'] ?>', '<?= $row['product_id'] ?>')"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAdd" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_consumption">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Registrar Consumo</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Habitación</label><select class="form-select" name="reservation_id" required><option value="">-- Seleccione --</option><?php foreach ($reservasActivas as $r): ?><option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['room_name']) ?></option><?php endforeach; ?></select></div>
                    <div class="row mb-3">
                        <div class="col-8"><label>Producto</label><select class="form-select" name="product_id" required><?php foreach ($productos as $p): $dis = ($p['product_stock'] <= 0) ? 'disabled' : ''; $txt = $p['product_name'] . " ($" . $p['product_price'] . ")"; ?><option value="<?= $p['product_id'] ?>" <?= $dis ?>><?= htmlspecialchars($txt) ?></option><?php endforeach; ?></select></div>
                        <div class="col-4"><label>Cant.</label><input type="number" class="form-control" name="quantity" value="1" min="1" required></div>
                    </div>
                    <div class="bg-light p-2 rounded border">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="pay_now" name="pay_now" onclick="togglePaymentMethod()">
                            <label class="form-check-label fw-bold text-success" for="pay_now">¿Pagar Ahora?</label>
                        </div>
                        <div id="payment_method_div" style="display:none;" class="mt-2">
                            <label class="small">Método</label>
                            <select class="form-select form-select-sm" name="payment_method">
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta">Tarjeta</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalPayLate" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="pay_consumption">
                <input type="hidden" name="reservation_id" id="pay_res_id">
                <input type="hidden" name="product_id" id="pay_prod_id">
                <input type="hidden" name="quantity" id="pay_qty">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Pagar Pendiente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p id="pay_info_text" class="mb-3"></p>
                    <div class="text-start">
                        <label class="form-label small fw-bold">Seleccione Método</label>
                        <select class="form-select" name="payment_method_late">
                            <option value="Efectivo">Efectivo</option>
                            <option value="Tarjeta">Tarjeta</option>
                            <option value="Transferencia">Transferencia</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-success w-100">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_consumption">
    <input type="hidden" name="reservation_id" id="del_res_id">
    <input type="hidden" name="product_id" id="del_prod_id">
</form>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>