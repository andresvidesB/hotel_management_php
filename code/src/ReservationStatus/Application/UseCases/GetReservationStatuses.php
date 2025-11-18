<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Application\UseCases;

use Src\ReservationStatus\Domain\Interfaces\ReservationStatusRepository;

final class GetReservationStatuses
{
    public function __construct(
        private readonly ReservationStatusRepository $repository
    ) {
    }

    public function execute(): array
    {
        return $this->repository->getReservationStatuses();
    }
}
