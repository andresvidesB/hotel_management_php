<?php

declare(strict_types=1);

namespace Src\Products\Application\UseCases;

use Src\Products\Domain\Entities\WriteProduct;
use Src\Products\Domain\Interfaces\ProductsRepository;
use Src\Shared\Domain\Interfaces\IdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class AddProduct
{
    public function __construct(
        private readonly ProductsRepository $productsRepository,
        private readonly IdentifierCreator $identifierCreator
    ) {
    }

    public function execute(WriteProduct $product): Identifier
    {
        $product->setId($this->identifierCreator->createIdentifier());
        return $this->productsRepository->addProduct($product);
    }
}
