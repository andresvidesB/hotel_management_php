<?php

declare(strict_types=1);

namespace Src\Reservations\Infrastructure\Repositories;

use Src\Reservations\Domain\Entities\ReadReservation;
use Src\Reservations\Domain\Interfaces\ReservationsRepository;
use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Reservations\Domain\ValueObjects\ReservationSource;
use Src\Reservations\Domain\ValueObjects\ReservationCreationDate;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlReservationsRepository implements ReservationsRepository
{
    public function addReservation(WriteReservation $reservation): Identifier
    {
        return new Identifier("00000000-0000-0000-0000-000000000401");
    }

    public function updateReservation(WriteReservation $reservation): void {}

    public function deleteReservation(Identifier $id): void {}

    public function getReservationById(Identifier $id): ?ReadReservation
    {
        foreach ($this->seed() as $reservation) {
            if ($reservation->getId()->getValue() === $id->getValue()) {
                return $reservation;
            }
        }
        return null;
    }

    public function getReservations(): array
    {
        return $this->seed();
    }

    private function seed(): array
    {
        return [
            $this->make(
                "1",
                "web",
                "11111111-1111-1111-1111-111111111111",
                "2025-01-01 08:30:00"
            ),
            $this->make(
                "2",
                "agencia",
                "22222222-2222-2222-2222-222222222222",
                "2025-01-20 10:00:00"
            ),
        ];
    }

    private function make(
        string $id,
        string $source,
        string $userId,
        string $createdAt
    ): ReadReservation {
        return new ReadReservation(
            new Identifier($id),
            new ReservationSource($source),
            new Identifier($userId),
            new ReservationCreationDate($createdAt)
        );
    }
}
