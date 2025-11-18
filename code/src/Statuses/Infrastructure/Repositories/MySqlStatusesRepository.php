<?php
// Archivo: src/Statuses/Infrastructure/Repositories/MySqlStatusesRepository.php

declare(strict_types=1);

namespace Src\Statuses\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Statuses\Domain\Entities\ReadStatus;
use Src\Statuses\Domain\Entities\WriteStatus;
use Src\Statuses\Domain\Interfaces\StatusesRepository;
use Src\Statuses\Domain\ValueObjects\StatusName;
use Src\Statuses\Domain\ValueObjects\StatusDescription;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlStatusesRepository implements StatusesRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addStatus(WriteStatus $status): Identifier
    {
        $sql = "INSERT INTO Estados (Id, Nombre, Descripcion) VALUES (:id, :name, :description)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'          => $status->getId()->getValue(),
            ':name'        => $status->getName()->getValue(),
            ':description' => $status->getDescription()->getValue()
        ]);
        
        return $status->getId();
    }

    public function updateStatus(WriteStatus $status): void
    {
        $sql = "UPDATE Estados SET Nombre = :name, Descripcion = :description WHERE Id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id'          => $status->getId()->getValue(),
            ':name'        => $status->getName()->getValue(),
            ':description' => $status->getDescription()->getValue()
        ]);
    }

    public function getStatusById(Identifier $id): ?ReadStatus
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre, Descripcion FROM Estados WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new ReadStatus(
            new Identifier($row['Id']),
            new StatusName($row['Nombre']),
            new StatusDescription($row['Descripcion'] ?? '')
        );
    }

    public function getStatuses(): array
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre, Descripcion FROM Estados");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $statuses = [];

        foreach ($rows as $row) {
            $statuses[] = new ReadStatus(
                new Identifier($row['Id']),
                new StatusName($row['Nombre']),
                new StatusDescription($row['Descripcion'] ?? '')
            );
        }

        return $statuses;
    }

    public function deleteStatus(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Estados WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }
}