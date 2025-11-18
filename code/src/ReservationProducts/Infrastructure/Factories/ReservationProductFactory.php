<?php
// Archivo: src/ReservationProducts/Infrastructure/Factories/ReservationProductFactory.php

declare(strict_types=1);

namespace Src\ReservationProducts\Infrastructure\Factories;

use Src\ReservationProducts\Domain\Entities\WriteReservationProduct;
use Src\ReservationProducts\Domain\ValueObjects\ReservationProductQuantity;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationProductFactory
{
    public static function writeReservationProductFromArray(array $data): WriteReservationProduct
    {
        // 1. Manejo de Fecha
        $dateVal = $data['reservation_product_consumption_date'] ?? time();
        if (is_string($dateVal)) {
            $dateVal = strtotime($dateVal);
        }

        // 2. Retorno de Entidad con Value Objects
        return new WriteReservationProduct(
            new Identifier($data['reservation_product_reservation_id']),
            new Identifier($data['reservation_product_product_id']),
            new ReservationProductQuantity((int) $data['reservation_product_quantity']),
            new TimeStamp((int) $dateVal)
        );
    }
}