<?php
require __DIR__ . '/vendor/autoload.php';
use Src\Shared\Infrastructure\Services\AuthService;

$error = null;
$next = $_GET['next'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $password = trim($_POST['password']);
    $next = $_POST['next_redirect'] ?? $next;

    if (empty($id) || empty($password)) {
        $error = "Por favor ingrese usuario y contraseña.";
    } else {
        $result = AuthService::login($id, $password);
        
        if ($result['success']) {
            if (!empty($next)) {
                header("Location: " . urldecode($next));
                exit;
            }
            if ($result['role'] === 'Cliente') {
                header('Location: catalogo.php');
            } else {
                header('Location: dashboard.php');
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            /* Imagen de fondo de lujo (Unsplash) */
            background: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1920&q=80') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Capa oscura para mejorar lectura */
        .overlay {
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Oscuridad al 50% */
            z-index: 1;
        }

        .login-card {
            position: relative;
            z-index: 2; /* Encima de la capa oscura */
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            border-radius: 15px;
            /* Blanco con ligera transparencia */
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px); /* Efecto borroso detrás */
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            border-top: 5px solid #0d6efd; /* Detalle de color arriba */
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: #0d6efd;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            margin: -60px auto 20px auto; /* Flotando arriba de la tarjeta */
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>
<body>

<div class="overlay"></div>

<div class="login-card text-center">
    <div class="logo-icon"><i class="fa-solid fa-hotel"></i></div>
    
    <h3 class="fw-bold mb-1 text-dark">Bienvenido</h3>
    <p class="text-muted mb-4">Accede a tu cuenta para continuar</p>
    
    <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center small py-2 text-start">
            <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($next)): ?>
        <div class="alert alert-info small text-center py-2 mb-3 border-0 bg-info bg-opacity-10 text-info">
            <i class="fa-solid fa-lock me-1"></i> Inicia sesión para finalizar tu reserva.
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="next_redirect" value="<?= htmlspecialchars($next) ?>">

        <div class="form-floating mb-3 text-start">
            <input type="text" class="form-control" id="id" name="id" placeholder="Documento" required>
            <label for="id" class="text-muted"><i class="fa-solid fa-user me-1"></i> Usuario / Documento</label>
        </div>
        
        <div class="form-floating mb-4 text-start">
            <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
            <label for="password" class="text-muted"><i class="fa-solid fa-key me-1"></i> Contraseña</label>
        </div>
        
        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm mb-3 text-uppercase" style="letter-spacing: 1px;">
            Ingresar
        </button>
    </form>
    
    <div class="pt-3 border-top">
        <p class="mb-2 text-muted small">¿No tienes una cuenta?</p>
        <a href="registro.php" class="btn btn-outline-dark btn-sm w-100 fw-bold">Crear Cuenta Nueva</a>
        <div class="mt-3">
            <a href="catalogo.php" class="text-decoration-none small text-secondary fw-semibold">
                <i class="fa-solid fa-arrow-left me-1"></i> Volver al catálogo
            </a>
        </div>
    </div>
</div>

</body>
</html>