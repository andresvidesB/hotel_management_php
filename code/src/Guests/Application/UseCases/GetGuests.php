<?php

declare(strict_types=1);

namespace Src\Guests\Application\UseCases;

use Src\Guests\Domain\Entities\ReadGuest;
use Src\Guests\Domain\Interfaces\GuestsRepository;

final class GetGuests
{
    public function __construct(
        private readonly GuestsRepository $guestsRepository
    ) {
    }

    /**
     * @return ReadGuest[]
     */
    public function execute(): array
    {
        return $this->guestsRepository->getGuests();
    }
}
