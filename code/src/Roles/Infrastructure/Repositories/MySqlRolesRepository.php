<?php
// Archivo: src/Roles/Infrastructure/Repositories/MySqlRolesRepository.php

declare(strict_types=1);

namespace Src\Roles\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Roles\Domain\Entities\ReadRole;
use Src\Roles\Domain\Entities\WriteRole;
use Src\Roles\Domain\Interfaces\RolesRepository;
use Src\Roles\Domain\ValueObjects\RoleName;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlRolesRepository implements RolesRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addRole(WriteRole $role): Identifier
    {
        $sql = "INSERT INTO Roles (Id, Nombre) VALUES (:id, :nombre)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $role->getId()->getValue(),
            ':nombre' => $role->getName()->getValue()
        ]);
        
        return $role->getId();
    }

    public function updateRole(WriteRole $role): void
    {
        $sql = "UPDATE Roles SET Nombre = :nombre WHERE Id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $role->getId()->getValue(),
            ':nombre' => $role->getName()->getValue()
        ]);
    }

    public function getRoleById(Identifier $id): ?ReadRole
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre FROM Roles WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new ReadRole(
            new Identifier($row['Id']),
            new RoleName($row['Nombre'])
        );
    }

    public function getRoles(): array
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre FROM Roles");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $roles = [];

        foreach ($rows as $row) {
            $roles[] = new ReadRole(
                new Identifier($row['Id']),
                new RoleName($row['Nombre'])
            );
        }

        return $roles;
    }

    public function deleteRole(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Roles WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }
}