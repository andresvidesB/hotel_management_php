<?php

declare(strict_types=1);

namespace Src\Statuses\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class StatusName extends CustomString
{
    public function verifyValue(): void
    {

    }
}
