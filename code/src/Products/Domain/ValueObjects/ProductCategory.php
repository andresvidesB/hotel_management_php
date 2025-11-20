<?php
declare(strict_types=1);

namespace Src\Products\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class ProductCategory extends CustomString
{
    public function verifyValue(): void
    {
        // Aquí podrías validar que sea una de las permitidas, 
        // pero por ahora aceptamos cualquier string no vacío.
    }
}