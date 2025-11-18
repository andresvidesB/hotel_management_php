<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Application\UseCases;

use Src\ReservationStatus\Domain\Interfaces\ReservationStatusRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteReservationStatus
{
    public function __construct(
        private readonly ReservationStatusRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId, Identifier $statusId): void
    {
        $this->repository->deleteReservationStatus($reservationId, $statusId);
    }
}
