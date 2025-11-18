<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Application\UseCases;

use Src\ReservationProducts\Domain\Entities\WriteReservationProduct;
use Src\ReservationProducts\Domain\Interfaces\ReservationProductsRepository;

final class UpdateReservationProduct
{
    public function __construct(
        private readonly ReservationProductsRepository $repository
    ) {
    }

    public function execute(WriteReservationProduct $relation): void
    {
        $this->repository->updateReservationProduct($relation);
    }
}
