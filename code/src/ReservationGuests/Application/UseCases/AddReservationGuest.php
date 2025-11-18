<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Application\UseCases;

use Src\ReservationGuests\Domain\Entities\WriteReservationGuest;
use Src\ReservationGuests\Domain\Interfaces\ReservationGuestsRepository;

final class AddReservationGuest
{
    public function __construct(
        private readonly ReservationGuestsRepository $repository
    ) {
    }

    public function execute(WriteReservationGuest $relation): void
    {
        $this->repository->addReservationGuest($relation);
    }
}
