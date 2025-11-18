<?php

declare(strict_types=1);

namespace Src\Users\Infrastructure\Repositories;

use Src\Users\Domain\Entities\ReadUser;
use Src\Users\Domain\Entities\WriteUser;
use Src\Users\Domain\Interfaces\UsersRepository;
use Src\Users\Domain\ValueObjects\UserPassword;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlUsersRepository implements UsersRepository
{
    public function addUser(WriteUser $user): void
    {
        // Mock: sin persistencia
    }

    public function updateUser(WriteUser $user): void
    {
        // Mock
    }

    public function getUserByIdPerson(Identifier $idPerson): ?ReadUser
    {
        foreach ($this->seedUsers() as $user) {
            if ($user->getIdPerson()->getValue() === $idPerson->getValue()) {
                return $user;
            }
        }

        return null;
    }

    /**
     * @return ReadUser[]
     * @psalm-return list<ReadUser>
     * @phpstan-return list<ReadUser>
     */
    public function getUsers(): array
    {
        return $this->seedUsers();
    }

    public function deleteUser(Identifier $idPerson): void
    {
        // Mock
    }

    /**
     * Dataset de prueba consistente.
     *
     * @return list<ReadUser>
     */
    private function seedUsers(): array
    {
        return [
            $this->makeReadUser(
                '11111111-1111-1111-1111-111111111111', // IdPersona (FK Terceros.Id)
                'hash_password_admin',                 // Contraseña cifrada
                '1'                                    // IdRol (FK Roles.Id)
            ),
            $this->makeReadUser(
                '22222222-2222-2222-2222-222222222222',
                'hash_password_client',
                '3'
            ),
        ];
    }

    private function makeReadUser(
        string $idPerson,
        string $password,
        string $roleId
    ): ReadUser {
        return new ReadUser(
            new Identifier($idPerson),
            new UserPassword($password),
            new Identifier($roleId)
        );
    }
}
