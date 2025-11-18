<?php

declare(strict_types=1);

namespace Src\ReservationRooms\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReadReservationRoom
{
    private Identifier $reservationId;
    private Identifier $roomId;
    private TimeStamp $startDate;
    private TimeStamp $endDate;

    public function __construct(
        Identifier $reservationId,
        Identifier $roomId
    ) {
        $this->reservationId = $reservationId;
        $this->roomId = $roomId;
    }

    // GETTERS
    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    public function getRoomId(): Identifier
    {
        return $this->roomId;
    }

    public function getStartDate(): TimeStamp
    {
        return $this->startDate;
    }

    public function getEndDate(): TimeStamp
    {
        return $this->endDate;
    }

    // SETTERS
    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function setRoomId(Identifier $roomId): void
    {
        $this->roomId = $roomId;
    }

    public function setStartDate(TimeStamp $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function setEndDate(TimeStamp $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function toArray(): array
    {
        return [
            'reservation_room_reservation_id' => $this->reservationId->getValue(),
            'reservation_room_room_id' => $this->roomId->getValue(),
            'reservation_room_start_date' => $this->startDate->getValue(),
            'reservation_room_end_date' => $this->endDate->getValue()
        ];
    }
}
