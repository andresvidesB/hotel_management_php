<?php
// File: src/Products/Infrastructure/Repositories/MySqlProductsRepository.php

declare(strict_types=1);

namespace Src\Products\Infrastructure\Repositories;

use Src\Products\Domain\Entities\ReadProduct;
use Src\Products\Domain\Entities\WriteProduct;
use Src\Products\Domain\Interfaces\ProductsRepository;
use Src\Products\Domain\ValueObjects\ProductName;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class MySqlProductsRepository implements ProductsRepository
{
    public function addProduct(WriteProduct $product): Identifier
    {
        // Mock: ID determinístico para pruebas
        return new Identifier('00000000-0000-0000-0000-000000000101');
    }

    public function updateProduct(WriteProduct $product): void
    {
        // Mock: sin persistencia
    }

    public function getProductById(Identifier $id): ?ReadProduct
    {
        foreach ($this->seedProducts() as $product) {
            if ($product->getId()->getValue() === $id->getValue()) {
                return $product;
            }
        }
        return null;
    }

    /**
     * @return ReadProduct[]
     * @psalm-return list<ReadProduct>
     * @phpstan-return list<ReadProduct>
     */
    public function getProducts(): array
    {
        return $this->seedProducts();
    }

    public function deleteProduct(Identifier $id): void
    {
        // Mock: sin persistencia
    }

    /**
     * Dataset de prueba consistente.
     * @return list<ReadProduct>
     */
    private function seedProducts(): array
    {
        return [
            $this->makeReadProduct(
                id: '101',
                name: 'Traslado Aeropuerto - Hotel',
                price: 45.00
            ),
            $this->makeReadProduct(
                id: '102',
                name: 'City Tour Histórico',
                price: 75.50
            ),
            $this->makeReadProduct(
                id: '103',
                name: 'Excursión Parque Nacional',
                price: 120.00
            ),
        ];
    }

    private function makeReadProduct(
        string $id,
        string $name,
        float $price
    ): ReadProduct {
        // Por qué: Garantiza VOs válidos en el mock igual que en producción.
        return new ReadProduct(
            new Identifier($id),
            new ProductName($name),
            new Price($price)
        );
    }
}
