<?php

declare(strict_types=1);

namespace Src\Guests\Application\UseCases;

use Src\Guests\Domain\Entities\WriteGuest;
use Src\Guests\Domain\Interfaces\GuestsRepository;

final class AddGuest
{
    public function __construct(
        private readonly GuestsRepository $guestsRepository
    ) {
    }

    public function execute(WriteGuest $guest): void
    {
        $this->guestsRepository->addGuest($guest);
    }
}
