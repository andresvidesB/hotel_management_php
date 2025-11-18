<?php

declare(strict_types=1);

namespace Src\Roles\Application\UseCases;

use Src\Roles\Domain\Entities\WriteRole;
use Src\Roles\Domain\Interfaces\RolesRepository;
use Src\Shared\Domain\Interfaces\IdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class AddRole
{
    public function __construct(
        private readonly RolesRepository $rolesRepository,
        private readonly IdentifierCreator $identifierCreator
    ) {
    }

    public function execute(WriteRole $role): Identifier
    {
        $role->setId($this->identifierCreator->createIdentifier());
        return $this->rolesRepository->addRole($role);
    }
}
