<?php
// Archivo: src/ReservationRooms/Infrastructure/Factories/ReservationRoomFactory.php

declare(strict_types=1);

namespace Src\ReservationRooms\Infrastructure\Factories;

use Src\ReservationRooms\Domain\Entities\WriteReservationRoom;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationRoomFactory
{
    public static function writeReservationRoomFromArray(array $data): WriteReservationRoom
    {
        // Manejo de Fechas: Asegurar que sean Timestamps (enteros)
        $start = $data['reservation_room_start_date'];
        $end   = $data['reservation_room_end_date'];

        if (is_string($start)) $start = strtotime($start);
        if (is_string($end))   $end   = strtotime($end);

        return new WriteReservationRoom(
            new Identifier($data['reservation_room_reservation_id']),
            new Identifier($data['reservation_room_room_id']),
            new TimeStamp((int)$start),
            new TimeStamp((int)$end)
        );
    }
}