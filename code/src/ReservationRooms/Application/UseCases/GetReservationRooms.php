<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Application\UseCases;

use Src\ReservationRooms\Domain\Entities\ReadReservationRoom;
use Src\ReservationRooms\Domain\Interfaces\ReservationRoomsRepository;

final class GetReservationRooms
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
    public function execute(): array
    {
        return $this->repository->getReservationRooms();
    }
}
