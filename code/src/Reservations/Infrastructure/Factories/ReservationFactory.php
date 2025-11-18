<?php
// Archivo: src/Reservations/Infrastructure/Factories/ReservationFactory.php

declare(strict_types=1);

namespace Src\Reservations\Infrastructure\Factories;

use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Reservations\Domain\ValueObjects\ReservationSource;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationFactory
{
    public static function writeReservationFromArray(array $data): WriteReservation
    {
        // Manejo de fecha: Si viene string, convertir a int. Si viene int, dejar como int.
        // Si no viene, usar el momento actual (time()).
        $createdVal = $data['reservation_created_at'] ?? time();
        
        if (is_string($createdVal)) {
            $createdVal = strtotime($createdVal);
        }

        return new WriteReservation(
            new Identifier($data['reservation_id'] ?? ''),
            new ReservationSource($data['reservation_source'] ?? 'Web'),
            new Identifier($data['reservation_user_id']),
            new TimeStamp((int)$createdVal)
        );
    }
}