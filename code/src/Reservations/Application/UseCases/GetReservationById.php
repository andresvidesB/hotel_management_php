<?php

declare(strict_types=1);

namespace Src\Reservations\Application\UseCases;

use Src\Reservations\Domain\Entities\ReadReservation;
use Src\Reservations\Domain\Interfaces\ReservationsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetReservationById
{
    public function __construct(
        private readonly ReservationsRepository $repository
    ) {
    }

    public function execute(Identifier $id): ?ReadReservation
    {
        return $this->repository->getReservationById($id);
    }
}
