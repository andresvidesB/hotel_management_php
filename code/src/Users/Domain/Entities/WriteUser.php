<?php

declare(strict_types=1);

namespace Src\Users\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Users\Domain\ValueObjects\UserPassword;

final class WriteUser
{
    private Identifier $idPerson;
    private UserPassword $password;
    private Identifier $roleId;

    public function __construct(
        Identifier $idPerson,
        UserPassword $password,
        Identifier $roleId
    ) {
        $this->idPerson = $idPerson;
        $this->password = $password;
        $this->roleId   = $roleId;
    }

    public function getIdPerson(): Identifier
    {
        return $this->idPerson;
    }

    public function getPassword(): UserPassword
    {
        return $this->password;
    }

    public function getRoleId(): Identifier
    {
        return $this->roleId;
    }

    public function setIdPerson(Identifier $idPerson): void
    {
        $this->idPerson = $idPerson;
    }

    public function setPassword(UserPassword $password): void
    {
        $this->password = $password;
    }

    public function setRoleId(Identifier $roleId): void
    {
        $this->roleId = $roleId;
    }
}
