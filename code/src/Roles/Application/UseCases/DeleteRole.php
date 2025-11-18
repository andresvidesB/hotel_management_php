<?php

declare(strict_types=1);

namespace Src\Roles\Application\UseCases;

use Src\Roles\Domain\Interfaces\RolesRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteRole
{
    public function __construct(
        private readonly RolesRepository $rolesRepository
    ) {
    }

    public function execute(Identifier $id): void
    {
        $this->rolesRepository->deleteRole($id);
    }
}
