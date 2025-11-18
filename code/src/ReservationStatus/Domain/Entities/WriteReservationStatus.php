<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class WriteReservationStatus
{
    private Identifier $reservationId;
    private Identifier $statusId;
    private TimeStamp $changedAt;

    public function __construct(
        Identifier $reservationId,
        Identifier $statusId,
        TimeStamp $changedAt
    ) {
        $this->reservationId = $reservationId;
        $this->statusId      = $statusId;
        $this->changedAt     = $changedAt;
    }

    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    public function getStatusId(): Identifier
    {
        return $this->statusId;
    }

    public function getChangedAt(): TimeStamp
    {
        return $this->changedAt;
    }

    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function setStatusId(Identifier $statusId): void
    {
        $this->statusId = $statusId;
    }

    public function setChangedAt(TimeStamp $changedAt): void
    {
        $this->changedAt = $changedAt;
    }
}
