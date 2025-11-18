<?php

declare(strict_types=1);

namespace Src\Users\Infrastructure\Services;

use Src\Users\Application\UseCases\AddUser;
use Src\Users\Application\UseCases\UpdateUser;
use Src\Users\Application\UseCases\DeleteUser;
use Src\Users\Application\UseCases\GetUserByIdPerson;
use Src\Users\Application\UseCases\GetUsers;
use Src\Users\Domain\Entities\ReadUser;
use Src\Users\Infrastructure\Factories\UserFactory;
use Src\Users\Infrastructure\Repositories\MySqlUsersRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class UsersController
{
    public static function addUser(array $user): void
    {
        $userEntity = UserFactory::writeUserFromArray($user);
        $useCase    = new AddUser(self::repo());
        $useCase->execute($userEntity);
    }

    public static function updateUser(array $user): void
    {
        $userEntity = UserFactory::writeUserFromArray($user);
        $useCase    = new UpdateUser(self::repo());
        $useCase->execute($userEntity);
    }

    public static function deleteUser(string $idPerson): void
    {
        $useCase = new DeleteUser(self::repo());
        $useCase->execute(new Identifier($idPerson));
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getUsers(): array
    {
        $useCase = new GetUsers(self::repo());

        /** @var ReadUser[] $items */
        $items = $useCase->execute();

        $users = [];
        foreach ($items as $user) {
            if (!$user instanceof ReadUser) {
                continue;
            }
            $users[] = $user->toArray();
        }

        return $users;
    }

    /**
     * @return array<string,mixed> Empty array si no existe.
     */
    public static function getUserByIdPerson(string $idPerson): array
    {
        $useCase = new GetUserByIdPerson(self::repo());
        $read    = $useCase->execute(new Identifier($idPerson));

        return $read instanceof ReadUser ? $read->toArray() : [];
    }

    private static function repo(): MySqlUsersRepository
    {
        return new MySqlUsersRepository();
    }
}
