<?php

declare(strict_types=1);

namespace Src\Reservations\Infrastructure\Factories;

use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Reservations\Domain\ValueObjects\ReservationSource;
use Src\Reservations\Domain\ValueObjects\ReservationCreationDate;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationFactory
{
    public static function writeReservationFromArray(array $data): WriteReservation
    {
        return new WriteReservation(
            new Identifier($data['reservation_id'] ?? ''),
            new ReservationSource($data['reservation_source'] ?? ''),
            new Identifier($data['reservation_user_id']),
            new ReservationCreationDate($data['reservation_created_at'])
        );
    }
}
