<?php
// Archivo: src/ReservationGuests/Infrastructure/Factories/ReservationGuestFactory.php

declare(strict_types=1);

namespace Src\ReservationGuests\Infrastructure\Factories;

use Src\ReservationGuests\Domain\Entities\WriteReservationGuest;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationGuestFactory
{
    public static function writeReservationGuestFromArray(array $data): WriteReservationGuest
    {
        return new WriteReservationGuest(
            new Identifier($data['reservation_guest_guest_id']),
            new Identifier($data['reservation_guest_reservation_id'])
        );
    }
}