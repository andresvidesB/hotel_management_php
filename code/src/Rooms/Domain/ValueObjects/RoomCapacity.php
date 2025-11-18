<?php

namespace Src\Rooms\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomUnsignedInteger;

final class RoomCapacity extends CustomUnsignedInteger
{
    public function verifyValue(): void
    {
    }
}