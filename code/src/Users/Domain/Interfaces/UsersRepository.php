<?php

declare(strict_types=1);

namespace Src\Users\Domain\Interfaces;

use Src\Users\Domain\Entities\ReadUser;
use Src\Users\Domain\Entities\WriteUser;
use Src\Shared\Domain\ValueObjects\Identifier;

interface UsersRepository
{
    public function addUser(WriteUser $user): void;

    public function updateUser(WriteUser $user): void;

    /** @return ReadUser|null */
    public function getUserByIdPerson(Identifier $idPerson): ?ReadUser;

    public function deleteUser(Identifier $idPerson): void;

    /**
     * @return ReadUser[]
     * @psalm-return list<ReadUser>
     * @phpstan-return list<ReadUser>
     */
    public function getUsers(): array;
}
