<?php

declare(strict_types=1);

namespace Src\Roles\Infrastructure\Repositories;

use Src\Roles\Domain\Entities\ReadRole;
use Src\Roles\Domain\Entities\WriteRole;
use Src\Roles\Domain\Interfaces\RolesRepository;
use Src\Roles\Domain\ValueObjects\RoleName;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlRolesRepository implements RolesRepository
{
    public function addRole(WriteRole $role): Identifier
    {
        return new Identifier('00000000-0000-0000-0000-000000000301');
    }

    public function updateRole(WriteRole $role): void
    {
        // Mock: sin persistencia
    }

    public function getRoleById(Identifier $id): ?ReadRole
    {
        foreach ($this->seedRoles() as $role) {
            if ($role->getId()->getValue() === $id->getValue()) {
                return $role;
            }
        }
        return null;
    }

    /**
     * @return ReadRole[]
     */
    public function getRoles(): array
    {
        return $this->seedRoles();
    }

    public function deleteRole(Identifier $id): void
    {
        // Mock: sin persistencia
    }

    private function seedRoles(): array
    {
        return [
            $this->makeReadRole('1', 'Administrador'),
            $this->makeReadRole('2', 'Recepcionista'),
            $this->makeReadRole('3', 'Cliente'),
            $this->makeReadRole('4', 'Operador'),
        ];
    }

    private function makeReadRole(string $id, string $name): ReadRole
    {
        return new ReadRole(
            new Identifier($id),
            new RoleName($name)
        );
    }
}
