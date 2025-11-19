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
    
    /**
     * NUEVO: Método estático para guardar datos de persona (Nombres, Apellidos, Email)
     */
    public static function savePersonData(string $id, string $nombres, string $apellidos, string $email = null): void
    {
        self::repo()->savePersonData($id, $nombres, $apellidos, $email);
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

    public static function getUsers(): array
    {
        // Devolvemos el array directo del repositorio (compatible con la Vista SQL)
        return self::repo()->getUsers();
    }

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