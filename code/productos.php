<?php
// Cargar Autoload y el Controlador
require __DIR__ . '/vendor/autoload.php';
use Src\Products\Infrastructure\Services\ProductsController;

// Obtener los datos reales de la BD
try {
    $productos = ProductsController::getProducts();
    $error = null;
} catch (Exception $e) {
    $error = "Error al cargar productos: " . $e->getMessage();
    $productos = [];
}

// Iniciar la Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>document.getElementById('page-title').innerText = 'Gestión de Productos';</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Productos</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalProducto">
            <i class="fa-solid fa-plus"></i> Añadir Producto
        </button>
    </div>
    
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Buscar productos por nombre...">
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Producto</th>
                        <th>Categoría (Sim.)</th>
                        <th>Precio</th>
                        <th>Stock (Sim.)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr><td colspan="5" class="text-center">No hay productos registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($productos as $product): 
                            // LÓGICA DE SIMULACIÓN VISUAL (No están en ReadProduct)
                            $categories = ['Bebidas', 'Snacks', 'Servicios', 'Baño'];
                            $category = $categories[rand(0, 3)];
                            $stock = rand(0, 150) . ' uds';
                        ?>
                        <tr>
                            <td><span class="fw-bold"><?= htmlspecialchars($product['product_name']) ?></span></td>
                            <td><?= $category ?></td>
                            <td>$ <?= number_format($product['product_price'] * 4000, 0) ?> COP</td> 
                            <td><?= $stock ?></td>
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