<?php
require __DIR__ . '/vendor/autoload.php';

use Src\Users\Infrastructure\Services\UsersController;
use Src\Shared\Infrastructure\Services\EmailService;

// Iniciamos sesión temporal para guardar el código de verificación
if (session_status() === PHP_SESSION_NONE) session_start();

$message = null;
$error = null;
$step = $_SESSION['rec_step'] ?? 1; // 1 = Pedir ID, 2 = Verificar Código

// Limpiar sesión si se solicita reiniciar
if (isset($_GET['reset'])) {
    session_destroy();
    header('Location: recuperar.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // ENVIAR CÓDIGO ---
        if (isset($_POST['action']) && $_POST['action'] === 'send_code') {
            $id = trim($_POST['id']);
            if(empty($id)) throw new Exception("Ingrese su documento.");

            // Buscar Usuario y Correo
            $user = UsersController::getUserByIdPerson($id);
            if(empty($user)) throw new Exception("Usuario no encontrado.");

            $allUsers = UsersController::getUsers();
            $userData = null;
            foreach($allUsers as $u) {
                if($u['user_id_person'] === $id) { $userData = $u; break; }
            }

            if(empty($userData) || empty($userData['CorreoElectronico']) || $userData['CorreoElectronico'] === 'sin_correo@hotel.com') {
                throw new Exception("Este usuario no tiene correo asociado.");
            }

            // Generar Código de 6 dígitos
            $code = rand(100000, 999999);

            // Guardar en Sesión (Memoria temporal)
            $_SESSION['rec_step'] = 2;
            $_SESSION['rec_id'] = $id;
            $_SESSION['rec_code'] = $code;
            $_SESSION['rec_email'] = $userData['CorreoElectronico'];
            $_SESSION['rec_role'] = $user['user_role_id']; // Necesario para el update

            // Enviar Correo
            $sent = EmailService::sendVerificationCode(
                $userData['CorreoElectronico'], 
                $userData['NombreCompleto'], 
                (string)$code
            );

            if($sent) {
                $step = 2; // Avanzar visualmente
                $message = "Código enviado a: " . maskEmail($userData['CorreoElectronico']);
            } else {
                throw new Exception("Error enviando el correo. Intente de nuevo.");
            }
        }

        // VERIFICAR Y CAMBIAR 
        elseif (isset($_POST['action']) && $_POST['action'] === 'verify_change') {
            $inputCode = trim($_POST['code']);
            $newPass = trim($_POST['new_password']);
            
            // Validaciones
            if($inputCode != $_SESSION['rec_code']) {
                throw new Exception("Código incorrecto.");
            }
            if(strlen($newPass) < 4) {
                throw new Exception("La contraseña debe tener al menos 4 caracteres.");
            }

            // Actualizar en Base de Datos
            UsersController::updateUser([
                'user_id_person' => $_SESSION['rec_id'],
                'user_password'  => $newPass, // cambiar mas adelante a hash
                'user_role_id'   => $_SESSION['rec_role']
            ]);

            // Limpiar y Finalizar
            session_destroy();
            $step = 3; // Éxito
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

function maskEmail($email) {
    $parts = explode("@", $email);
    return substr($parts[0], 0, 3) . '***@' . end($parts);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>body { background: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }</style>
</head>
<body>
    <div class="card shadow border-0 p-4" style="width:400px; border-radius: 12px;">
        
        <?php if($step === 3): ?>
            <div class="text-center">
                <div class="text-success mb-3"><i class="fa-solid fa-circle-check fa-4x"></i></div>
                <h4>¡Contraseña Actualizada!</h4>
                <p class="text-muted small">Ya puedes ingresar con tu nueva clave.</p>
                <a href="login.php" class="btn btn-primary w-100 fw-bold">Ir al Login</a>
            </div>

        <?php else: ?>

            <div class="text-center mb-4">
                <i class="fa-solid fa-shield-halved fa-3x text-primary mb-2"></i>
                <h4>Recuperar Acceso</h4>
            </div>

            <?php if($message): ?><div class="alert alert-info small"><i class="fa-solid fa-envelope me-1"></i> <?= $message ?></div><?php endif; ?>
            <?php if($error): ?><div class="alert alert-danger small"><i class="fa-solid fa-circle-xmark me-1"></i> <?= $error ?></div><?php endif; ?>

            <?php if($step === 1): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="send_code">
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-muted">Documento de Identidad</label>
                        <input type="text" class="form-control" name="id" placeholder="Ej: 123456789" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">Enviar Código</button>
                </form>
            
            <?php elseif($step === 2): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="verify_change">
                    
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold small text-muted">Código de Verificación</label>
                        <input type="text" class="form-control text-center fw-bold letter-spacing-2" name="code" placeholder="######" maxlength="6" required style="letter-spacing: 5px; font-size: 1.2rem;">
                        <div class="form-text small">Revisa tu correo electrónico.</div>
                    </div>
                    
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold small text-muted">Nueva Contraseña</label>
                        <input type="password" class="form-control" name="new_password" placeholder="Mínimo 4 caracteres" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold">Cambiar Contraseña</button>
                </form>
                <div class="text-center mt-3">
                    <a href="recuperar.php?reset=1" class="small text-muted">¿No llegó el código? Intentar de nuevo</a>
                </div>
            <?php endif; ?>

            <?php if($step === 1): ?>
                <div class="text-center mt-3 pt-3 border-top">
                    <a href="login.php" class="text-decoration-none small text-secondary">Volver al Login</a>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</body>
</html>