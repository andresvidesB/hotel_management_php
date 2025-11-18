<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Infrastructure\Repositories;

use Src\ReservationGuests\Domain\Entities\ReadReservationGuest;
use Src\ReservationGuests\Domain\Entities\WriteReservationGuest;
use Src\ReservationGuests\Domain\Interfaces\ReservationGuestsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlReservationGuestsRepository implements ReservationGuestsRepository
{
    public function addReservationGuest(WriteReservationGuest $relation): void
    {
        // Mock
    }

    public function deleteReservationGuest(
        Identifier $guestId,
        Identifier $reservationId
    ): void {
        // Mock
    }

    public function getReservationGuests(): array
    {
        return $this->seed();
    }

    public function getGuestsByReservation(Identifier $reservationId): array
    {
        $result = [];
        foreach ($this->seed() as $relation) {
            if ($relation->getReservationId()->getValue() === $reservationId->getValue()) {
                $result[] = $relation;
            }
        }
        return $result;
    }

    public function getReservationsByGuest(Identifier $guestId): array
    {
        $result = [];
        foreach ($this->seed() as $relation) {
            if ($relation->getGuestId()->getValue() === $guestId->getValue()) {
                $result[] = $relation;
            }
        }
        return $result;
    }

    private function seed(): array
    {
        return [
            $this->make('11111111-1111-1111-1111-111111111111', '1'),
            $this->make('11111111-1111-1111-1111-111111111111', '2'),
            $this->make('22222222-2222-2222-2222-222222222222', '2'),
        ];
    }

    private function make(string $guestId, string $reservationId): ReadReservationGuest
    {
        return new ReadReservationGuest(
            new Identifier($guestId),
            new Identifier($reservationId)
        );
    }
}
