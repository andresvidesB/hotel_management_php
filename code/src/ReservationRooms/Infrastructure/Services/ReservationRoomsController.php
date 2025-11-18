<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Infrastructure\Services;

use Src\ReservationRooms\Application\UseCases\AddReservationRoom;
use Src\ReservationRooms\Application\UseCases\DeleteReservationRoom;
use Src\ReservationRooms\Application\UseCases\GetReservationRooms;
use Src\ReservationRooms\Application\UseCases\GetRoomsByReservation;
use Src\ReservationRooms\Domain\Entities\ReadReservationRoom;
use Src\ReservationRooms\Infrastructure\Factories\ReservationRoomFactory;
use Src\ReservationRooms\Infrastructure\Repositories\MySqlReservationRoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationRoomsController
{
    public static function addReservationRoom(array $data): void
    {
        $entity  = ReservationRoomFactory::writeReservationRoomFromArray($data);
        $useCase = new AddReservationRoom(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservationRoom(string $reservationId, string $roomId): void
    {
        $useCase = new DeleteReservationRoom(self::repo());
        $useCase->execute(
            new Identifier($reservationId),
            new Identifier($roomId)
        );
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getReservationRooms(): array
    {
        $useCase = new GetReservationRooms(self::repo());
        $items   = $useCase->execute();

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationRoom) {
                $result[] = $relation->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getRoomsByReservation(string $reservationId): array
    {
        $useCase = new GetRoomsByReservation(self::repo());
        $items   = $useCase->execute(new Identifier($reservationId));
        $result = [];
        
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationRoom) {
                $result[] = $relation->toArray();
            }
        }

        return $result;
    }

    private static function repo(): MySqlReservationRoomsRepository
    {
        return new MySqlReservationRoomsRepository();
    }
}
