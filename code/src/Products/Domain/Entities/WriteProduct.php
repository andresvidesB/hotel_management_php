<?php
declare(strict_types=1);

namespace Src\Products\Domain\Entities;

use Src\Products\Domain\ValueObjects\ProductName;
use Src\Products\Domain\ValueObjects\ProductCategory; // Nuevo
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class WriteProduct
{
    private Identifier $id;
    private ProductName $name;
    private Price $price;
    private ProductCategory $category; // Nuevo

    public function __construct(
        Identifier $id,
        ProductName $name,
        Price $price,
        ProductCategory $category
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->price = $price;
        $this->category = $category;
    }

    public function getId(): Identifier { return $this->id; }
    public function getName(): ProductName { return $this->name; }
    public function getPrice(): Price { return $this->price; }
    public function getCategory(): ProductCategory { return $this->category; }

    public function setId(Identifier $id): void { $this->id = $id; }
}