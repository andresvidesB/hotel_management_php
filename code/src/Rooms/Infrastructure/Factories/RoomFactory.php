<?php

namespace Src\Rooms\Infrastructure\Factories;

use Src\Rooms\Domain\Entities\WriteRoom;

// 1. IMPORTAMOS TODOS LOS VALUE OBJECTS QUE NECESITAMOS
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Rooms\Domain\ValueObjects\RoomName;
use Src\Rooms\Domain\ValueObjects\RoomType;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Rooms\Domain\ValueObjects\RoomCapacity;

final class RoomFactory
{
    public static function writeRoomFromArray(array $data): WriteRoom
    {
        // 2. AHORA ENVOLVEMOS CADA VALOR EN SU OBJETO CORRESPONDIENTE
        return new WriteRoom(
            new Identifier($data["room_id"] ?? ''), // El '?? '' ' es por si es un 'add' y no un 'update'
            new RoomName($data["room_name"]),
            new RoomType($data["room_type"]),
            new Price((float)$data["room_price"]), // El constructor de Price espera un float
            new RoomCapacity((int)$data["room_capacity"]) // El de Capacity espera un int
        );
    }
}