<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Infrastructure\Factories;

use Src\ReservationPayments\Domain\Entities\WriteReservationPayment;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class ReservationPaymentFactory
{
    public static function writeReservationPaymentFromArray(array $data): WriteReservationPayment
    {
        return new WriteReservationPayment(
            new Identifier($data['reservation_payment_reservation_id']),
            new Price((float) $data['reservation_payment_amount']),
            new TimeStamp($data['reservation_payment_date'])
        );
    }
}
