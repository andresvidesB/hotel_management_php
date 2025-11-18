<?php

declare(strict_types=1);

namespace Src\ReservationReports\Application\UseCases;

use Src\ReservationReports\Domain\Interfaces\ReservationReportsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteReservationReport
{
    public function __construct(
        private readonly ReservationReportsRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId): void
    {
        $this->repository->deleteReservationReport($reservationId);
    }
}
