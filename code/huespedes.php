<?php
// Cargar Autoload y el Controlador
require __DIR__ . '/vendor/autoload.php';
use Src\Guests\Infrastructure\Services\GuestsController;

// Obtener los datos reales de la BD
try {
    $huespedes = GuestsController::getGuests();
    $error = null;
} catch (Exception $e) {
    $error = "Error al cargar huéspedes: " . $e->getMessage();
    $huespedes = [];
}

// Iniciar la Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>document.getElementById('page-title').innerText = 'Gestionar información de huéspedes';</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Huéspedes</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalHuesped">
            <i class="fa-solid fa-user-plus"></i> Agregar huésped
        </button>
    </div>
    
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-5">
                <input type="text" class="form-control" placeholder="Buscar huésped por nombre, documento o habitación">
            </div>
            <div class="col-md-7 d-flex justify-content-end">
                 <h5 class="fw-bold mb-0">Huéspedes alojados</h5>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Huésped (Documento)</th>
                        <th>Hab. No (Sim.)</th>
                        <th>País</th>
                        <th>Días de estadía (Sim.)</th>
                        <th>Días restantes (Sim.)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($huespedes)): ?>
                        <tr><td colspan="6" class="text-center">No hay huéspedes registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($huespedes as $guest): 
                            // LÓGICA DE SIMULACIÓN VISUAL (Datos no presentes en ReadGuest)
                            $randomDays = rand(1, 7);
                            $daysLeft = rand(0, $randomDays);
                            $habNo = (intval(substr(str_replace('-', '', $guest['guest_id_person']), -4)) % 5) * 100 + 101; 
                        ?>
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">Huésped <?= $guest['guest_document_number'] ?></span> 
                                <div class="text-muted small"><?= $guest['guest_document_type'] ?></div>
                            </td>
                            <td><span class="badge bg-secondary"><?= $habNo ?></span></td>
                            <td><?= htmlspecialchars($guest['guest_country'] ?: 'N/A') ?></td>
                            <td><?= $randomDays ?></td>
                            <td><?= $daysLeft ?> días</td>
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