<?php

declare(strict_types=1);

namespace Src\Products\Application\UseCases;

use Src\Products\Domain\Entities\WriteProduct;
use Src\Products\Domain\Interfaces\ProductsRepository;

final class UpdateProduct
{
    public function __construct(
        private readonly ProductsRepository $productsRepository
    ) {
    }

    public function execute(WriteProduct $product): void
    {
        $this->productsRepository->updateProduct($product);
    }
}
