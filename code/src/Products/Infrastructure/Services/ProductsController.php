<?php
// File: src/Products/Infrastructure/Services/ProductsController.php

declare(strict_types=1);

namespace Src\Products\Infrastructure\Services;

use Src\Products\Application\UseCases\AddProduct;
use Src\Products\Application\UseCases\UpdateProduct;
use Src\Products\Application\UseCases\DeleteProduct;
use Src\Products\Application\UseCases\GetProducts;
use Src\Products\Application\UseCases\GetProductById;
use Src\Products\Domain\Entities\ReadProduct;
use Src\Products\Infrastructure\Factories\ProductFactory;
use Src\Products\Infrastructure\Repositories\MySqlProductsRepository;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ProductsController
{
    public static function addProduct(array $product): Identifier
    {
        $productEntity = ProductFactory::writeProductFromArray($product);
        $useCase       = new AddProduct(self::repo(), self::idCreator());

        return $useCase->execute($productEntity);
    }

    public static function updateProduct(array $product): void
    {
        $productEntity = ProductFactory::writeProductFromArray($product);
        $useCase       = new UpdateProduct(self::repo());

        $useCase->execute($productEntity);
    }

    public static function deleteProduct(string $id): void
    {
        $useCase = new DeleteProduct(self::repo());
        $useCase->execute(new Identifier($id));
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getProducts(): array
    {
        $useCase = new GetProducts(self::repo());

        /** @var ReadProduct[] $items */
        $items = $useCase->execute();

        $products = [];
        foreach ($items as $product) {
            if (!$product instanceof ReadProduct) { // por qué: evitar respuestas inconsistentes si el repo cambia
                continue;
            }
            $products[] = $product->toArray();
        }

        return $products;
    }

    /**
     * @return array<string,mixed> Empty array si no existe.
     */
    public static function getProductById(string $id): array
    {
        $useCase = new GetProductById(self::repo());
        $read    = $useCase->execute(new Identifier($id));

        return $read instanceof ReadProduct ? $read->toArray() : [];
    }

    /** Helpers estáticos para dependencias */
    private static function repo(): MySqlProductsRepository
    {
        return new MySqlProductsRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }
}
