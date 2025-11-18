<?php
// File: src/Products/Infrastructure/Repositories/MySqlProductsRepository.php

declare(strict_types=1);

namespace Src\Products\Infrastructure\Repositories;

// 1. Importamos PDO y nuestra Base de Datos
use \PDO;
use Src\Shared\Infrastructure\Database;
// Tus imports
use Src\Products\Domain\Entities\ReadProduct;
use Src\Products\Domain\Entities\WriteProduct;
use Src\Products\Domain\Interfaces\ProductsRepository;
use Src\Products\Domain\ValueObjects\ProductName;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class MySqlProductsRepository implements ProductsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        // 2. Obtenemos la conexión
        $this->pdo = (new Database())->getConnection();
    }

    public function addProduct(WriteProduct $product): Identifier
    {
        $sql = "INSERT INTO Productos (Id, Nombre, Precio) 
                VALUES (:id, :nombre, :precio)";
        
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':id' => $product->getId()->getValue(),
            ':nombre' => $product->getName()->getValue(),
            ':precio' => $product->getPrice()->getValue()
        ]);
        
        return $product->getId();
    }

    public function updateProduct(WriteProduct $product): void
    {
        $sql = "UPDATE Productos SET 
                    Nombre = :nombre, 
                    Precio = :precio 
                WHERE Id = :id";
                
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':id' => $product->getId()->getValue(),
            ':nombre' => $product->getName()->getValue(),
            ':precio' => $product->getPrice()->getValue()
        ]);
    }

    public function getProductById(Identifier $id): ?ReadProduct
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre, Precio FROM Productos WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new ReadProduct(
            new Identifier($row['Id']),
            new ProductName($row['Nombre']),
            new Price((float)$row['Precio'])
        );
    }

    /**
     * @return ReadProduct[]
     * @psalm-return list<ReadProduct>
     * @phpstan-return list<ReadProduct>
     */
    public function getProducts(): array
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre, Precio FROM Productos");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $products = [];

        foreach ($rows as $row) {
            $products[] = new ReadProduct(
                new Identifier($row['Id']),
                new ProductName($row['Nombre']),
                new Price((float)$row['Precio'])
            );
        }

        return $products;
    }

    public function deleteProduct(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Productos WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }
}