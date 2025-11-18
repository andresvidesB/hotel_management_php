<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Infrastructure\Repositories;

use Src\ReservationRooms\Domain\Entities\ReadReservationRoom;
use Src\ReservationRooms\Domain\Entities\WriteReservationRoom;
use Src\ReservationRooms\Domain\Interfaces\ReservationRoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlReservationRoomsRepository implements ReservationRoomsRepository
{
    public function addReservationRoom(WriteReservationRoom $reservationRoom): void
    {
        // Mock: no persistencia real
    }

    public function deleteReservationRoom(
        Identifier $reservationId,
        Identifier $roomId
    ): void {
        // Mock: no persistencia real
    }

    /**
     * @return ReadReservationRoom[]
     * @psalm-return list<ReadReservationRoom>
     * @phpstan-return list<ReadReservationRoom>
     */
    public function getReservationRooms(): array
    {
        return $this->seed();
    }

    /**
     * @return ReadReservationRoom[]
     * @psalm-return list<ReadReservationRoom>
     * @phpstan-return list<ReadReservationRoom>
     */
    public function getRoomsByReservation(Identifier $reservationId): array
    {
        $result = [];
        foreach ($this->seed() as $relation) {
            if ($relation->getReservationId()->getValue() === $reservationId->getValue()) {
                $result[] = $relation;
            }
        }

        return $result;
    }

    /**
     * Dataset de prueba consistente.
     *
     * @return list<ReadReservationRoom>
     */
    private function seed(): array
    {
        return [
            $this->make(
                '1',   // IdReserva
                '101'  // IdHabitacion
            ),
            $this->make(
                '1',
                '102'
            ),
            $this->make(
                '2',
                '201'
            ),
        ];
    }

    private function make(
        string $reservationId,
        string $roomId
    ): ReadReservationRoom {
        return new ReadReservationRoom(
            new Identifier($reservationId),
            new Identifier($roomId)
        );
    }
}
