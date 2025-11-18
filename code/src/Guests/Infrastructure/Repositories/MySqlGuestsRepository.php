<?php

declare(strict_types=1);

namespace Src\Guests\Infrastructure\Repositories;

use Src\Guests\Domain\Entities\ReadGuest;
use Src\Guests\Domain\Entities\WriteGuest;
use Src\Guests\Domain\Interfaces\GuestsRepository;
use Src\Guests\Domain\ValueObjects\GuestDocumentType;
use Src\Guests\Domain\ValueObjects\GuestDocumentNumber;
use Src\Guests\Domain\ValueObjects\GuestCountry;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlGuestsRepository implements GuestsRepository
{
    public function addGuest(WriteGuest $guest): void
    {
        // Mock sin persistencia
    }

    public function updateGuest(WriteGuest $guest): void
    {
        // Mock
    }

    public function getGuestById(Identifier $idPerson): ?ReadGuest
    {
        foreach ($this->seedGuests() as $guest) {
            if ($guest->getIdPerson()->getValue() === $idPerson->getValue()) {
                return $guest;
            }
        }
        return null;
    }

    /**
     * @return ReadGuest[]
     */
    public function getGuests(): array
    {
        return $this->seedGuests();
    }

    public function deleteGuest(Identifier $idPerson): void
    {
        // Mock
    }

    private function seedGuests(): array
    {
        return [
            $this->makeReadGuest(
                '11111111-1111-1111-1111-111111111111',
                'DNI',
                '12345678',
                'Colombia'
            ),
            $this->makeReadGuest(
                '22222222-2222-2222-2222-222222222222',
                'PASAPORTE',
                'A9876543',
                'Argentina'
            )
        ];
    }

    private function makeReadGuest(
        string $id,
        string $docType,
        string $docNumber,
        string $country
    ): ReadGuest {
        return new ReadGuest(
            new Identifier($id),
            new GuestDocumentType($docType),
            new GuestDocumentNumber($docNumber),
            new GuestCountry($country)
        );
    }
}
