<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Domain\Interfaces;

use Src\ReservationPayments\Domain\Entities\ReadReservationPayment;
use Src\ReservationPayments\Domain\Entities\WriteReservationPayment;
use Src\ReservationPayments\Domain\ValueObjects\ReservationPaymentDate;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationPaymentsRepository
{
    public function addReservationPayment(WriteReservationPayment $payment): void;

    public function updateReservationPayment(WriteReservationPayment $payment): void;

    public function deleteReservationPayment(
        Identifier $reservationId
    ): void;

    /**
     * @return ReadReservationPayment[]
     * @psalm-return list<ReadReservationPayment>
     */
    public function getReservationPayments(): array;

    /**
     * @return ReadReservationPayment[]
     * @psalm-return list<ReadReservationPayment>
     */
    public function getPaymentsByReservation(Identifier $reservationId): array;
}
