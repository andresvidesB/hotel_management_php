<?php

declare(strict_types=1);

namespace Src\Products\Infrastructure\Factories;

use Src\Products\Domain\Entities\WriteProduct;
use Src\Products\Domain\ValueObjects\ProductName;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class ProductFactory
{
    public static function writeProductFromArray(array $data): WriteProduct
    {
        return new WriteProduct(
            new Identifier($data['product_id'] ?? ''),        // puede venir vacío para Add
            new ProductName($data['product_name']),
            new Price((float) $data['product_price'])
        );
    }
}
