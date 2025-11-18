<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;

final class ReadReservationGuest
{
    private Identifier $guestId;
    private Identifier $reservationId;

    public function __construct(
        Identifier $guestId,
        Identifier $reservationId
    ) {
        $this->guestId      = $guestId;
        $this->reservationId = $reservationId;
    }

    // GETTERS
    public function getGuestId(): Identifier
    {
        return $this->guestId;
    }

    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    // SETTERS
    public function setGuestId(Identifier $guestId): void
    {
        $this->guestId = $guestId;
    }

    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function toArray(): array
    {
        return [
            'reservation_guest_guest_id'      => $this->guestId->getValue(),
            'reservation_guest_reservation_id'=> $this->reservationId->getValue(),
        ];
    }
}
