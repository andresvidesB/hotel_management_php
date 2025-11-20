<?php
namespace Src\Rooms\Infrastructure\Factories;

use Src\Rooms\Domain\Entities\WriteRoom;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Rooms\Domain\ValueObjects\RoomName;
use Src\Rooms\Domain\ValueObjects\RoomType;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Rooms\Domain\ValueObjects\RoomCapacity;
use Src\Rooms\Domain\ValueObjects\RoomState; // Nuevo

final class RoomFactory
{
    public static function writeRoomFromArray(array $data): WriteRoom
    {
        return new WriteRoom(
            new Identifier($data["room_id"] ?? ''),
            new RoomName($data["room_name"]),
            new RoomType($data["room_type"]),
            new Price((float)$data["room_price"]),
            new RoomCapacity((int)$data["room_capacity"]),
            // Si no envían estado (ej: al crear), por defecto 'Disponible'
            new RoomState($data["room_state"] ?? 'Disponible') 
        );
    }
}