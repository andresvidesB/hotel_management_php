<?php

declare(strict_types=1);

namespace Src\ReservationReports\Infrastructure\Repositories;

use Src\ReservationReports\Domain\Entities\ReadReservationReport;
use Src\ReservationReports\Domain\Entities\WriteReservationReport;
use Src\ReservationReports\Domain\Interfaces\ReservationReportsRepository;
use Src\ReservationReports\Domain\ValueObjects\ReservationReportContent;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationReportsRepository implements ReservationReportsRepository
{
    public function addReservationReport(WriteReservationReport $report): void
    {
        // Mock sin persistencia real
    }

    public function updateReservationReport(WriteReservationReport $report): void
    {
        // Mock
    }

    public function deleteReservationReport(Identifier $reservationId): void
    {
        // Mock
    }

    public function getReportByReservationId(Identifier $reservationId): ?ReadReservationReport
    {
        foreach ($this->seed() as $item) {
            if ($item->getReservationId()->getValue() === $reservationId->getValue()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @return ReadReservationReport[]
     * @psalm-return list<ReadReservationReport>
     */
    public function getReservationReports(): array
    {
        return $this->seed();
    }

    /**
     * Dataset de prueba consistente.
     *
     * @return list<ReadReservationReport>
     */
    private function seed(): array
    {
        return [
            $this->make(
                '1',
                'Reporte inicial de la reserva: llegada estimada a las 15:00.',
                12345
            ),
            $this->make(
                '2',
                'Notas de la reserva: requiere habitación accesible y cuna.',
                12345
            ),
        ];
    }

    private function make(
        string $reservationId,
        string $content,
        int $dateTime
    ): ReadReservationReport {
        return new ReadReservationReport(
            new Identifier($reservationId),
            new ReservationReportContent($content),
            new TimeStamp($dateTime)
        );
    }
}
