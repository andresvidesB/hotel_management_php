<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;

final class WriteReservationGuest
{
    private Identifier $guestId;
    private Identifier $reservationId;

    public function __construct(
        Identifier $guestId,
        Identifier $reservationId
    ) {
        $this->guestId       = $guestId;
        $this->reservationId = $reservationId;
    }

    public function getGuestId(): Identifier
    {
        return $this->guestId;
    }

    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    public function setGuestId(Identifier $guestId): void
    {
        $this->guestId = $guestId;
    }

    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }
}
