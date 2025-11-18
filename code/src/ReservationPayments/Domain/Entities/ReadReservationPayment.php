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

    public function __construct(
        Identifier $reservationId,
        Price $amount,
        TimeStamp $paymentDate
    ) {
        $this->reservationId = $reservationId;
        $this->amount        = $amount;
        $this->paymentDate   = $paymentDate;
    }

    // GETTERS
    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    public function getAmount(): Price
    {
        return $this->amount;
    }

    public function getPaymentDate(): TimeStamp
    {
        return $this->paymentDate;
    }

    // SETTERS
    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function setAmount(Price $amount): void
    {
        $this->amount = $amount;
    }

    public function setPaymentDate(TimeStamp $paymentDate): void
    {
        $this->paymentDate = $paymentDate;
    }

    public function toArray(): array
    {
        return [
            'reservation_payment_reservation_id' => $this->reservationId->getValue(),
            'reservation_payment_amount'         => $this->amount->getValue(),
            'reservation_payment_date'           => $this->paymentDate->getValue(),
        ];
    }
}
