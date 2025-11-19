<?php
// 1. Cargar Autoload y Controladores
require __DIR__ . '/vendor/autoload.php';
use Src\Users\Infrastructure\Services\UsersController;
use Src\Roles\Infrastructure\Services\RolesController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;

$message = null; // Mensajes de éxito
$error = null;   // Mensajes de error

// 2. Traer la lista de Roles (para los selects)
try {
    $roles = RolesController::getRoles();
} catch (Exception $e) {
    $error = "Error al cargar roles: " . $e->getMessage();
    $roles = [];
}

// 3. MANEJO DE ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- ACCIÓN: AGREGAR USUARIO ---
        if ($_POST['action'] === 'add_user') {
            $password = trim($_POST['password'] ?? '');
            $roleId = trim($_POST['role_id'] ?? '');

            if (empty($password) || empty($roleId)) {
                throw new Exception("Contraseña y Rol son obligatorios.");
            }

            $idCreator = new UuidIdentifierCreator();
            $userId = $idCreator->createIdentifier()->getValue();
            
            $userData = [
                'user_id_person' => $userId,
                'user_password' => $password, 
                'user_role_id' => $roleId
            ];

            UsersController::addUser($userData);
            $message = "✅ Usuario agregado con éxito.";
        }

        // --- ACCIÓN: EDITAR USUARIO ---
        elseif ($_POST['action'] === 'edit_user') {
            $id = $_POST['user_id'] ?? '';
            $password = trim($_POST['password'] ?? '');
            $roleId = $_POST['role_id'] ?? '';

            if (empty($id) || empty($roleId)) {
                throw new Exception("ID y Rol son obligatorios.");
            }

            // Si la contraseña está vacía, buscamos el usuario actual para mantener la vieja
            if (empty($password)) {
                $currentUser = UsersController::getUserByIdPerson($id);
                if (empty($currentUser)) throw new Exception("Usuario no encontrado.");
                $password = $currentUser['user_password'];
            }

            $userData = [
                'user_id_person' => $id,
                'user_password' => $password,
                'user_role_id' => $roleId
            ];

            UsersController::updateUser($userData);
            $message = "✏️ Usuario actualizado con éxito.";
        }

        // --- ACCIÓN: ELIMINAR USUARIO ---
        elseif ($_POST['action'] === 'delete_user') {
            $id = $_POST['user_id'] ?? '';
            if (empty($id)) throw new Exception("ID de usuario inválido.");

            UsersController::deleteUser($id);
            $message = "🗑️ Usuario eliminado correctamente.";
        }

    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// 4. Obtener Usuarios (Lista actualizada)
try {
    $usuarios = UsersController::getUsers();
} catch (Exception $e) {
    $error = ($error ? $error . " | " : "") . "Error al listar usuarios: " . $e->getMessage();
    $usuarios = [];
}

// 5. Renderizar la Vista
require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestión de Usuarios';

    // Función para llenar el modal de edición con datos del usuario
    function prepareEditUser(id, roleId) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_role_id').value = roleId;
        document.getElementById('edit_password').value = ''; // Limpiar password
    }

    // Función para confirmar eliminación
    function confirmDelete(id) {
        if(confirm('¿Está seguro de que desea eliminar este usuario? Esta acción no se puede deshacer.')) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="dashboard.php">Inicio</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Usuarios</li>
                </ol>
            </nav>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
            <i class="fa-solid fa-plus"></i> Agregar Usuario
        </button>
    </div>
    
    <div class="card-body">
        
        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th># ID (Tercero)</th>
                        <th>Contraseña</th>
                        <th>Rol ID</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="4" class="text-center">No hay usuarios registrados</td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $user): ?>
                        <tr>
                            <td><small><?= htmlspecialchars($user['user_id_person']) ?></small></td>
                            <td><code class="text-muted">********</code></td>
                            <td>
                                <?php 
                                    $roleName = $user['user_role_id']; // Default
                                    foreach($roles as $r) {
                                        if($r['role_id'] == $user['user_role_id']) {
                                            $roleName = $r['role_name'];
                                            break;
                                        }
                                    }
                                ?>
                                <span class="badge bg-info text-dark"><?= htmlspecialchars($roleName) ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditUser"
                                        onclick="prepareEditUser('<?= $user['user_id_person'] ?>', '<?= $user['user_role_id'] ?>')">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-outline-danger" 
                                        onclick="confirmDelete('<?= $user['user_id_person'] ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fa-solid fa-info-circle"></i> Se creará automáticamente un registro en la tabla Terceros con nombre genérico.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_user_id"> 
                
                <div class="modal-header">
                    <h5 class="modal-title">Editar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="password" id="edit_password" placeholder="(Dejar en blanco para no cambiar)">
                        <div class="form-text">Si no desea cambiar la contraseña, deje este campo vacío.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role_id" id="edit_role_id" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    <input type="hidden" name="action" value="delete_user">
    <input type="hidden" name="user_id" id="delete_user_id">
</form>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>