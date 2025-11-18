<?php

declare(strict_types=1);

namespace Src\ReservationPayments\Application\UseCases;

use Src\ReservationPayments\Domain\Entities\ReadReservationPayment;
use Src\ReservationPayments\Domain\Interfaces\ReservationPaymentsRepository;

final class GetReservationPayments
{
    public function __construct(
        private readonly ReservationPaymentsRepository $repository
    ) {
    }

    /**
     * @return ReadReservationPayment[]
     * @psalm-return list<ReadReservationPayment>
     */
    public function execute(): array
    {
        return $this->repository->getReservationPayments();
    }
}
