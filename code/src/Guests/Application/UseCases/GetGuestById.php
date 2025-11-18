<?php

declare(strict_types=1);

namespace Src\Guests\Application\UseCases;

use Src\Guests\Domain\Entities\ReadGuest;
use Src\Guests\Domain\Interfaces\GuestsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetGuestById
{
    public function __construct(
        private readonly GuestsRepository $guestsRepository
    ) {
    }

    public function execute(Identifier $idPerson): ?ReadGuest
    {
        return $this->guestsRepository->getGuestById($idPerson);
    }
}
