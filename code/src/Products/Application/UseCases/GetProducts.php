<?php

declare(strict_types=1);

namespace Src\Products\Application\UseCases;

use Src\Products\Domain\Entities\ReadProduct;
use Src\Products\Domain\Interfaces\ProductsRepository;

final class GetProducts
{
    public function __construct(
        private readonly ProductsRepository $productsRepository
    ) {
    }

    /**
     * @return ReadProduct[]
     * @psalm-return list<ReadProduct>
     * @phpstan-return list<ReadProduct>
     */
    public function execute(): array
    {
        return $this->productsRepository->getProducts();
    }
}
