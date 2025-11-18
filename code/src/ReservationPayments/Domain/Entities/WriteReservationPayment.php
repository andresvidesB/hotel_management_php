<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class WriteReservationPayment
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
}
