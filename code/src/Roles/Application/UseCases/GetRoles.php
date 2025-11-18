<?php

declare(strict_types=1);

namespace Src\Roles\Application\UseCases;

use Src\Roles\Domain\Entities\ReadRole;
use Src\Roles\Domain\Interfaces\RolesRepository;

final class GetRoles
{
    public function __construct(
        private readonly RolesRepository $rolesRepository
    ) {
    }

    /**
     * @return ReadRole[]
     */
    public function execute(): array
    {
        return $this->rolesRepository->getRoles();
    }
}
