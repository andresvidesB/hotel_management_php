<?php

declare(strict_types=1);

namespace Src\Reservations\Application\UseCases;

use Src\Reservations\Domain\Entities\ReadReservation;
use Src\Reservations\Domain\Interfaces\ReservationsRepository;

final class GetReservations
{
    public function __construct(
        private readonly ReservationsRepository $repository
    ) {
    }

    /**
     * @return ReadReservation[]
     */
    public function execute(): array
    {
        return $this->repository->getReservations();
    }
}
