<?php

declare(strict_types=1);

namespace Src\Roles\Infrastructure\Factories;

use Src\Roles\Domain\Entities\WriteRole;
use Src\Roles\Domain\ValueObjects\RoleName;
use Src\Shared\Domain\ValueObjects\Identifier;

final class RoleFactory
{
    public static function writeRoleFromArray(array $data): WriteRole
    {
        return new WriteRole(
            new Identifier($data['role_id'] ?? ''),
            new RoleName($data['role_name'])
        );
    }
}
