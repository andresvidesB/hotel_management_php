<?php

declare(strict_types=1);

namespace Src\Products\Domain\Entities;

use Src\Products\Domain\ValueObjects\ProductName;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class ReadProduct
{
    private Identifier $id;
    private ProductName $name;
    private Price $price;

    public function __construct(
        Identifier $id,
        ProductName $name,
        Price $price
    ) {
        $this->id    = $id;
        $this->name  = $name;
        $this->price = $price;
    }

    // GETTERS
    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): ProductName
    {
        return $this->name;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }

    // SETTERS
    public function setId(Identifier $id): void
    {
        $this->id = $id;
    }

    public function setName(ProductName $name): void
    {
        $this->name = $name;
    }

    public function setPrice(Price $price): void
    {
        $this->price = $price;
    }

    public function toArray(): array
    {
        return [
            'product_id'    => $this->getId()->getValue(),
            'product_name'  => $this->getName()->getValue(),
            'product_price' => $this->getPrice()->getValue(),
        ];
    }
}
