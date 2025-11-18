<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\DefinedValue;

abstract class CustomString extends DefinedValue
{

    public function __construct(string $value)
    {
        $this->value = (string) $value;
        $this->isDefined();
        $this->isValidString();
        $this->verifyValue();
    }

    protected function isValidString(): void
    {
        if (!is_string($this->value)) {
            throw new \InvalidArgumentException("Value must be a string: {$this->value}");
        }

        $this->value = trim($this->value);
        if ($this->value === '') {
            throw new \InvalidArgumentException("Value must not be empty: {$this->value}");
        }

        $this->value = htmlspecialchars($this->value, ENT_QUOTES, 'UTF-8');
    }

}