<?php

declare(strict_types=1);

namespace Src\Users\Application\UseCases;

use Src\Users\Domain\Interfaces\UsersRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteUser
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    public function execute(Identifier $idPerson): void
    {
        $this->usersRepository->deleteUser($idPerson);
    }
}
