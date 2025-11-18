<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Infrastructure\Factories;

use Src\ReservationStatus\Domain\Entities\WriteReservationStatus;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationStatusFactory
{
    public static function writeReservationStatusFromArray(array $data): WriteReservationStatus
    {
        return new WriteReservationStatus(
            new Identifier($data['reservation_status_reservation_id']),
            new Identifier($data['reservation_status_status_id']),
            new TimeStamp($data['reservation_status_changed_at'])
        );
    }
}
