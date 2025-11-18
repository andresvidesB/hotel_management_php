<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Application\UseCases;

use Src\ReservationStatus\Domain\Entities\WriteReservationStatus;
use Src\ReservationStatus\Domain\Interfaces\ReservationStatusRepository;

final class UpdateReservationStatus
{
    public function __construct(
        private readonly ReservationStatusRepository $repository
    ) {
    }

    public function execute(WriteReservationStatus $data): void
    {
        $this->repository->updateReservationStatus($data);
    }
}
