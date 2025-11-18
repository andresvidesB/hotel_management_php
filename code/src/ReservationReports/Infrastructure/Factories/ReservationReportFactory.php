<?php

declare(strict_types=1);

namespace Src\ReservationReports\Infrastructure\Factories;

use Src\ReservationReports\Domain\Entities\WriteReservationReport;
use Src\ReservationReports\Domain\ValueObjects\ReservationReportContent;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationReportFactory
{
    public static function writeReservationReportFromArray(array $data): WriteReservationReport
    {
        return new WriteReservationReport(
            new Identifier($data['reservation_report_reservation_id']),
            new ReservationReportContent($data['reservation_report_content']),
            new TimeStamp($data['reservation_report_created_at'])
        );
    }
}
