<?php

declare(strict_types=1);

namespace Src\Reservations\Application\UseCases;

use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Reservations\Domain\Interfaces\ReservationsRepository;

final class UpdateReservation
{
    public function __construct(
        private readonly ReservationsRepository $repository
    ) {
    }

    public function execute(WriteReservation $reservation): void
    {
        $this->repository->updateReservation($reservation);
    }
}
