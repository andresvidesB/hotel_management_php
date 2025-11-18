<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomUnsignedInteger;

final class ReservationProductQuantity extends CustomUnsignedInteger
{
    public function verifyValue(): void
    {

    }
}
