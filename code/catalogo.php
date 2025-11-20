<?php
require __DIR__ . '/vendor/autoload.php';

use Src\Rooms\Infrastructure\Services\RoomsController;

$rooms = [];
$error = null;

try {
    $rooms = RoomsController::getRooms();
} catch (Exception $e) {
    $error = "Error cargando habitaciones.";
}

require_once __DIR__ . '/views/layouts/header.php';
?>
<script>document.getElementById('page-title').innerText = 'Explorar Habitaciones';</script>
<div class="container py-4">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-primary">Nuestras Habitaciones</h1>
        <p class="lead text-muted">Encuentra el espacio perfecto para tu estadía.</p>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

    <div class="row g-4">
        <?php foreach ($rooms as $room): 
            // 1. LÓGICA DE DISPONIBILIDAD PARA EL CLIENTE
            // Solo bloqueamos si está dañada (Mantenimiento/Bloqueada).
            // Si está Ocupada o Sucia, igual la mostramos como "Disponible" para reservas futuras.
            $estadoReal = $room['room_state'];
            $isBookable = ($estadoReal !== 'Mantenimiento' && $estadoReal !== 'Bloqueada');

            // 2. Imágenes simuladas
            $img = "https://via.placeholder.com/400x250/0d6efd/ffffff?text=" . urlencode($room['room_type']);
            if(stripos($room['room_type'], 'Suite') !== false) $img = "https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=400&h=250";
            elseif(stripos($room['room_type'], 'Doble') !== false) $img = "https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=400&h=250";
            else $img = "https://images.unsplash.com/photo-1631049307264-da0f29ca2691?auto=format&fit=crop&w=400&h=250";
        ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0 overflow-hidden card-hover">
                <img src="<?= $img ?>" class="card-img-top" alt="Habitación">
                
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title fw-bold mb-0">Habitación <?= htmlspecialchars($room['room_name']) ?></h5>
                        <span class="badge bg-light text-dark border"><?= htmlspecialchars(ucfirst($room['room_type'])) ?></span>
                    </div>
                    <p class="card-text text-muted small">
                        Capacidad: <?= $room['room_capacity'] ?> personas <br>
                        Wifi, TV, Aire Acondicionado.
                    </p>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <span class="h5 mb-0 text-primary fw-bold">$<?= number_format($room['room_price'], 0) ?> <small class="text-muted fs-6">/ noche</small></span>
                        
                        <?php if ($isBookable): ?>
                            <a href="reservar_cliente.php?room_id=<?= $room['room_id'] ?>" class="btn btn-primary">
                                Reservar Ahora
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>No Disponible</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer bg-white border-top-0 pb-3">
                    <?php if ($isBookable): ?>
                        <small class="text-success fw-bold">
                            <i class="fa-solid fa-circle"></i> Disponible
                        </small>
                    <?php else: ?>
                        <small class="text-danger fw-bold">
                            <i class="fa-solid fa-circle-xmark"></i> En Mantenimiento
                        </small>
                    <?php endif; ?>
                </div>

            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container py-5 mt-4 bg-white rounded shadow-sm">
    <h3 class="mb-4 text-center">Servicios Adicionales</h3>
    <?php 
        use Src\Products\Infrastructure\Services\ProductsController;
        try {
            $products = array_slice(ProductsController::getProducts(), 0, 4);
            echo '<div class="row text-center">';
            foreach($products as $p) {
                echo '<div class="col-md-3 mb-3"><div class="p-3 border rounded">';
                echo '<i class="fa-solid fa-star text-warning mb-2"></i>';
                echo '<h6 class="fw-bold">'.htmlspecialchars($p['product_name']).'</h6>';
                echo '<span class="text-success">$'.number_format($p['product_price'],0).'</span>';
                echo '</div></div>';
            }
            echo '</div>';
        } catch(Exception $e) {}
    ?>
</div>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>