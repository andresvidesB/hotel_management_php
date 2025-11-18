<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Infrastructure\Services;

use Src\ReservationProducts\Application\UseCases\AddReservationProduct;
use Src\ReservationProducts\Application\UseCases\UpdateReservationProduct;
use Src\ReservationProducts\Application\UseCases\DeleteReservationProduct;
use Src\ReservationProducts\Application\UseCases\GetReservationProducts;
use Src\ReservationProducts\Application\UseCases\GetProductsByReservation;
use Src\ReservationProducts\Application\UseCases\GetReservationsByProduct;
use Src\ReservationProducts\Domain\Entities\ReadReservationProduct;
use Src\ReservationProducts\Infrastructure\Factories\ReservationProductFactory;
use Src\ReservationProducts\Infrastructure\Repositories\MySqlReservationProductsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReservationProductsController
{
    public static function addReservationProduct(array $data): void
    {
        $entity  = ReservationProductFactory::writeReservationProductFromArray($data);
        $useCase = new AddReservationProduct(self::repo());
        $useCase->execute($entity);
    }

    public static function updateReservationProduct(array $data): void
    {
        $entity  = ReservationProductFactory::writeReservationProductFromArray($data);
        $useCase = new UpdateReservationProduct(self::repo());
        $useCase->execute($entity);
    }

    public static function deleteReservationProduct(string $reservationId, string $productId): void
    {
        $useCase = new DeleteReservationProduct(self::repo());
        $useCase->execute(
            new Identifier($reservationId),
            new Identifier($productId)
        );
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getReservationProducts(): array
    {
        $useCase = new GetReservationProducts(self::repo());
        $items   = $useCase->execute();

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationProduct) {
                $result[] = $relation->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getProductsByReservation(string $reservationId): array
    {
        $useCase = new GetProductsByReservation(self::repo());
        $items   = $useCase->execute(new Identifier($reservationId));

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationProduct) {
                $result[] = $relation->toArray();
            }
        }

        return $result;
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getReservationsByProduct(string $productId): array
    {
        $useCase = new GetReservationsByProduct(self::repo());
        $items   = $useCase->execute(new Identifier($productId));

        $result = [];
        foreach ($items as $relation) {
            if ($relation instanceof ReadReservationProduct) {
                $result[] = $relation->toArray();
            }
        }

        return $result;
    }

    private static function repo(): MySqlReservationProductsRepository
    {
        return new MySqlReservationProductsRepository();
    }
}
