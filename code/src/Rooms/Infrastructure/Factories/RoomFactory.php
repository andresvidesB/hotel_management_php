<?php

namespace Src\Rooms\Infrastructure\Factories;

use Src\Rooms\Domain\Entities\WriteRoom;
final class RoomFactory
{
    public static function writeRoomFromArray(array $data): WriteRoom
    {
        return new WriteRoom(
            $data["room_id"],
            $data["room_name"],
            $data["room_type"],
            $data["room_price"],
            $data["room_capacity"]
        );
    }

}