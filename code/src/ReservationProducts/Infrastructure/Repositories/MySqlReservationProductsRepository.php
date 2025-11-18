<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Infrastructure\Repositories;

use Src\ReservationProducts\Domain\Entities\ReadReservationProduct;
use Src\ReservationProducts\Domain\Entities\WriteReservationProduct;
use Src\ReservationProducts\Domain\Interfaces\ReservationProductsRepository;
use Src\ReservationProducts\Domain\ValueObjects\ReservationProductQuantity;
use Src\ReservationProducts\Domain\ValueObjects\ReservationProductConsumptionDate;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationProductsRepository implements ReservationProductsRepository
{
    public function addReservationProduct(WriteReservationProduct $relation): void
    {
        // Mock: sin persistencia real
    }

    public function updateReservationProduct(WriteReservationProduct $relation): void
    {
        // Mock
    }

    public function deleteReservationProduct(
        Identifier $reservationId,
        Identifier $productId
    ): void {
        // Mock
    }

    public function getReservationProducts(): array
    {
        return $this->seed();
    }

    public function getProductsByReservation(Identifier $reservationId): array
    {
        $result = [];
        foreach ($this->seed() as $relation) {
            if ($relation->getReservationId()->getValue() === $reservationId->getValue()) {
                $result[] = $relation;
            }
        }
        return $result;
    }

    public function getReservationsByProduct(Identifier $productId): array
    {
        $result = [];
        foreach ($this->seed() as $relation) {
            if ($relation->getProductId()->getValue() === $productId->getValue()) {
                $result[] = $relation;
            }
        }
        return $result;
    }

    /**
     * @return ReadReservationProduct[]
     * @psalm-return list<ReadReservationProduct>
     */
    private function seed(): array
    {
        return [
            $this->make(
                '1',  // IdReserva
                '101',// IdProducto
                2,
                123
            ),
            $this->make(
                '1',
                '102',
                1,
                123
            ),
            $this->make(
                '2',
                '123',
                3,
                123 // sin fecha de consumo
            ),
        ];
    }

    private function make(
        string $reservationId,
        string $productId,
        int $quantity,
        int $consumptionDate
    ): ReadReservationProduct {
        return new ReadReservationProduct(
            new Identifier($reservationId),
            new Identifier($productId),
            new ReservationProductQuantity($quantity),
            new TimeStamp($consumptionDate)
        );
    }
}
