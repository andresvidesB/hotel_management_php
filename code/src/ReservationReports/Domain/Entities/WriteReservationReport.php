<?php

declare(strict_types=1);

namespace Src\ReservationReports\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\ReservationReports\Domain\ValueObjects\ReservationReportContent;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class WriteReservationReport
{
    private Identifier $reservationId;
    private ReservationReportContent $content;
    private TimeStamp $reportDate;

    public function __construct(
        Identifier $reservationId,
        ReservationReportContent $content,
        TimeStamp $reportDate
    ) {
        $this->reservationId = $reservationId;
        $this->content       = $content;
        $this->reportDate    = $reportDate;
    }

    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    public function getContent(): ReservationReportContent
    {
        return $this->content;
    }

    public function getReportDate(): TimeStamp
    {
        return $this->reportDate;
    }

    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function setContent(ReservationReportContent $content): void
    {
        $this->content = $content;
    }

    public function setReportDate(TimeStamp $reportDate): void
    {
        $this->reportDate = $reportDate;
    }
}
