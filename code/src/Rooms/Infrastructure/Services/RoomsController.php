<?php
declare(strict_types=1);

namespace Src\Rooms\Infrastructure\Services;

use Src\Rooms\Domain\ValueObjects\RoomState;
use Src\Rooms\Application\UseCases\AddRoom;
use Src\Rooms\Application\UseCases\UpdateRoom;
use Src\Rooms\Application\UseCases\DeleteRoom;
use Src\Rooms\Application\UseCases\GetRooms;
use Src\Rooms\Application\UseCases\GetRoomById;
use Src\Rooms\Domain\Entities\ReadRoom;
use Src\Rooms\Infrastructure\Factories\RoomFactory;
use Src\Rooms\Infrastructure\Repositories\MySqlRoomsRepository;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class RoomsController
{

    public static function updateRoomState(string $id, string $state): void
    {
        // Convertimos strings a Value Objects
        self::repo()->updateRoomState(
            new Identifier($id),
            new RoomState($state)
        );
    }
    public static function addRoom(array $room): Identifier
    {
        $roomEntity = RoomFactory::writeRoomFromArray($room);
        $useCase = new AddRoom(self::repo(), self::idCreator());
        return $useCase->execute($roomEntity);
    }

    public static function updateRoom(array $room): void
    {
        $roomEntity = RoomFactory::writeRoomFromArray($room);
        $useCase = new UpdateRoom(self::repo());
        $useCase->execute($roomEntity);
    }

    public static function deleteRoom(string $id): void
    {
        $useCase = new DeleteRoom(self::repo());
        $useCase->execute(new Identifier($id));
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getRooms(): array
    {
        $useCase = new GetRooms(self::repo());
        /** @var ReadRoom[] $items */
        $items = $useCase->execute();

        $rooms = [];
        foreach ($items as $room) {
            if (!$room instanceof ReadRoom) continue;
            $rooms[] = $room->toArray();
        }

        return $rooms;
    }

    public static function getRoomById(string $id): array
    {
        $useCase = new GetRoomById(self::repo());
        $read = $useCase->execute(new Identifier($id));
        return $read instanceof ReadRoom ? $read->toArray() : [];
    }

    private static function repo(): MySqlRoomsRepository
    {
        return new MySqlRoomsRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }
}