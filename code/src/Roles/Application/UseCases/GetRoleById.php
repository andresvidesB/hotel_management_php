<?php

declare(strict_types=1);

namespace Src\Roles\Application\UseCases;

use Src\Roles\Domain\Entities\ReadRole;
use Src\Roles\Domain\Interfaces\RolesRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetRoleById
{
    public function __construct(
        private readonly RolesRepository $rolesRepository
    ) {
    }

    public function execute(Identifier $id): ?ReadRole
    {
        return $this->rolesRepository->getRoleById($id);
    }
}
