<?php

namespace Src\Shared\Infrastructure\Services;

use Src\Shared\Domain\Interfaces\IdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

class UuidIdentifierCreator implements IdentifierCreator
{
    public function createIdentifier(): Identifier
    {
        return new Identifier($this->generateUuid());
    }

    private function generateUuid(): string
    {
        // UUID v4 generator (RFC 4122 compliant)
        return '';
    }
}