<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Products\Infrastructure\Services\ProductsController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

$message = null;
$error = null;
$categorias = ['Bebidas', 'Snacks', 'Restaurante', 'Servicios', 'Minibar', 'Baño', 'Otros'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'add_product') {
            $name = trim($_POST['product_name']);
            $price = floatval($_POST['product_price']);
            $stock = intval($_POST['product_stock']);
            $cat = $_POST['product_category'];

            if(empty($name) || $price < 0) throw new Exception("Datos inválidos.");

            $idCreator = new UuidIdentifierCreator();
            ProductsController::addProduct([
                'product_id' => $idCreator->createIdentifier()->getValue(),
                'product_name' => $name,
                'product_price' => $price,
                'product_category' => $cat,
                'product_stock' => $stock
            ]);
            $message = "✅ Producto agregado.";
        }
        elseif ($_POST['action'] === 'edit_product') {
            ProductsController::updateProductWithStock([
                'product_id' => $_POST['product_id'],
                'product_name' => trim($_POST['product_name']),
                'product_price' => floatval($_POST['product_price']),
                'product_category' => $_POST['product_category'],
                'product_stock' => intval($_POST['product_stock'])
            ]);
            $message = "✏️ Inventario actualizado.";
        }
        elseif ($_POST['action'] === 'delete_product') {
            ProductsController::deleteProduct($_POST['product_id']);
            $message = "🗑️ Producto eliminado.";
        }
    } catch (Exception $e) { $error = "❌ Error: " . $e->getMessage(); }
}

try { $productos = ProductsController::getProducts(); } catch (Exception $e) { $productos = []; }
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestión de Inventario';
    function prepareEdit(id, name, price, category, stock) {
        document.getElementById('edit_product_id').value = id;
        document.getElementById('edit_product_name').value = name;
        document.getElementById('edit_product_price').value = price;
        document.getElementById('edit_product_category').value = category;
        document.getElementById('edit_product_stock').value = stock;
    }
    function confirmDelete(id) {
        if(confirm('¿Eliminar?')) {
            document.getElementById('delete_product_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Inventario</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdd"><i class="fa-solid fa-plus"></i> Nuevo Producto</button>
    </div>
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th>Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $prod): 
                        $st = $prod['product_stock'];
                        $badgeClass = 'bg-success';
                        if($st <= 5) $badgeClass = 'bg-danger'; // Crítico
                        elseif($st <= 20) $badgeClass = 'bg-warning text-dark'; // Bajo
                    ?>
                    <tr>
                        <td class="fw-bold"><?= htmlspecialchars($prod['product_name']) ?></td>
                        <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($prod['product_category']) ?></span></td>
                        <td class="text-success">$ <?= number_format($prod['product_price'], 2) ?></td>
                        <td>
                            <span class="badge <?= $badgeClass ?> rounded-pill px-3">
                                <?= $st ?> uds
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#modalEdit"
                                    onclick="prepareEdit('<?= $prod['product_id'] ?>', '<?= htmlspecialchars($prod['product_name']) ?>', '<?= $prod['product_price'] ?>', '<?= htmlspecialchars($prod['product_category']) ?>', '<?= $st ?>')">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $prod['product_id'] ?>')"><i class="fa-solid fa-trash"></i></button>
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
                <input type="hidden" name="action" value="add_product">
                <div class="modal-header bg-primary text-white"><h5 class="modal-title">Nuevo Producto</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Nombre</label><input type="text" class="form-control" name="product_name" required></div>
                    <div class="row mb-3">
                        <div class="col-6"><label>Categoría</label><select class="form-select" name="product_category"><?php foreach ($categorias as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select></div>
                        <div class="col-6"><label>Precio</label><input type="number" class="form-control" name="product_price" step="0.01" required></div>
                    </div>
                    <div class="mb-3"><label>Stock Inicial</label><input type="number" class="form-control" name="product_stock" value="50" required></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Guardar</button></div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-header"><h5 class="modal-title">Editar Inventario</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label>Nombre</label><input type="text" class="form-control" name="product_name" id="edit_product_name" required></div>
                    <div class="row mb-3">
                        <div class="col-6"><label>Categoría</label><select class="form-select" name="product_category" id="edit_product_category"><?php foreach ($categorias as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select></div>
                        <div class="col-6"><label>Precio</label><input type="number" class="form-control" name="product_price" id="edit_product_price" step="0.01" required></div>
                    </div>
                    <div class="mb-3 bg-warning bg-opacity-10 p-2 rounded border border-warning">
                        <label class="fw-bold">Stock Actual</label>
                        <input type="number" class="form-control" name="product_stock" id="edit_product_stock" required>
                        <div class="form-text">Ajuste manual de inventario.</div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-warning">Actualizar</button></div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="product_id" id="delete_product_id"></form>
<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>