<?php

declare(strict_types=1);

namespace Src\Reservations\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class ReservationSource extends CustomString
{
    public function verifyValue(): void
    {
    }
}
