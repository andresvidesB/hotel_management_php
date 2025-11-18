<?php
// Archivo: src/Roles/Infrastructure/Factories/RoleFactory.php

declare(strict_types=1);

namespace Src\Roles\Infrastructure\Factories;

use Src\Roles\Domain\Entities\WriteRole;
// Importamos los Value Objects necesarios
use Src\Roles\Domain\ValueObjects\RoleName;
use Src\Shared\Domain\ValueObjects\Identifier;

final class RoleFactory
{
    public static function writeRoleFromArray(array $data): WriteRole
    {
        return new WriteRole(
            // Usamos '??' por si el ID viene vacío (al crear nuevo)
            new Identifier($data['role_id'] ?? ''),
            new RoleName($data['role_name'])
        );
    }
}