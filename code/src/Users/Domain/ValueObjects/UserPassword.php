<?php

declare(strict_types=1);

namespace Src\Users\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class UserPassword extends CustomString
{
    public function verifyValue(): void
    {

    }
}
