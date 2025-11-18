<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\DefinedValue;

abstract class CustomFloat extends DefinedValue
{
    public function __construct(float $value){
        $this->value = (float) $value;
        $this->isDefined();
        $this->isValidFloat();
        $this->verifyValue();
    }
    protected function isValidFloat(): void
    {
        if (!is_float($this->value)) {
            throw new \InvalidArgumentException("Value must be of type float: {$this->value}");
        }
    }
}