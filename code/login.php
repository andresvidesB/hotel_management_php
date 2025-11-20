<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Shared\Infrastructure\Services\AuthService;

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $password = trim($_POST['password']);

    if (empty($id) || empty($password)) {
        $error = "Por favor ingrese usuario y contraseña.";
    } else {
        $result = AuthService::login($id, $password);
        
        if ($result['success']) {
            // Redirección según rol
            if ($result['role'] === 'Cliente') {
                header('Location: catalogo.php'); // Página nueva para clientes
            } else {
                header('Location: dashboard.php'); // Admin y Recepción
            }
            exit;
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hotel System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d6efd 0%, #0099ff 100%); height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 400px; padding: 2rem; border-radius: 15px; background: white; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
        .logo-icon { font-size: 3rem; color: #0d6efd; margin-bottom: 1rem; }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="login-card text-center">
    <div class="logo-icon"><i class="fa-solid fa-hotel"></i></div>
    <h3 class="mb-4 fw-bold">Hotel System</h3>
    
    <?php if ($error): ?>
        <div class="alert alert-danger text-start small"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="id" name="id" placeholder="Documento de Identidad" required>
            <label for="id">Usuario / Documento</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
            <label for="password">Contraseña</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold mb-3">INGRESAR</button>
    </form>
    
    <hr>
    <div class="text-center">
        <small class="text-muted">¿Eres nuevo?</small><br>
        <a href="registro.php" class="text-decoration-none fw-bold">Regístrate aquí</a>
    </div>
</div>

</body>
</html>