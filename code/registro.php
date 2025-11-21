<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Users\Infrastructure\Services\UsersController;
use Src\Guests\Infrastructure\Services\GuestsController;

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = trim($_POST['id']); // Documento = ID Usuario
        $nombre = trim($_POST['nombre']);
        $apellido = trim($_POST['apellido']);
        $password = trim($_POST['password']);
        $email = trim($_POST['email']);

        if(empty($id) || empty($nombre) || empty($password)) throw new Exception("Campos obligatorios vacíos.");

        // Crear Usuario Rol 3 = Cliente
        UsersController::addUser([
            'user_id_person' => $id,
            'user_password' => $password, // Aquí usar password_hash($password, PASSWORD_BCRYPT) en producción
            'user_role_id' => '3' 
        ]);

        // Guardar Datos Personales
        UsersController::savePersonData($id, $nombre, $apellido, $email);

        // Crear registro base de Huésped
        GuestsController::addGuest([
            'guest_id_person' => $id,
            'guest_document_type' => 'CC', // Default
            'guest_document_number' => $id,
            'guest_country' => 'Local'
        ]);

        $message = "✅ ¡Cuenta creada! <a href='login.php'>Inicia sesión aquí</a>";

    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }</style>
</head>
<body>
    <div class="card shadow-sm" style="width: 400px;">
        <div class="card-body p-4">
            <h4 class="card-title text-center mb-4">Crear Cuenta</h4>
            
            <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Documento de Identidad (Será tu usuario)</label>
                    <input type="text" class="form-control" name="id" required>
                </div>
                <div class="row mb-3">
                    <div class="col"><input type="text" class="form-control" name="nombre" placeholder="Nombre" required></div>
                    <div class="col"><input type="text" class="form-control" name="apellido" placeholder="Apellido" required></div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" class="form-control" name="email">
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <button type="submit" class="btn btn-success w-100">Registrarse</button>
            </form>
            <div class="text-center mt-3">
                <a href="login.php">Ya tengo cuenta</a>
            </div>
        </div>
    </div>
</body>
</html>