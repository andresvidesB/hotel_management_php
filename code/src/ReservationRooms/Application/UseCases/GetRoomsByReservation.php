<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Application\UseCases;

use Src\ReservationRooms\Domain\Entities\ReadReservationRoom;
use Src\ReservationRooms\Domain\Interfaces\ReservationRoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetRoomsByReservation
{
    public function __construct(
        private readonly ReservationRoomsRepository $repository
    ) {
    }

    /**
     * @return ReadReservationRoom[]
     * @psalm-return list<ReadReservationRoom>
     * @phpstan-return list<ReadReservationRoom>
     */
    public function execute(Identifier $reservationId): array
    {
        return $this->repository->getRoomsByReservation($reservationId);
    }
}
