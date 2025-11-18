<?php

declare(strict_types=1);

namespace Src\ReservationReports\Infrastructure\Services;

use Src\ReservationReports\Application\UseCases\AddReservationReport;
use Src\ReservationReports\Application\UseCases\UpdateReservationReport;
use Src\ReservationReports\Application\UseCases\DeleteReservationReport;
use Src\ReservationReports\Application\UseCases\GetReservationReportByReservationId;
use Src\ReservationReports\Application\UseCases\GetReservationReports;
use Src\ReservationReports\Domain\Entities\ReadReservationReport;
use Src\ReservationReports\Infrastructure\Factories\ReservationReportFactory;
use Src\ReservationReports\Infrastructure\Repositories\MySqlReservationReportsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationReportsController
{
    public static function addReservationReport(array $data): void
    {
        $entity  = ReservationReportFactory::writeReservationReportFromArray($data);
        $useCase = new AddReservationReport(self::repo());
        $useCase->execute($entity);
    }

    public static function updateReservationReport(array $data): void
    {
        $entity  = ReservationReportFactory::writeReservationReportFromArray($data);
        $useCase = new UpdateReservationReport(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservationReport(string $reservationId): void
    {
        $useCase = new DeleteReservationReport(self::repo());
        $useCase->execute(new Identifier($reservationId));
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getReservationReports(): array
    {
        $useCase = new GetReservationReports(self::repo());
        $items   = $useCase->execute();

        $result = [];
        foreach ($items as $report) {
            if ($report instanceof ReadReservationReport) {
                $result[] = $report->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array<string,mixed>
     */
    public static function getReservationReportByReservationId(string $reservationId): array
    {
        $useCase = new GetReservationReportByReservationId(self::repo());
        $report  = $useCase->execute(new Identifier($reservationId));

        return $report instanceof ReadReservationReport ? $report->toArray() : [];
    }

    private static function repo(): MySqlReservationReportsRepository
    {
        return new MySqlReservationReportsRepository();
    }
}
