<?php

declare(strict_types=1);

namespace Src\Roles\Domain\Interfaces;

use Src\Roles\Domain\Entities\ReadRole;
use Src\Roles\Domain\Entities\WriteRole;
use Src\Shared\Domain\ValueObjects\Identifier;

interface RolesRepository
{
    public function addRole(WriteRole $role): Identifier;

    public function updateRole(WriteRole $role): void;

    /** @return ReadRole|null */
    public function getRoleById(Identifier $id): ?ReadRole;

    public function deleteRole(Identifier $id): void;

    /**
     * @return ReadRole[]
     * @psalm-return list<ReadRole>
     * @phpstan-return list<ReadRole>
     */
    public function getRoles(): array;
}
