<?php
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

            // 1. Insertamos un Tercero base si no existe
            $sqlTercero = "INSERT IGNORE INTO Terceros (Id, Nombres, Apellidos) 
                           VALUES (:id, 'Usuario', 'Nuevo')";
            
            $stmtT = $this->pdo->prepare($sqlTercero);
            $stmtT->execute([':id' => $user->getIdPerson()->getValue()]);

            // 2. Insertar el Usuario
            $sqlUser = "INSERT INTO Usuario (IdPersona, Contrasena, IdRol) 
                        VALUES (:id, :password, :roleId)";
            
            $stmtU = $this->pdo->prepare($sqlUser);
            $stmtU->execute([
                ':id' => $user->getIdPerson()->getValue(),
                ':password' => $user->getPassword()->getValue(),
                ':roleId' => $user->getRoleId()->getValue()
            ]);

            $this->pdo->commit();

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * NUEVO MÉTODO: Guarda los datos personales reales en la tabla Terceros
     */
    public function savePersonData(string $id, string $nombres, string $apellidos, ?string $email): void
    {
        $sql = "UPDATE Terceros SET 
                    Nombres = :nom, 
                    Apellidos = :ape, 
                    CorreoElectronico = :email 
                WHERE Id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':email' => $email
        ]);
    }

    // ... (Mantener el resto de métodos: updateUser, getUserByIdPerson, getUsers, deleteUser igual que antes) ...
    
    public function updateUser(WriteUser $user): void
    {
        $sql = "UPDATE Usuario SET Contrasena = :password, IdRol = :roleId WHERE IdPersona = :id";
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
        if (!$row) return null;
        return new ReadUser(new Identifier($row['IdPersona']), new UserPassword($row['Contrasena']), new Identifier($row['IdRol']));
    }

    public function getUsers(): array
    {
        // Usamos la Vista si existe, sino la tabla
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Vista_Usuarios_Info");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            // Fallback si la vista no existe
            $stmt = $this->pdo->prepare("SELECT * FROM Usuario");
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }

    public function deleteUser(Identifier $idPerson): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Usuario WHERE IdPersona = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
    }
}