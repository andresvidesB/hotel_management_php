<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Infrastructure\Services;

use Src\ReservationGuests\Application\UseCases\AddReservationGuest;
use Src\ReservationGuests\Application\UseCases\DeleteReservationGuest;
use Src\ReservationGuests\Application\UseCases\GetReservationGuests;
use Src\ReservationGuests\Application\UseCases\GetGuestsByReservation;
use Src\ReservationGuests\Application\UseCases\GetReservationsByGuest;
use Src\ReservationGuests\Domain\Entities\ReadReservationGuest;
use Src\ReservationGuests\Infrastructure\Factories\ReservationGuestFactory;
use Src\ReservationGuests\Infrastructure\Repositories\MySqlReservationGuestsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationGuestsController
{
    public static function addReservationGuest(array $data): void
    {
        $entity = ReservationGuestFactory::writeReservationGuestFromArray($data);
        $useCase = new AddReservationGuest(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservationGuest(string $guestId, string $reservationId): void
    {
        $useCase = new DeleteReservationGuest(self::repo());
        $useCase->execute(new Identifier($guestId), new Identifier($reservationId));
    }

    public static function getReservationGuests(): array
    {
        $useCase = new GetReservationGuests(self::repo());
        $items   = $useCase->execute();

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationGuest) {
                $result[] = $relation->toArray();
            }
        }
        return $result;
    }

    public static function getGuestsByReservation(string $reservationId): array
    {
        $useCase = new GetGuestsByReservation(self::repo());
        $items = $useCase->execute(new Identifier($reservationId));

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationGuest) {
                $result[] = $relation->toArray();
            }
        }
        return $result;
    }

    public static function getReservationsByGuest(string $guestId): array
    {
        $useCase = new GetReservationsByGuest(self::repo());
        $items = $useCase->execute(new Identifier($guestId));

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationGuest) {
                $result[] = $relation->toArray();
            }
        }
        return $result;
    }

    private static function repo(): MySqlReservationGuestsRepository
    {
        return new MySqlReservationGuestsRepository();
    }
}
