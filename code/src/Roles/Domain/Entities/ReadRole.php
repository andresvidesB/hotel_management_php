<?php

declare(strict_types=1);

namespace Src\Roles\Domain\Entities;

use Src\Roles\Domain\ValueObjects\RoleName;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReadRole
{
    private Identifier $id;
    private RoleName $name;

    public function __construct(
        Identifier $id,
        RoleName $name
    ) {
        $this->id   = $id;
        $this->name = $name;
    }

    // GETTERS
    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): RoleName
    {
        return $this->name;
    }

    // SETTERS
    public function setId(Identifier $id): void
    {
        $this->id = $id;
    }

    public function setName(RoleName $name): void
    {
        $this->name = $name;
    }

    public function toArray(): array
    {
        return [
            'role_id'   => $this->id->getValue(),
            'role_name' => $this->name->getValue(),
        ];
    }
}
