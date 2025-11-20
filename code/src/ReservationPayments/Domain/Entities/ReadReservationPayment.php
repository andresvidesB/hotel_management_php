<?php
declare(strict_types=1);

namespace Src\ReservationPayments\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReadReservationPayment
{
    private Identifier $reservationId;
    private Price $amount;
    private TimeStamp $paymentDate;
    private string $method; // <--- ESTO ES LO NUEVO

    public function __construct(
        Identifier $reservationId,
        Price $amount,
        TimeStamp $paymentDate,
        string $method = 'Efectivo' // Valor por defecto para evitar errores con datos viejos
    ) {
        $this->reservationId = $reservationId;
        $this->amount        = $amount;
        $this->paymentDate   = $paymentDate;
        $this->method        = $method;
    }

    // GETTERS
    public function getReservationId(): Identifier { return $this->reservationId; }
    public function getAmount(): Price { return $this->amount; }
    public function getPaymentDate(): TimeStamp { return $this->paymentDate; }
    
    // ESTA ES LA FUNCIÓN QUE TE FALTABA Y CAUSABA EL ERROR:
    public function getMethod(): string { return $this->method; }

    public function toArray(): array
    {
        return [
            'reservation_payment_reservation_id' => $this->reservationId->getValue(),
            'reservation_payment_amount'         => $this->amount->getValue(),
            'reservation_payment_date'           => $this->paymentDate->getValue(),
            'reservation_payment_method'         => $this->method,
        ];
    }
}