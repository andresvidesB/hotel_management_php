<?php

declare(strict_types=1);

namespace Src\Roles\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class RoleName extends CustomString
{
    public function verifyValue(): void
    {

    }
}
