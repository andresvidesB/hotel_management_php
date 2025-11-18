<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Infrastructure\Services;

use Src\ReservationStatus\Application\UseCases\AddReservationStatus;
use Src\ReservationStatus\Application\UseCases\UpdateReservationStatus;
use Src\ReservationStatus\Application\UseCases\DeleteReservationStatus;
use Src\ReservationStatus\Application\UseCases\GetReservationStatuses;
use Src\ReservationStatus\Application\UseCases\GetStatusesByReservation;
use Src\ReservationStatus\Domain\Entities\ReadReservationStatus;
use Src\ReservationStatus\Infrastructure\Factories\ReservationStatusFactory;
use Src\ReservationStatus\Infrastructure\Repositories\MySqlReservationStatusRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationStatusController
{
    public static function addReservationStatus(array $data): void
    {
        $entity = ReservationStatusFactory::writeReservationStatusFromArray($data);
        $useCase = new AddReservationStatus(self::repo());
        $useCase->execute($entity);
    }

    public static function updateReservationStatus(array $data): void
    {
        $entity = ReservationStatusFactory::writeReservationStatusFromArray($data);
        $useCase = new UpdateReservationStatus(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservationStatus(string $reservationId, string $statusId): void
    {
        $useCase = new DeleteReservationStatus(self::repo());
        $useCase->execute(
            new Identifier($reservationId),
            new Identifier($statusId)
        );
    }

    public static function getReservationStatuses(): array
    {
        $useCase = new GetReservationStatuses(self::repo());
        $items = $useCase->execute();

        $result = [];
        foreach ($items as $item) {
            if ($item instanceof ReadReservationStatus) {
                $result[] = $item->toArray();
            }
        }
        return $result;
    }

    public static function getStatusesByReservation(string $reservationId): array
    {
        $useCase = new GetStatusesByReservation(self::repo());
        $items = $useCase->execute(new Identifier($reservationId));

        $result = [];
        foreach ($items as $item) {
            if ($item instanceof ReadReservationStatus) {
                $result[] = $item->toArray();
            }
        }
        return $result;
    }

    private static function repo(): MySqlReservationStatusRepository
    {
        return new MySqlReservationStatusRepository();
    }
}
