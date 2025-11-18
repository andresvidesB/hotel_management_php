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
        // ESTA ES LA LÍNEA QUE FALTABA
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}