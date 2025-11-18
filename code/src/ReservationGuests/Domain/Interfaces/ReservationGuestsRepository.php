<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Domain\Interfaces;

use Src\ReservationGuests\Domain\Entities\ReadReservationGuest;
use Src\ReservationGuests\Domain\Entities\WriteReservationGuest;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationGuestsRepository
{
    public function addReservationGuest(WriteReservationGuest $relation): void;

    public function deleteReservationGuest(
        Identifier $guestId,
        Identifier $reservationId
    ): void;

    /**
     * @return ReadReservationGuest[]
     * @psalm-return list<ReadReservationGuest>
     * @phpstan-return list<ReadReservationGuest>
     */
    public function getReservationGuests(): array;

    /**
     * @return ReadReservationGuest[]
     * @psalm-return list<ReadReservationGuest>
     */
    public function getGuestsByReservation(Identifier $reservationId): array;

    /**
     * @return ReadReservationGuest[]
     * @psalm-return list<ReadReservationGuest>
     */
    public function getReservationsByGuest(Identifier $guestId): array;
}
