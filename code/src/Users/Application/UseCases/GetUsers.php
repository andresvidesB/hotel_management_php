<?php

declare(strict_types=1);

namespace Src\Users\Application\UseCases;

use Src\Users\Domain\Entities\ReadUser;
use Src\Users\Domain\Interfaces\UsersRepository;

final class GetUsers
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    /**
     * @return ReadUser[]
     * @psalm-return list<ReadUser>
     * @phpstan-return list<ReadUser>
     */
    public function execute(): array
    {
        return $this->usersRepository->getUsers();
    }
}
