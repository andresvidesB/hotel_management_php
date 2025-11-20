<?php
declare(strict_types=1);

namespace Src\Products\Infrastructure\Services;

use Src\Products\Application\UseCases\AddProduct;
use Src\Products\Application\UseCases\DeleteProduct;
use Src\Products\Infrastructure\Factories\ProductFactory;
use Src\Products\Infrastructure\Repositories\MySqlProductsRepository;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ProductsController
{
    public static function addProduct(array $product): Identifier
    {
        $productEntity = ProductFactory::writeProductFromArray($product);
        $useCase = new AddProduct(self::repo(), self::idCreator());
        $id = $useCase->execute($productEntity);
        
        // Actualizar el stock inicial si viene en el array
        if (isset($product['product_stock'])) {
            self::repo()->updateProductWithStock(
                $id->getValue(), 
                $product['product_name'], 
                (float)$product['product_price'], 
                $product['product_category'], 
                (int)$product['product_stock']
            );
        }
        return $id;
    }

    public static function updateProductWithStock(array $data): void
    {
        self::repo()->updateProductWithStock(
            $data['product_id'],
            $data['product_name'],
            (float)$data['product_price'],
            $data['product_category'],
            (int)$data['product_stock']
        );
    }
    
    public static function decreaseStock(string $id, int $qty): void
    {
        self::repo()->decreaseStock($id, $qty);
    }

    public static function updateProduct(array $product): void
    {
        // Redirigimos al metodo completo
        self::updateProductWithStock($product);
    }

    public static function deleteProduct(string $id): void
    {
        $useCase = new DeleteProduct(self::repo());
        $useCase->execute(new Identifier($id));
    }

    public static function getProducts(): array
    {
        // El repositorio ya devuelve el array formateado con Stock
        return self::repo()->getProducts();
    }

    private static function repo(): MySqlProductsRepository
    {
        return new MySqlProductsRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }
}