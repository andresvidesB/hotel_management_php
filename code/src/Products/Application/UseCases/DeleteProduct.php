<?php

declare(strict_types=1);

namespace Src\Products\Application\UseCases;

use Src\Products\Domain\Interfaces\ProductsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteProduct
{
    public function __construct(
        private readonly ProductsRepository $productsRepository
    ) {
    }

    public function execute(Identifier $id): void
    {
        $this->productsRepository->deleteProduct($id);
    }
}
