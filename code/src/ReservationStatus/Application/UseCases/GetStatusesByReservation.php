<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Application\UseCases;

use Src\ReservationStatus\Domain\Interfaces\ReservationStatusRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetStatusesByReservation
{
    public function __construct(
        private readonly ReservationStatusRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId): array
    {
        return $this->repository->getStatusesByReservation($reservationId);
    }
}
