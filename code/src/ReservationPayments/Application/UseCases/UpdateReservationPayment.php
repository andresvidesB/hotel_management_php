<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Application\UseCases;

use Src\ReservationPayments\Domain\Entities\WriteReservationPayment;
use Src\ReservationPayments\Domain\Interfaces\ReservationPaymentsRepository;

final class UpdateReservationPayment
{
    public function __construct(
        private readonly ReservationPaymentsRepository $repository
    ) {
    }

    public function execute(WriteReservationPayment $payment): void
    {
        $this->repository->updateReservationPayment($payment);
    }
}
