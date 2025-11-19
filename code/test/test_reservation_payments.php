<?php
// Archivo: code/test_reservation_payments.php
require __DIR__ . '/vendor/autoload.php';

use Src\ReservationPayments\Infrastructure\Services\ReservationPaymentsController;
use Src\Reservations\Infrastructure\Services\ReservationsController;

echo "--- Iniciando Prueba de Pagos --- \n";

try {
    // 1. Buscar Reserva
    $reservas = ReservationsController::getReservations();
    if (empty($reservas)) throw new Exception("Error: No hay reservas.");
    $reservaId = $reservas[0]['reservation_id'];
    echo "Reserva ID: " . $reservaId . "\n";

    // 2. Agregar Pago (ej: Anticipo de $50.00)
    $data = [
        'reservation_payment_reservation_id' => $reservaId,
        'reservation_payment_amount'         => 50.00,
        'reservation_payment_date'           => date('Y-m-d')
    ];

    echo "Registrando pago de $50.00... \n";
    ReservationPaymentsController::addReservationPayment($data);
    echo "¡Pago registrado con éxito! \n";

    // 3. Verificar
    echo "Pagos de la reserva: \n";
    $items = ReservationPaymentsController::getPaymentsByReservation($reservaId);
    print_r($items);

} catch (Exception $e) {
    echo "\n !!! ERROR: " . $e->getMessage() . "\n";
}