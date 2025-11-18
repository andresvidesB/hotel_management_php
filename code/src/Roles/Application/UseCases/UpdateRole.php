<?php

declare(strict_types=1);

namespace Src\Roles\Application\UseCases;

use Src\Roles\Domain\Entities\WriteRole;
use Src\Roles\Domain\Interfaces\RolesRepository;

final class UpdateRole
{
    public function __construct(
        private readonly RolesRepository $rolesRepository
    ) {
    }

    public function execute(WriteRole $role): void
    {
        $this->rolesRepository->updateRole($role);
    }
}
