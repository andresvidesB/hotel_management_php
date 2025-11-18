<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Infrastructure\Repositories;

use Src\ReservationPayments\Domain\Entities\ReadReservationPayment;
use Src\ReservationPayments\Domain\Entities\WriteReservationPayment;
use Src\ReservationPayments\Domain\Interfaces\ReservationPaymentsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationPaymentsRepository implements ReservationPaymentsRepository
{
    public function addReservationPayment(WriteReservationPayment $payment): void
    {
        // Mock: sin persistencia real
    }

    public function updateReservationPayment(WriteReservationPayment $payment): void
    {
        // Mock
    }

    public function deleteReservationPayment(
        Identifier $reservationId
    ): void {
        // Mock
    }

    /**
     * @return ReadReservationPayment[]
     * @psalm-return list<ReadReservationPayment>
     */
    public function getReservationPayments(): array
    {
        return $this->seed();
    }

    /**
     * @return ReadReservationPayment[]
     * @psalm-return list<ReadReservationPayment>
     */
    public function getPaymentsByReservation(Identifier $reservationId): array
    {
        $result = [];
        foreach ($this->seed() as $payment) {
            if ($payment->getReservationId()->getValue() === $reservationId->getValue()) {
                $result[] = $payment;
            }
        }

        return $result;
    }

    /**
     * Dataset de prueba consistente.
     *
     * @return list<ReadReservationPayment>
     */
    private function seed(): array
    {
        return [
            $this->make(
                '1',
                100.00,
                123
            ),
            $this->make(
                '1',
                50.50,
                213
            ),
            $this->make(
                '2',
                200.00,
                321
            ),
        ];
    }

    private function make(
        string $reservationId,
        float $amount,
        int $paymentDate
    ): ReadReservationPayment {
        return new ReadReservationPayment(
            new Identifier($reservationId),
            new Price($amount),
            new TimeStamp($paymentDate)
        );
    }
}
