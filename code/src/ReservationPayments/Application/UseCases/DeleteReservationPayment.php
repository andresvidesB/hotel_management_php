<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Application\UseCases;

use Src\ReservationPayments\Domain\Interfaces\ReservationPaymentsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteReservationPayment
{
    public function __construct(
        private readonly ReservationPaymentsRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId): void
    {
        $this->repository->deleteReservationPayment($reservationId);
    }
}
