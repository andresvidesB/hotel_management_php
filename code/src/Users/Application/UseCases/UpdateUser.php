<?php

declare(strict_types=1);

namespace Src\Users\Application\UseCases;

use Src\Users\Domain\Entities\WriteUser;
use Src\Users\Domain\Interfaces\UsersRepository;

final class UpdateUser
{
    public function __construct(
        private readonly UsersRepository $usersRepository
    ) {
    }

    public function execute(WriteUser $user): void
    {
        $this->usersRepository->updateUser($user);
    }
}
