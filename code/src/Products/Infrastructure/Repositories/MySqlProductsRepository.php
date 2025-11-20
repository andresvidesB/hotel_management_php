<?php
declare(strict_types=1);

namespace Src\Products\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Products\Domain\Entities\ReadProduct;
use Src\Products\Domain\Entities\WriteProduct;
use Src\Products\Domain\Interfaces\ProductsRepository;
use Src\Products\Domain\ValueObjects\ProductName;
use Src\Products\Domain\ValueObjects\ProductCategory;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class MySqlProductsRepository implements ProductsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addProduct(WriteProduct $product): Identifier
    {
        // Guardamos Stock inicial (Asumimos que viene en el array o default 0)
        // Nota: Como WriteProduct no tiene campo Stock en tu diseño actual, lo manejamos directo aquí o lo añadimos a la entidad.
        // Para ser rápidos, asumimos que pasas el stock en un método separado o lo inyectamos.
        // VAMOS A HACERLO SIMPLE: Usaremos un valor por defecto o lo actualizamos luego.
        
        $sql = "INSERT INTO Productos (Id, Nombre, Precio, Categoria, Stock) VALUES (:id, :name, :price, :cat, :stock)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'    => $product->getId()->getValue(),
            ':name'  => $product->getName()->getValue(),
            ':price' => $product->getPrice()->getValue(),
            ':cat'   => $product->getCategory()->getValue(),
            ':stock' => 50 // Stock inicial por defecto
        ]);
        return $product->getId();
    }

    /**
     * MÉTODO NUEVO: Actualizar stock manual desde el formulario
     */
    public function updateProductWithStock(string $id, string $name, float $price, string $cat, int $stock): void
    {
        $sql = "UPDATE Productos SET Nombre = :name, Precio = :price, Categoria = :cat, Stock = :stock WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'    => $id,
            ':name'  => $name,
            ':price' => $price,
            ':cat'   => $cat,
            ':stock' => $stock
        ]);
    }

    /**
     * MÉTODO NUEVO: Restar Stock al consumir
     */
    public function decreaseStock(string $productId, int $quantity): void
    {
        $sql = "UPDATE Productos SET Stock = Stock - :qty WHERE Id = :id AND Stock >= :qty";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':qty' => $quantity, ':id' => $productId]);
        
        if ($stmt->rowCount() === 0) {
            throw new \Exception("No hay suficiente stock para realizar esta operación.");
        }
    }

    public function updateProduct(WriteProduct $product): void 
    {
        // Método legacy (mantener por compatibilidad de interfaz)
        $sql = "UPDATE Productos SET Nombre = :name, Precio = :price, Categoria = :cat WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $product->getId()->getValue(),
            ':name' => $product->getName()->getValue(),
            ':price' => $product->getPrice()->getValue(),
            ':cat' => $product->getCategory()->getValue()
        ]);
    }

    public function getProductById(Identifier $id): ?ReadProduct
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Productos WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch();
        if (!$row) return null;
        return $this->mapRow($row);
    }

    public function getProducts(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Productos ORDER BY Categoria ASC, Nombre ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        $products = [];
        foreach ($rows as $row) {
            // Devolvemos array asociativo enriquecido con Stock para el frontend
            $products[] = [
                'product_id' => $row['Id'],
                'product_name' => $row['Nombre'],
                'product_price' => $row['Precio'],
                'product_category' => $row['Categoria'] ?? 'General',
                'product_stock' => $row['Stock'] // DATO NUEVO
            ];
        }
        return $products;
    }

    public function deleteProduct(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Productos WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }

    private function mapRow(array $row): ReadProduct
    {
        return new ReadProduct(
            new Identifier($row['Id']),
            new ProductName($row['Nombre']),
            new Price((float)$row['Precio']),
            new ProductCategory($row['Categoria'] ?? 'General')
        );
    }
}