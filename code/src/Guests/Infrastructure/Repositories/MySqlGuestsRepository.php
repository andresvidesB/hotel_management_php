<?php
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
            $this->pdo->beginTransaction();

            // 1. Asegurar que existe el registro en TERCEROS (Aquí es donde vive el Nombre)
            // Usamos 'INSERT IGNORE' para no borrar si ya existe un usuario con ese ID
            $sqlTercero = "INSERT IGNORE INTO Terceros (Id, Nombres, Apellidos) 
                           VALUES (:id, 'Huesped', 'Nuevo')";
            $stmtT = $this->pdo->prepare($sqlTercero);
            $stmtT->execute([':id' => $guest->getIdPerson()->getValue()]);

            // 2. Insertar solo los datos de alojamiento en HUESPEDES
            $sqlGuest = "INSERT INTO Huespedes (IdPersona, TipoDocumento, NumeroDocumento, Pais) 
                         VALUES (:id, :docType, :docNum, :country)";
            $stmtG = $this->pdo->prepare($sqlGuest);
            $stmtG->execute([
                ':id' => $guest->getIdPerson()->getValue(),
                ':docType' => $guest->getDocumentType()->getValue(),
                ':docNum' => $guest->getDocumentNumber()->getValue(),
                ':country' => $guest->getCountry()->getValue()
            ]);

            $this->pdo->commit();
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function updateGuest(WriteGuest $guest): void
    {
        // Aquí solo actualizamos datos de alojamiento (Documento, País)
        // Los nombres se actualizan vía UsersController::savePersonData en la tabla Terceros
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
        $stmt = $this->pdo->prepare("SELECT * FROM Huespedes WHERE IdPersona = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
        $row = $stmt->fetch();

        if (!$row) return null;

        return new ReadGuest(
            new Identifier($row['IdPersona']),
            new GuestDocumentType($row['TipoDocumento']),
            new GuestDocumentNumber($row['NumeroDocumento']),
            new GuestCountry($row['Pais'] ?? '')
        );
    }

    /**
     * AQUÍ ESTÁ LA MAGIA: JOIN para extraer el nombre desde TERCEROS
     */
    public function getGuests(): array
    {
        // Seleccionamos datos de Huespedes y los unimos con Terceros para sacar el nombre
        $sql = "SELECT 
                    h.IdPersona, 
                    h.TipoDocumento, 
                    h.NumeroDocumento, 
                    h.Pais,
                    t.Nombres, 
                    t.Apellidos, 
                    t.CorreoElectronico,
                    t.Telefono
                FROM Huespedes h
                LEFT JOIN Terceros t ON h.IdPersona = t.Id
                ORDER BY t.Nombres ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $rawRows = $stmt->fetchAll();

        $guests = [];
        foreach ($rawRows as $row) {
            // Construimos el nombre completo usando los datos de la tabla Terceros
            $nombre = $row['Nombres'] ?? '';
            $apellido = $row['Apellidos'] ?? '';
            $nombreCompleto = trim("$nombre $apellido");
            
            if (empty($nombreCompleto)) {
                $nombreCompleto = 'Huésped ' . substr($row['IdPersona'], 0, 6);
            }

            $guests[] = [
                'guest_id_person'       => $row['IdPersona'],
                'guest_document_type'   => $row['TipoDocumento'],
                'guest_document_number' => $row['NumeroDocumento'],
                'guest_country'         => $row['Pais'] ?? '',
                
                // Datos extraídos de Terceros
                'NombreCompleto'    => $nombreCompleto,
                'Nombres'           => $nombre,
                'Apellidos'         => $apellido,
                'CorreoElectronico' => $row['CorreoElectronico'] ?? 'S/E',
                'Telefono'          => $row['Telefono'] ?? ''
            ];
        }

        return $guests;
    }

    public function deleteGuest(Identifier $idPerson): void
    {
        // Al borrar el Tercero, se borra el Huésped en cascada
        $stmt = $this->pdo->prepare("DELETE FROM Terceros WHERE Id = :id");
        $stmt->execute([':id' => $idPerson->getValue()]);
    }
}