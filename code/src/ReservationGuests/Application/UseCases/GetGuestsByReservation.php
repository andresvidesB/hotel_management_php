<?php

declare(strict_types=1);

namespace Src\ReservationGuests\Application\UseCases;

use Src\ReservationGuests\Domain\Interfaces\ReservationGuestsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetGuestsByReservation
{
    public function __construct(
        private readonly ReservationGuestsRepository $repository
    ) {
    }

    public function execute(Identifier $reservationId): array
    {
        return $this->repository->getGuestsByReservation($reservationId);
    }
}
