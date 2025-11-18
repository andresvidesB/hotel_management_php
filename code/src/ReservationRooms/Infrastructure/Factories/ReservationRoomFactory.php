<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Infrastructure\Factories;

use Src\ReservationRooms\Domain\Entities\WriteReservationRoom;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationRoomFactory
{
    public static function writeReservationRoomFromArray(array $data): WriteReservationRoom
    {
        return new WriteReservationRoom(
            new Identifier($data['reservation_room_reservation_id']),
            new Identifier($data['reservation_room_room_id']),
            new TimeStamp($data['reservation_room_start_date']),
            new TimeStamp($data['reservation_room_end_date'])
        );
    }
}
