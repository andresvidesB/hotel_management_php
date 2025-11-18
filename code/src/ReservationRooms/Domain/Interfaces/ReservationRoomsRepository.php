<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Domain\Interfaces;

use Src\ReservationRooms\Domain\Entities\ReadReservationRoom;
use Src\ReservationRooms\Domain\Entities\WriteReservationRoom;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationRoomsRepository
{
    public function addReservationRoom(WriteReservationRoom $reservationRoom): void;

    public function deleteReservationRoom(
        Identifier $reservationId,
        Identifier $roomId
    ): void;

    /**
     * @return ReadReservationRoom[]
     * @psalm-return list<ReadReservationRoom>
     * @phpstan-return list<ReadReservationRoom>
     */
    public function getReservationRooms(): array;

    /**
     * @return ReadReservationRoom[]
     * @psalm-return list<ReadReservationRoom>
     * @phpstan-return list<ReadReservationRoom>
     */
    public function getRoomsByReservation(Identifier $reservationId): array;
}
