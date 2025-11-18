<?php

declare(strict_types=1);

namespace Src\ReservationReports\Application\UseCases;

use Src\ReservationReports\Domain\Entities\ReadReservationReport;
use Src\ReservationReports\Domain\Interfaces\ReservationReportsRepository;

final class GetReservationReports
{
    public function __construct(
        private readonly ReservationReportsRepository $repository
    ) {
    }

    /**
     * @return ReadReservationReport[]
     * @psalm-return list<ReadReservationReport>
     */
    public function execute(): array
    {
        return $this->repository->getReservationReports();
    }
}
