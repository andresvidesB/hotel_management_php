<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\DefinedValue;

abstract class CustomInteger extends DefinedValue
{
    public function __construct(int $value){
        $this->value = (int) $value;
        $this->isDefined();
        $this->isValidInteger();
        $this->verifyValue();
    }
    protected function isValidInteger(): void
    {
        if (!is_integer($this->value)) {
            throw new \InvalidArgumentException("Value must be an integer: {$this->value}");
        }
    }
}