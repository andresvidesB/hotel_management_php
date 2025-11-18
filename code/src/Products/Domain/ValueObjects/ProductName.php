<?php

declare(strict_types=1);

namespace Src\Products\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class ProductName extends CustomString
{
    public function verifyValue(): void
    {

    }
}
