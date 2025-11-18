<?php

declare(strict_types=1);

namespace Src\ReservationReports\Domain\Interfaces;

use Src\ReservationReports\Domain\Entities\ReadReservationReport;
use Src\ReservationReports\Domain\Entities\WriteReservationReport;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationReportsRepository
{
    public function addReservationReport(WriteReservationReport $report): void;

    public function updateReservationReport(WriteReservationReport $report): void;

    public function deleteReservationReport(Identifier $reservationId): void;

    /** @return ReadReservationReport|null */
    public function getReportByReservationId(Identifier $reservationId): ?ReadReservationReport;

    /**
     * @return ReadReservationReport[]
     * @psalm-return list<ReadReservationReport>
     */
    public function getReservationReports(): array;
}
