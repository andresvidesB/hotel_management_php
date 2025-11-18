<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Application\UseCases;

use Src\ReservationProducts\Domain\Interfaces\ReservationProductsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetProductsByReservation
{
    public function __construct(
        private readonly ReservationProductsRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId): array
    {
        return $this->repository->getProductsByReservation($reservationId);
    }
}
