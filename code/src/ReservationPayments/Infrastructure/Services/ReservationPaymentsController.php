<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Infrastructure\Services;

use Src\ReservationPayments\Application\UseCases\AddReservationPayment;
use Src\ReservationPayments\Application\UseCases\UpdateReservationPayment;
use Src\ReservationPayments\Application\UseCases\DeleteReservationPayment;
use Src\ReservationPayments\Application\UseCases\GetReservationPayments;
use Src\ReservationPayments\Application\UseCases\GetPaymentsByReservation;
use Src\ReservationPayments\Domain\Entities\ReadReservationPayment;
use Src\ReservationPayments\Infrastructure\Factories\ReservationPaymentFactory;
use Src\ReservationPayments\Infrastructure\Repositories\MySqlReservationPaymentsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationPaymentsController
{
    public static function addReservationPayment(array $data): void
    {
        $entity = ReservationPaymentFactory::writeReservationPaymentFromArray($data);
        $useCase = new AddReservationPayment(self::repo());
        $useCase->execute($entity);
    }

    public static function updateReservationPayment(array $data): void
    {
        $entity = ReservationPaymentFactory::writeReservationPaymentFromArray($data);
        $useCase = new UpdateReservationPayment(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservationPayment(string $reservationId, string $paymentDate): void
    {
        $useCase = new DeleteReservationPayment(self::repo());
        $useCase->execute(
            new Identifier($reservationId)
        );
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getReservationPayments(): array
    {
        $useCase = new GetReservationPayments(self::repo());
        $items = $useCase->execute();

        $result = [];
        foreach ($items as $payment) {
            if ($payment instanceof ReadReservationPayment) {
                $result[] = $payment->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getPaymentsByReservation(string $reservationId): array
    {
        $useCase = new GetPaymentsByReservation(self::repo());
        $items = $useCase->execute(new Identifier($reservationId));

        $result = [];
        foreach ($items as $payment) {
            if ($payment instanceof ReadReservationPayment) {
                $result[] = $payment->toArray();
            }
        }

        return $result;
    }

    private static function repo(): MySqlReservationPaymentsRepository
    {
        return new MySqlReservationPaymentsRepository();
    }
}
