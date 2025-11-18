<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Application\UseCases;

use Src\ReservationGuests\Domain\Interfaces\ReservationGuestsRepository;
use Src\ReservationGuests\Domain\Entities\ReadReservationGuest;

final class GetReservationGuests
{
    public function __construct(
        private readonly ReservationGuestsRepository $repository
    ) {
    }

    /**
     * @return ReadReservationGuest[]
     */
    public function execute(): array
    {
        return $this->repository->getReservationGuests();
    }
}
