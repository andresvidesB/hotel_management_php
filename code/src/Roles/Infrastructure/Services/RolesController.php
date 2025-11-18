<?php

declare(strict_types=1);

namespace Src\Roles\Infrastructure\Services;

use Src\Roles\Application\UseCases\AddRole;
use Src\Roles\Application\UseCases\UpdateRole;
use Src\Roles\Application\UseCases\DeleteRole;
use Src\Roles\Application\UseCases\GetRoleById;
use Src\Roles\Application\UseCases\GetRoles;
use Src\Roles\Domain\Entities\ReadRole;
use Src\Roles\Infrastructure\Factories\RoleFactory;
use Src\Roles\Infrastructure\Repositories\MySqlRolesRepository;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class RolesController
{
    public static function addRole(array $role): Identifier
    {
        $roleEntity = RoleFactory::writeRoleFromArray($role);
        $useCase    = new AddRole(self::repo(), self::idCreator());

        return $useCase->execute($roleEntity);
    }

    public static function updateRole(array $role): void
    {
        $roleEntity = RoleFactory::writeRoleFromArray($role);
        $useCase    = new UpdateRole(self::repo());

        $useCase->execute($roleEntity);
    }

    public static function deleteRole(string $id): void
    {
        $useCase = new DeleteRole(self::repo());
        $useCase->execute(new Identifier($id));
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getRoles(): array
    {
        $useCase = new GetRoles(self::repo());

        /** @var ReadRole[] $items */
        $items = $useCase->execute();

        $roles = [];
        foreach ($items as $role) {
            if (!$role instanceof ReadRole) {
                continue;
            }
            $roles[] = $role->toArray();
        }

        return $roles;
    }

    public static function getRoleById(string $id): array
    {
        $useCase = new GetRoleById(self::repo());
        $read    = $useCase->execute(new Identifier($id));

        return $read instanceof ReadRole ? $read->toArray() : [];
    }

    private static function repo(): MySqlRolesRepository
    {
        return new MySqlRolesRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }
}
