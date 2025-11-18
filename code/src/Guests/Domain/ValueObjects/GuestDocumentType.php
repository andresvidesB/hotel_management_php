<?php

declare(strict_types=1);

namespace Src\Guests\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class GuestDocumentType extends CustomString
{
    public function verifyValue(): void
    {
    }
}
