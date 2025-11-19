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

            $sqlTercero = "INSERT IGNORE INTO Terceros (Id, Nombres, Apellidos) 
                           VALUES (:id, 'Usuario', 'Nuevo')";
            $stmtT = $this->pdo->prepare($sqlTercero);
            $stmtT->execute([':id' => $user->getIdPerson()->getValue()]);

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

    public function savePersonData(string $id, string $nombres, string $apellidos, ?string $email): void
    {
        $sql = "UPDATE Terceros SET Nombres = :nom, Apellidos = :ape, CorreoElectronico = :email WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id, ':nom' => $nombres, ':ape' => $apellidos, ':email' => $email]);
    }

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

        return new ReadUser(
            new Identifier($row['IdPersona']),
            new UserPassword($row['Contrasena']),
            new Identifier($row['IdRol'])
        );
    }

    /**
     * MÉTODO CORREGIDO: Normaliza las claves del array
     */
    public function getUsers(): array
    {
        $rawRows = [];
        
        // 1. Intentamos leer de la Vista (que tiene nombres, etc.)
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM Vista_Usuarios_Info");
            $stmt->execute();
            $rawRows = $stmt->fetchAll();
        } catch (\Exception $e) {
            // Fallback: Si la vista falla, leemos de la tabla base
            $stmt = $this->pdo->prepare("SELECT * FROM Usuario");
            $stmt->execute();
            $rawRows = $stmt->fetchAll();
        }

        // 2. "Traducimos" los resultados al formato que espera el Frontend
        $users = [];
        foreach ($rawRows as $row) {
            // Detectamos el ID (puede venir como 'user_id' de la vista o 'IdPersona' de la tabla)
            $id = $row['user_id'] ?? $row['IdPersona'] ?? '';
            
            // Detectamos el Rol (puede venir como 'IdRol')
            $rol = $row['IdRol'] ?? '';

            $users[] = [
                // Estas son las claves que 'usuarios.php' espera:
                'user_id_person' => $id,
                'user_role_id'   => $rol,
                // Extras para 'reservas.php':
                'NombreCompleto' => $row['NombreCompleto'] ?? ('Usuario ' . substr($id, 0, 5)),
                // Contraseña (dummy para la lista, no se muestra)
                'user_password'  => $row['Contrasena'] ?? ''
            ];
        }

        return $users;
    }

    public function deleteUser(Identifier $idPerson): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Usuario WHERE IdPersona = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
    }
}