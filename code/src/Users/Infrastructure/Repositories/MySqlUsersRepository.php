<?php
// Archivo: src/Users/Infrastructure/Repositories/MySqlUsersRepository.php

declare(strict_types=1);

namespace Src\Users\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Users\Domain\Entities\ReadUser;
use Src\Users\Domain\Entities\WriteUser;
use Src\Users\Domain\Interfaces\UsersRepository;
use Src\Users\Domain\ValueObjects\UserPassword;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlUsersRepository implements UsersRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addUser(WriteUser $user): void
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Asegurar que existe el Tercero (Persona)
            // Usamos nombres genéricos porque WriteUser no tiene esos datos
            $sqlTercero = "INSERT IGNORE INTO Terceros (Id, Nombres, Apellidos) 
                           VALUES (:id, 'Usuario Sistema', 'Pendiente')";
            
            $stmtT = $this->pdo->prepare($sqlTercero);
            $stmtT->execute([':id' => $user->getIdPerson()->getValue()]);

            // 2. Insertar el Usuario
            $sqlUser = "INSERT INTO Usuario (IdPersona, Contrasena, IdRol) 
                        VALUES (:id, :password, :roleId)";
            
            $stmtU = $this->pdo->prepare($sqlUser);
            $stmtU->execute([
                ':id' => $user->getIdPerson()->getValue(),
                ':password' => $user->getPassword()->getValue(), // En producción, aquí usarías password_hash()
                ':roleId' => $user->getRoleId()->getValue()
            ]);

            $this->pdo->commit();

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateUser(WriteUser $user): void
    {
        // Solo permitimos cambiar contraseña y rol, el ID es inmutable
        $sql = "UPDATE Usuario SET 
                    Contrasena = :password, 
                    IdRol = :roleId 
                WHERE IdPersona = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $user->getIdPerson()->getValue(),
            ':password' => $user->getPassword()->getValue(),
            ':roleId' => $user->getRoleId()->getValue()
        ]);
    }

    public function getUserByIdPerson(Identifier $idPerson): ?ReadUser
    {
        $stmt = $this->pdo->prepare("SELECT IdPersona, Contrasena, IdRol FROM Usuario WHERE IdPersona = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new ReadUser(
            new Identifier($row['IdPersona']),
            new UserPassword($row['Contrasena']),
            new Identifier($row['IdRol'])
        );
    }

    public function getUsers(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdPersona, Contrasena, IdRol FROM Usuario");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $users = [];

        foreach ($rows as $row) {
            $users[] = new ReadUser(
                new Identifier($row['IdPersona']),
                new UserPassword($row['Contrasena']),
                new Identifier($row['IdRol'])
            );
        }

        return $users;
    }

    public function deleteUser(Identifier $idPerson): void
    {
        // Borramos el Usuario. Dependiendo de tu lógica de negocio, 
        // podrías querer borrar también el Tercero o mantenerlo.
        // Aquí borramos solo el registro de Usuario para no eliminar historial de la persona.
        $stmt = $this->pdo->prepare("DELETE FROM Usuario WHERE IdPersona = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
    }
}