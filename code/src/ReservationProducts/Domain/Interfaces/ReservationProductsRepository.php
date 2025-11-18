<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Domain\Interfaces;

use Src\ReservationProducts\Domain\Entities\ReadReservationProduct;
use Src\ReservationProducts\Domain\Entities\WriteReservationProduct;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationProductsRepository
{
    public function addReservationProduct(WriteReservationProduct $relation): void;

    public function updateReservationProduct(WriteReservationProduct $relation): void;

    public function deleteReservationProduct(
        Identifier $reservationId,
        Identifier $productId
    ): void;

    /**
     * @return ReadReservationProduct[]
     * @psalm-return list<ReadReservationProduct>
     */
    public function getReservationProducts(): array;

    /**
     * @return ReadReservationProduct[]
     * @psalm-return list<ReadReservationProduct>
     */
    public function getProductsByReservation(Identifier $reservationId): array;

    /**
     * @return ReadReservationProduct[]
     * @psalm-return list<ReadReservationProduct>
     */
    public function getReservationsByProduct(Identifier $productId): array;
}
