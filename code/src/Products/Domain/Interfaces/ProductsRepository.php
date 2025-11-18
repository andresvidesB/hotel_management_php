<?php

declare(strict_types=1);

namespace Src\Products\Domain\Interfaces;

use Src\Products\Domain\Entities\ReadProduct;
use Src\Products\Domain\Entities\WriteProduct;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ProductsRepository
{
    public function addProduct(WriteProduct $product): Identifier;
    public function updateProduct(WriteProduct $product): void;

    /** @return ReadProduct|null */
    public function getProductById(Identifier $id): ?ReadProduct;

    public function deleteProduct(Identifier $id): void;

    /**
     * @return ReadProduct[]           Elementos del array son ReadProduct
     * @psalm-return list<ReadProduct> Secuencia indexada (0..n-1), sin huecos
     * @phpstan-return list<ReadProduct>
     */
    public function getProducts(): array;
}
