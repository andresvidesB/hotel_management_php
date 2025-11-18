<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Application\UseCases;

use Src\ReservationRooms\Domain\Entities\WriteReservationRoom;
use Src\ReservationRooms\Domain\Interfaces\ReservationRoomsRepository;

final class AddReservationRoom
{
    public function __construct(
        private readonly ReservationRoomsRepository $repository
    ) {
    }

    public function execute(WriteReservationRoom $reservationRoom): void
    {
        $this->repository->addReservationRoom($reservationRoom);
    }
}
