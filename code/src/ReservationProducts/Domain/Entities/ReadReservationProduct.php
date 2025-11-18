<?php

declare(strict_types=1);

namespace Src\ReservationProducts\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\ReservationProducts\Domain\ValueObjects\ReservationProductQuantity;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReadReservationProduct
{
    private Identifier $reservationId;
    private Identifier $productId;
    private ReservationProductQuantity $quantity;
    private TimeStamp $consumptionDate;

    public function __construct(
        Identifier $reservationId,
        Identifier $productId,
        ReservationProductQuantity $quantity,
        TimeStamp $consumptionDate
    ) {
        $this->reservationId   = $reservationId;
        $this->productId       = $productId;
        $this->quantity        = $quantity;
        $this->consumptionDate = $consumptionDate;
    }

    // GETTERS
    public function getReservationId(): Identifier
    {
        return $this->reservationId;
    }

    public function getProductId(): Identifier
    {
        return $this->productId;
    }

    public function getQuantity(): ReservationProductQuantity
    {
        return $this->quantity;
    }

    public function getConsumptionDate(): TimeStamp
    {
        return $this->consumptionDate;
    }

    // SETTERS
    public function setReservationId(Identifier $reservationId): void
    {
        $this->reservationId = $reservationId;
    }

    public function setProductId(Identifier $productId): void
    {
        $this->productId = $productId;
    }

    public function setQuantity(ReservationProductQuantity $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function setConsumptionDate(TimeStamp $date): void
    {
        $this->consumptionDate = $date;
    }

    public function toArray(): array
    {
        return [
            'reservation_product_reservation_id'   => $this->reservationId->getValue(),
            'reservation_product_product_id'       => $this->productId->getValue(),
            'reservation_product_quantity'         => $this->quantity->getValue(),
            'reservation_product_consumption_date' => $this->consumptionDate->getValue(),
        ];
    }
}
