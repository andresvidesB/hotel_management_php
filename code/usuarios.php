<?php
// 1. Cargar el Autoload y el Controlador
require __DIR__ . '/vendor/autoload.php';
use Src\Users\Infrastructure\Services\UsersController;
use Src\Roles\Infrastructure\Services\RolesController; // Para mostrar el nombre del rol, no el ID

// 2. Obtener los datos reales de la BD
try {
    $usuarios = UsersController::getUsers();
    // Opcional: Traer roles para mostrar nombres en vez de IDs si quisieras mapearlos
} catch (Exception $e) {
    $error = "Error al cargar usuarios: " . $e->getMessage();
    $usuarios = [];
}

// 3. Iniciar la Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>document.getElementById('page-title').innerText = 'Gestión de Usuarios';</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                </nav>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUsuario">
            <i class="fa-solid fa-plus"></i> Agregar Usuario
        </button>
    </div>
    
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" class="form-control" placeholder="Buscar usuario por nombre o email...">
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th># ID</th> <th>Nombre (Rol)</th> <th>Contraseña (Hash)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="4" class="text-center">No hay usuarios registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['user_id_person']) ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar bg-secondary text-white rounded-circle me-2 d-flex justify-content-center align-items-center" style="width: 35px; height: 35px;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div>
                                        <span class="fw-bold">Usuario (Rol: <?= $user['user_role_id'] ?>)</span>
                                        <div class="text-muted small">ID Persona: <?= $user['user_id_person'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td><code class="text-muted">********</code></td>
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