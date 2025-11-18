<?php
// File: src/Products/Infrastructure/Factories/ProductFactory.php

declare(strict_types=1);

namespace Src\Products\Infrastructure\Factories;

use Src\Products\Domain\Entities\WriteProduct;
// 1. Importamos los Value Objects
use Src\Products\Domain\ValueObjects\ProductName;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class ProductFactory
{
    public static function writeProductFromArray(array $data): WriteProduct
    {
        // 2. Envolvemos los valores en sus objetos
        return new WriteProduct(
            new Identifier($data['product_id'] ?? ''),
            new ProductName($data['product_name']),
            new Price((float) $data['product_price'])
        );
    }
}