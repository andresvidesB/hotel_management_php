<?php

declare(strict_types=1);

namespace Src\Reservations\Domain\Interfaces;

use Src\Reservations\Domain\Entities\ReadReservation;
use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationsRepository
{
    public function addReservation(WriteReservation $reservation): Identifier;

    public function updateReservation(WriteReservation $reservation): void;

    /** @return ReadReservation|null */
    public function getReservationById(Identifier $id): ?ReadReservation;

    public function deleteReservation(Identifier $id): void;

    /**
     * @return ReadReservation[]
     * @psalm-return list<ReadReservation>
     */
    public function getReservations(): array;
}
