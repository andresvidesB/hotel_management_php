<?php
namespace Src\Shared\Domain\Interfaces;

use Src\Shared\Domain\ValueObjects\Identifier;

interface IdentifierCreator
{
    public function createIdentifier(): Identifier;
}