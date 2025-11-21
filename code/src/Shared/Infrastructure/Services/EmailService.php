<?php
declare(strict_types=1);

namespace Src\Shared\Infrastructure\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class EmailService
{
    // CONFIGURACIÓN SMTP (CAMBIA ESTO CON TUS DATOS REALES)
    private const SMTP_HOST = 'smtp.gmail.com';
    private const SMTP_USER = 'videsbertel17@gmail.com'; 
    private const SMTP_PASS = 'namh elbi gufn mzde'; // NO es tu clave normal, es la App Password
    private const SMTP_PORT = 587;
    private const FROM_NAME = 'Hotel System Reservas';

    public static function sendReservationVoucher(string $toEmail, string $clientName, array $reservaData): bool
    {
        $mail = new PHPMailer(true);

        try {
            // 1. Configuración del Servidor
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = self::SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // 2. Remitente y Destinatario
            $mail->setFrom(self::SMTP_USER, self::FROM_NAME);
            $mail->addAddress($toEmail, $clientName);

            // 3. Contenido
            $mail->isHTML(true);
            $mail->Subject = 'Confirmación de Reserva #' . substr($reservaData['id'], 0, 6);
            $mail->Body    = self::getHtmlTemplate($clientName, $reservaData);
            $mail->AltBody = "Hola $clientName, tu reserva está confirmada. Habitación: {$reservaData['room']}. Fechas: {$reservaData['start']} al {$reservaData['end']}.";

            $mail->send();
            return true;

        } catch (Exception $e) {
            // En producción, podrías guardar el error en un log: $mail->ErrorInfo
            return false;
        }
    }

   

    // NUEVO MÉTODO: Enviar solicitud de cancelación al Admin
    public static function sendCancellationRequest(string $reservationId, string $clientName, string $reason): bool
    {
        $mail = new PHPMailer(true);
        // Correo del Administrador (Quien recibe las solicitudes)
        $adminEmail = 'videsbertel17@gmail.com'; 

        try {
            // Configuración Servidor (Igual que arriba)
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = self::SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            // De: Sistema / Para: Admin
            $mail->setFrom(self::SMTP_USER, 'Sistema de Reservas');
            $mail->addAddress($adminEmail, 'Administración Hotel');

            // Contenido
            $mail->isHTML(true);
            $mail->Subject = '⚠️ Solicitud de Cancelación - Reserva #' . substr($reservationId, 0, 6);
            
            $body = "
            <h3>Solicitud de Cancelación de Reserva</h3>
            <p>El cliente <strong>$clientName</strong> ha solicitado cancelar su reserva.</p>
            <ul>
                <li><strong>Reserva ID:</strong> $reservationId</li>
                <li><strong>Motivo:</strong> $reason</li>
            </ul>
            <p>Por favor, ingrese al sistema para gestionar esta solicitud.</p>
            ";

            $mail->Body = $body;
            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    
/*
    // NUEVO MÉTODO: Enviar nueva contraseña
    public static function sendPasswordRecovery(string $toEmail, string $name, string $newPassword): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración Servidor
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = self::SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(self::SMTP_USER, 'Soporte Hotel System');
            $mail->addAddress($toEmail, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Recuperación de Contraseña';
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px;'>
                <h2 style='color: #0d6efd; text-align: center;'>Nueva Contraseña</h2>
                <p>Hola <strong>$name</strong>,</p>
                <p>Hemos recibido una solicitud para restablecer tu contraseña.</p>
                <div style='background: #f8f9fa; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 2px; margin: 20px 0;'>
                    $newPassword
                </div>
                <p>Por favor ingresa con esta clave y cámbiala lo antes posible.</p>
                <hr>
                <small style='color: #888;'>Si no solicitaste esto, por favor contacta a administración.</small>
            </div>
            ";

            $mail->Body = $body;
            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

*/

    // Enviar Código de Verificación
    public static function sendVerificationCode(string $toEmail, string $name, string $code): bool
    {
        $mail = new PHPMailer(true);

        try {
            // Configuración Servidor
            $mail->isSMTP();
            $mail->Host       = self::SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = self::SMTP_USER;
            $mail->Password   = self::SMTP_PASS;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = self::SMTP_PORT;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom(self::SMTP_USER, 'Seguridad Hotel System');
            $mail->addAddress($toEmail, $name);

            $mail->isHTML(true);
            $mail->Subject = 'Código de Verificación';
            
            $body = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; border-radius: 8px; text-align: center;'>
                <h2 style='color: #0d6efd;'>Recuperación de Cuenta</h2>
                <p>Hola <strong>$name</strong>,</p>
                <p>Usa el siguiente código para verificar tu identidad y cambiar tu contraseña:</p>
                
                <div style='background: #f0f2f5; padding: 15px; font-size: 24px; font-weight: bold; letter-spacing: 5px; color: #333; margin: 20px auto; width: fit-content; border-radius: 5px;'>
                    $code
                </div>
                
                <p style='font-size: 12px; color: #666;'>Este código es válido por seguridad. No lo compartas.</p>
            </div>
            ";

            $mail->Body = $body;
            $mail->send();
            return true;

        } catch (Exception $e) {
            return false;
        }
    }




    // PLANTILLA HTML "BONITA"
    private static function getHtmlTemplate(string $name, array $data): string
    {
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden;'>
            <div style='background-color: #0d6efd; color: white; padding: 20px; text-align: center;'>
                <h1 style='margin: 0;'>¡Reserva Confirmada!</h1>
                <p style='margin: 5px 0 0;'>Gracias por elegirnos, $name</p>
            </div>

            <div style='padding: 30px; background-color: #ffffff;'>
                <p style='font-size: 16px; color: #333;'>Tu estadía ha sido reservada exitosamente. Aquí están los detalles:</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr>
                            <td style='padding: 8px; color: #666;'>Reserva ID:</td>
                            <td style='padding: 8px; font-weight: bold;'>#" . substr($data['id'], 0, 8) . "</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; color: #666;'>Habitación:</td>
                            <td style='padding: 8px; font-weight: bold;'>{$data['room']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; color: #666;'>Llegada:</td>
                            <td style='padding: 8px; font-weight: bold;'>{$data['start']}</td>
                        </tr>
                        <tr>
                            <td style='padding: 8px; color: #666;'>Salida:</td>
                            <td style='padding: 8px; font-weight: bold;'>{$data['end']}</td>
                        </tr>
                         <tr>
                            <td style='padding: 8px; color: #666;'>Estado:</td>
                            <td style='padding: 8px; color: #198754; font-weight: bold;'>Confirmada</td>
                        </tr>
                    </table>
                </div>

                <p style='font-size: 14px; color: #666; text-align: center;'>
                    Te esperamos a partir de las 15:00 horas para tu Check-in.
                </p>
            </div>

            <div style='background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #888;'>
                <p style='margin: 0;'>Sistema de Gestión Hotelera</p>
                <p style='margin: 0;'>Calle Principal 123, Ciudad</p>
            </div>
        </div>
        ";
    }
}