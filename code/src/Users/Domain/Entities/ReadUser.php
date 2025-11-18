<?php

declare(strict_types=1);

namespace Src\Users\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Users\Domain\ValueObjects\UserPassword;

final class ReadUser
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

    // GETTERS
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

    // SETTERS
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

    public function toArray(): array
    {
        return [
            'user_id_person' => $this->idPerson->getValue(),
            'user_password'  => $this->password->getValue(),
            'user_role_id'   => $this->roleId->getValue(),
        ];
    }
}
