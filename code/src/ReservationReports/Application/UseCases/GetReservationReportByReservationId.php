<?php

declare(strict_types=1);

namespace Src\ReservationReports\Application\UseCases;

use Src\ReservationReports\Domain\Entities\ReadReservationReport;
use Src\ReservationReports\Domain\Interfaces\ReservationReportsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetReservationReportByReservationId
{
    public function __construct(
        private readonly ReservationReportsRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId): ?ReadReservationReport
    {
        return $this->repository->getReportByReservationId($reservationId);
    }
}
