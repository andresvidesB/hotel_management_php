<?php

declare(strict_types=1);

namespace Src\ReservationReports\Application\UseCases;

use Src\ReservationReports\Domain\Entities\WriteReservationReport;
use Src\ReservationReports\Domain\Interfaces\ReservationReportsRepository;

final class AddReservationReport
{
    public function __construct(
        private readonly ReservationReportsRepository $repository
    ) {
    }

    public function execute(WriteReservationReport $report): void
    {
        $this->repository->addReservationReport($report);
    }
}
