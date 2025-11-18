<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReadReservationStatus
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

    // GETTERS
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

    // SETTERS
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

    public function toArray(): array
    {
        return [
            'reservation_status_reservation_id' => $this->reservationId->getValue(),
            'reservation_status_status_id'      => $this->statusId->getValue(),
            'reservation_status_changed_at'     => $this->changedAt->getValue(),
        ];
    }
}
