<?php
// Archivo: src/Guests/Infrastructure/Repositories/MySqlGuestsRepository.php

declare(strict_types=1);

namespace Src\Guests\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Guests\Domain\Entities\ReadGuest;
use Src\Guests\Domain\Entities\WriteGuest;
use Src\Guests\Domain\Interfaces\GuestsRepository;
use Src\Guests\Domain\ValueObjects\GuestDocumentType;
use Src\Guests\Domain\ValueObjects\GuestDocumentNumber;
use Src\Guests\Domain\ValueObjects\GuestCountry;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlGuestsRepository implements GuestsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addGuest(WriteGuest $guest): void
    {
        try {
            // 1. Iniciamos una Transacción (Todo o nada)
            $this->pdo->beginTransaction();

            // 2. Primero insertamos/aseguramos el "Tercero" (Persona base)
            // Usamos INSERT IGNORE para que no falle si la persona ya existe
            // Como tu objeto WriteGuest no tiene Nombre/Apellido, ponemos unos genéricos por ahora.
            $sqlTercero = "INSERT IGNORE INTO Terceros (Id, Nombres, Apellidos) 
                           VALUES (:id, 'Nombre Pendiente', 'Apellido Pendiente')";
            
            $stmtT = $this->pdo->prepare($sqlTercero);
            $stmtT->execute([':id' => $guest->getIdPerson()->getValue()]);

            // 3. Ahora sí insertamos el Huésped
            $sqlGuest = "INSERT INTO Huespedes (IdPersona, TipoDocumento, NumeroDocumento, Pais) 
                         VALUES (:id, :docType, :docNum, :country)";
            
            $stmtG = $this->pdo->prepare($sqlGuest);
            $stmtG->execute([
                ':id' => $guest->getIdPerson()->getValue(),
                ':docType' => $guest->getDocumentType()->getValue(),
                ':docNum' => $guest->getDocumentNumber()->getValue(),
                ':country' => $guest->getCountry()->getValue()
            ]);

            // 4. Confirmamos los cambios
            $this->pdo->commit();

        } catch (\Exception $e) {
            // Si algo falla, deshacemos todo
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateGuest(WriteGuest $guest): void
    {
        $sql = "UPDATE Huespedes SET 
                    TipoDocumento = :docType, 
                    NumeroDocumento = :docNum, 
                    Pais = :country
                WHERE IdPersona = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $guest->getIdPerson()->getValue(),
            ':docType' => $guest->getDocumentType()->getValue(),
            ':docNum' => $guest->getDocumentNumber()->getValue(),
            ':country' => $guest->getCountry()->getValue()
        ]);
    }

    public function getGuestById(Identifier $idPerson): ?ReadGuest
    {
        $stmt = $this->pdo->prepare("SELECT IdPersona, TipoDocumento, NumeroDocumento, Pais FROM Huespedes WHERE IdPersona = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        return new ReadGuest(
            new Identifier($row['IdPersona']),
            new GuestDocumentType($row['TipoDocumento']),
            new GuestDocumentNumber($row['NumeroDocumento']),
            new GuestCountry($row['Pais'] ?? '')
        );
    }

    public function getGuests(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdPersona, TipoDocumento, NumeroDocumento, Pais FROM Huespedes");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $guests = [];

        foreach ($rows as $row) {
            $guests[] = new ReadGuest(
                new Identifier($row['IdPersona']),
                new GuestDocumentType($row['TipoDocumento']),
                new GuestDocumentNumber($row['NumeroDocumento']),
                new GuestCountry($row['Pais'] ?? '')
            );
        }

        return $guests;
    }

    public function deleteGuest(Identifier $idPerson): void
    {
        // Al borrar de Terceros, se borra Huespedes automáticamente por el ON DELETE CASCADE del SQL
        $stmt = $this->pdo->prepare("DELETE FROM Terceros WHERE Id = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
    }
}