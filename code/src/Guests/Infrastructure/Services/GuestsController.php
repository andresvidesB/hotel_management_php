<?php

declare(strict_types=1);

namespace Src\Guests\Infrastructure\Services;

use Src\Guests\Application\UseCases\AddGuest;
use Src\Guests\Application\UseCases\UpdateGuest;
use Src\Guests\Application\UseCases\DeleteGuest;
use Src\Guests\Application\UseCases\GetGuestById;
use Src\Guests\Application\UseCases\GetGuests;
use Src\Guests\Infrastructure\Factories\GuestFactory;
use Src\Guests\Domain\Entities\ReadGuest;
use Src\Guests\Infrastructure\Repositories\MySqlGuestsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GuestsController
{
    public static function addGuest(array $guest): void
    {
        $guestEntity = GuestFactory::writeGuestFromArray($guest);
        $useCase = new AddGuest(self::repo());
        $useCase->execute($guestEntity);
    }

    public static function updateGuest(array $guest): void
    {
        $guestEntity = GuestFactory::writeGuestFromArray($guest);
        $useCase = new UpdateGuest(self::repo());
        $useCase->execute($guestEntity);
    }

    public static function deleteGuest(string $idPerson): void
    {
        $useCase = new DeleteGuest(self::repo());
        $useCase->execute(new Identifier($idPerson));
    }

    public static function getGuests(): array
    {
        $useCase = new GetGuests(self::repo());
        $items = $useCase->execute();

        $result = [];
        foreach ($items as $guest) {
            if ($guest instanceof ReadGuest) {
                $result[] = $guest->toArray();
            }
        }

        return $result;
    }

    public static function getGuestById(string $idPerson): array
    {
        $useCase = new GetGuestById(self::repo());
        $guest = $useCase->execute(new Identifier($idPerson));

        return $guest instanceof ReadGuest ? $guest->toArray() : [];
    }

    private static function repo(): MySqlGuestsRepository
    {
        return new MySqlGuestsRepository();
    }
}
