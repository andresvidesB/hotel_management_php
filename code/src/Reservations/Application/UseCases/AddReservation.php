<?php

declare(strict_types=1);

namespace Src\Reservations\Application\UseCases;

use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Reservations\Domain\Interfaces\ReservationsRepository;
use Src\Shared\Domain\Interfaces\IdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class AddReservation
{
    public function __construct(
        private readonly ReservationsRepository $repository,
        private readonly IdentifierCreator $identifierCreator
    ) {
    }

    public function execute(WriteReservation $reservation): Identifier
    {
        $reservation->setId($this->identifierCreator->createIdentifier());
        return $this->repository->addReservation($reservation);
    }
}
