<?php
// 1. Cargar Autoload y Controladores
require __DIR__ . '/vendor/autoload.php';
use Src\Users\Infrastructure\Services\UsersController;
use Src\Roles\Infrastructure\Services\RolesController;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Guests\Infrastructure\Services\GuestsController;

$message = null; // Mensajes de éxito
$error = null;   // Mensajes de error

// 2. Traer la lista de Roles
try {
    $roles = RolesController::getRoles();
} catch (Exception $e) {
    $error = "Error al cargar roles: " . $e->getMessage();
    $roles = [];
}

// 3. MANEJO DE ACCIONES (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        // --- ACCIÓN: AGREGAR USUARIO COMPLETO ---
        if ($_POST['action'] === 'add_user') {
            // Recoger datos
            $password = trim($_POST['password'] ?? '');
            $roleId   = trim($_POST['role_id'] ?? '');
            $nombres  = trim($_POST['nombres'] ?? '');
            $apellidos= trim($_POST['apellidos'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $cc       = trim($_POST['cc'] ?? '');

            if (empty($password) || empty($roleId) || empty($nombres)) {
                throw new Exception("Nombre, Contraseña y Rol son obligatorios.");
            }

            // Generar ID
            $idCreator = new UuidIdentifierCreator();
            $userId = $idCreator->createIdentifier()->getValue();
            
            // 1. Crear Usuario (Login)
            $userData = [
                'user_id_person' => $userId,
                'user_password' => $password, 
                'user_role_id' => $roleId
            ];
            UsersController::addUser($userData);

            // 2. Guardar Datos Personales (Nombre, Apellido, Email)
            UsersController::savePersonData($userId, $nombres, $apellidos, $email);

            // 3. Si puso Cédula, crear registro en Huéspedes (Opcional pero útil)
            if (!empty($cc)) {
                try {
                    GuestsController::addGuest([
                        'guest_id_person' => $userId,
                        'guest_document_type' => 'CC',
                        'guest_document_number' => $cc,
                        'guest_country' => 'Desconocido'
                    ]);
                } catch (Exception $ex) {
                    // Si falla el huésped (ej. duplicado), no bloqueamos la creación del usuario
                }
            }

            $message = "✅ Usuario y Datos Personales guardados con éxito.";
        }

        // --- ACCIÓN: EDITAR USUARIO ---
        elseif ($_POST['action'] === 'edit_user') {
            $id = $_POST['user_id'] ?? '';
            $password = trim($_POST['password'] ?? '');
            $roleId = $_POST['role_id'] ?? '';
            $nombres  = trim($_POST['nombres'] ?? '');
            $apellidos= trim($_POST['apellidos'] ?? '');
            $email    = trim($_POST['email'] ?? '');

            if (empty($id) || empty($roleId) || empty($nombres)) {
                throw new Exception("Datos incompletos.");
            }

            // 1. Actualizar Credenciales (si password no está vacío)
            if (empty($password)) {
                // Recuperar pass actual para no perderla (o lógica en repo para ignorar vacíos)
                $currentUser = UsersController::getUserByIdPerson($id);
                if ($currentUser) $password = $currentUser['user_password'];
            }

            $userData = [
                'user_id_person' => $id,
                'user_password' => $password,
                'user_role_id' => $roleId
            ];
            UsersController::updateUser($userData);

            // 2. Actualizar Datos Personales
            UsersController::savePersonData($id, $nombres, $apellidos, $email);

            $message = "✏️ Datos actualizados con éxito.";
        }

        // --- ACCIÓN: ELIMINAR USUARIO ---
        elseif ($_POST['action'] === 'delete_user') {
            $id = $_POST['user_id'] ?? '';
            if (empty($id)) throw new Exception("ID inválido.");

            UsersController::deleteUser($id);
            $message = "🗑️ Usuario eliminado correctamente.";
        }

    } catch (Exception $e) {
        $error = "❌ Error: " . $e->getMessage();
    }
}

// 4. Obtener Usuarios
try {
    $usuarios = UsersController::getUsers();
} catch (Exception $e) {
    $error = ($error ? $error . " | " : "") . "Error listando usuarios: " . $e->getMessage();
    $usuarios = [];
}

require_once __DIR__ . '/views/layouts/header.php';
?>

<script>
    document.getElementById('page-title').innerText = 'Gestión de Usuarios';

    function prepareEditUser(id, roleId, nombres, apellidos, email) {
        document.getElementById('edit_user_id').value = id;
        document.getElementById('edit_role_id').value = roleId;
        document.getElementById('edit_nombres').value = nombres;
        document.getElementById('edit_apellidos').value = apellidos;
        document.getElementById('edit_email').value = email;
        document.getElementById('edit_password').value = ''; // Limpiar campo password
    }

    function confirmDelete(id) {
        if(confirm('¿Está seguro de eliminar este usuario?')) {
            document.getElementById('delete_user_id').value = id;
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Usuarios</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
            <i class="fa-solid fa-plus"></i> Agregar Usuario
        </button>
    </div>
    
    <div class="card-body">
        <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Cédula / ID</th>
                        <th>Nombre Completo</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $user): ?>
                        <?php 
                            // Extraer datos de la vista o fallback
                            $nombreShow = $user['NombreCompleto'] ?? 'Sin Nombre';
                            $emailShow = $user['CorreoElectronico'] ?? 'Sin correo';
                            // Datos separados para el modal de editar
                            $rawNombres = $user['Nombres'] ?? '';
                            $rawApellidos = $user['Apellidos'] ?? '';
                            
                            // Nombre del Rol
                            $roleName = $user['user_role_id'];
                            foreach($roles as $r) { if($r['role_id'] == $user['user_role_id']) $roleName = $r['role_name']; }
                        ?>
                        <tr>
                            <td><small class="text-muted"><?= htmlspecialchars(substr($user['user_id_person'], 0, 8)) ?>...</small></td>
                            <td class="fw-bold"><?= htmlspecialchars($nombreShow) ?></td>
                            <td><?= htmlspecialchars($emailShow) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($roleName) ?></span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary me-1" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEditUser"
                                        onclick="prepareEditUser(
                                            '<?= $user['user_id_person'] ?>', 
                                            '<?= $user['user_role_id'] ?>',
                                            '<?= htmlspecialchars($rawNombres) ?>',
                                            '<?= htmlspecialchars($rawApellidos) ?>',
                                            '<?= htmlspecialchars($emailShow) ?>'
                                        )">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= $user['user_id_person'] ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAddUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add_user">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Nombres *</label>
                            <input type="text" class="form-control" name="nombres" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Documento (CC) - Opcional</label>
                        <input type="text" class="form-control" name="cc">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Rol *</label>
                        <select class="form-select" name="role_id" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['role_id'] ?>"><?= $r['role_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditUser" tabindex="-1">
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
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" name="nombres" id="edit_nombres">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" class="form-control" name="apellidos" id="edit_apellidos">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Correo</label>
                        <input type="email" class="form-control" name="email" id="edit_email">
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role_id" id="edit_role_id">
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['role_id'] ?>"><?= $r['role_name'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña (Opcional)</label>
                        <input type="password" class="form-control" name="password" id="edit_password" placeholder="Dejar vacío para no cambiar">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-warning">Actualizar</button>
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