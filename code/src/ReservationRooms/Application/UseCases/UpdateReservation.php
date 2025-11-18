<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Application\UseCases;

use Src\ReservationRooms\Domain\Interfaces\ReservationRoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteReservationRoom
{
    public function __construct(
        private readonly ReservationRoomsRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId, Identifier $roomId): void
    {
        $this->repository->deleteReservationRoom($reservationId, $roomId);
    }
}
