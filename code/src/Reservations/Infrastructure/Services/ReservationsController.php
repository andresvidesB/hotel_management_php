<?php

declare(strict_types=1);

namespace Src\Reservations\Infrastructure\Services;

use Src\Reservations\Application\UseCases\AddReservation;
use Src\Reservations\Application\UseCases\UpdateReservation;
use Src\Reservations\Application\UseCases\DeleteReservation;
use Src\Reservations\Application\UseCases\GetReservations;
use Src\Reservations\Application\UseCases\GetReservationById;
use Src\Reservations\Domain\Entities\ReadReservation;
use Src\Reservations\Infrastructure\Factories\ReservationFactory;
use Src\Reservations\Infrastructure\Repositories\MySqlReservationsRepository;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationsController
{
    public static function addReservation(array $data): Identifier
    {
        $entity = ReservationFactory::writeReservationFromArray($data);
        $useCase = new AddReservation(self::repo(), self::idCreator());
        return $useCase->execute($entity);
    }

    public static function updateReservation(array $data): void
    {
        $entity = ReservationFactory::writeReservationFromArray($data);
        $useCase = new UpdateReservation(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservation(string $id): void
    {
        $useCase = new DeleteReservation(self::repo());
        $useCase->execute(new Identifier($id));
    }

    public static function getReservations(): array
    {
        $useCase = new GetReservations(self::repo());
        $items = $useCase->execute();

        $list = [];
        foreach ($items as $reservation) {
            if ($reservation instanceof ReadReservation) {
                $list[] = $reservation->toArray();
            }
        }
        return $list;
    }

    public static function getReservationById(string $id): array
    {
        $useCase = new GetReservationById(self::repo());
        $reservation = $useCase->execute(new Identifier($id));

        return $reservation instanceof ReadReservation ? $reservation->toArray() : [];
    }

    private static function repo(): MySqlReservationsRepository
    {
        return new MySqlReservationsRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }

    public static function getReservaCompleta(string $id): array
    {
        $repo = self::repo();
        return $repo->getReservaCompleta(new Identifier($id));
    }
}
