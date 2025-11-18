<?php

namespace Src\Rooms\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class RoomName extends CustomString
{
    public function verifyValue(): void
    {
    }
}