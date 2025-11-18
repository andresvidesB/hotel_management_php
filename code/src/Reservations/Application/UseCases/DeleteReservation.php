<?php

declare(strict_types=1);

namespace Src\Reservations\Application\UseCases;

use Src\Reservations\Domain\Interfaces\ReservationsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteReservation
{
    public function __construct(
        private readonly ReservationsRepository $repository
    ) {
    }

    public function execute(Identifier $id): void
    {
        $this->repository->deleteReservation($id);
    }
}
