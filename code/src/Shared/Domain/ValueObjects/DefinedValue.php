<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\Exceptions\InvalidArgumentException;

abstract class DefinedValue
{
    protected mixed $value;

    public function __construct(mixed $value){
        $this->value = $value;
        $this->isDefined();
        $this->verifyValue();
    }

    protected function isDefined(): void
    {
        if (!isset($this->value)) {
            throw new InvalidArgumentException('Value must be defined.');
        }

        if (is_object($this->value) && $this->value === null) {
            throw new InvalidArgumentException('Value cannot be null.');
        }
    }

    public function getValue()
    {
        return $this->value;
    }

    abstract public function verifyValue();

}