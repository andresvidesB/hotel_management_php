<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Application\UseCases;

use Src\ReservationProducts\Domain\Interfaces\ReservationProductsRepository;
use Src\ReservationProducts\Domain\Entities\ReadReservationProduct;

final class GetReservationProducts
{
    public function __construct(
        private readonly ReservationProductsRepository $repository
    ) {
    }

    /**
     * @return ReadReservationProduct[]
     */
    public function execute(): array
    {
        return $this->repository->getReservationProducts();
    }
}
