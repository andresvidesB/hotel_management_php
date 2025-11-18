<?php

declare(strict_types=1);

namespace Src\Guests\Domain\Interfaces;

use Src\Guests\Domain\Entities\ReadGuest;
use Src\Guests\Domain\Entities\WriteGuest;
use Src\Shared\Domain\ValueObjects\Identifier;

interface GuestsRepository
{
    public function addGuest(WriteGuest $guest): void;

    public function updateGuest(WriteGuest $guest): void;

    /** @return ReadGuest|null */
    public function getGuestById(Identifier $idPerson): ?ReadGuest;

    public function deleteGuest(Identifier $idPerson): void;

    /**
     * @return ReadGuest[]
     * @phpstan-return list<ReadGuest>
     */
    public function getGuests(): array;
}
