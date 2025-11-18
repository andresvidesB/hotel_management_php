<?php

namespace Src\Shared\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\DefinedValue;

abstract class CustomUnsignedInteger extends CustomInteger
{
    public function __construct(int $value){
        $this->value = (int) $value;
        $this->isDefined();
        $this->isValidInteger();
        $this->verifyValue();
    }

    protected function isPositive(){
        if($this->value < 0){
            throw new \InvalidArgumentException("Value must be a positive integer: {$this->value}");
        }
    }
}