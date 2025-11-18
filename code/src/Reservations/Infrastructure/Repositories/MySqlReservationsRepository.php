<?php
// Archivo: src/Reservations/Infrastructure/Repositories/MySqlReservationsRepository.php

declare(strict_types=1);

namespace Src\Reservations\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Reservations\Domain\Entities\ReadReservation;
use Src\Reservations\Domain\Entities\WriteReservation;
use Src\Reservations\Domain\Interfaces\ReservationsRepository;
use Src\Reservations\Domain\ValueObjects\ReservationSource;
use Src\Shared\Domain\ValueObjects\TimeStamp; // Usamos TimeStamp genérico
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlReservationsRepository implements ReservationsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addReservation(WriteReservation $reservation): Identifier
    {
        $sql = "INSERT INTO Reserva (Id, FuenteReserva, IdUsuario, FechaCreacion) 
                VALUES (:id, :source, :userId, :createdAt)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // CONVERSIÓN: TimeStamp (int) -> MySQL DATETIME (string)
        $fechaMySQL = date('Y-m-d H:i:s', $reservation->getCreatedAt()->getValue());

        $stmt->execute([
            ':id' => $reservation->getId()->getValue(),
            ':source' => $reservation->getSource()->getValue(),
            ':userId' => $reservation->getUserId()->getValue(),
            ':createdAt' => $fechaMySQL
        ]);
        
        return $reservation->getId();
    }

    public function updateReservation(WriteReservation $reservation): void
    {
        $sql = "UPDATE Reserva SET 
                    FuenteReserva = :source, 
                    FechaCreacion = :createdAt 
                WHERE Id = :id";
        
        $fechaMySQL = date('Y-m-d H:i:s', $reservation->getCreatedAt()->getValue());

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $reservation->getId()->getValue(),
            ':source' => $reservation->getSource()->getValue(),
            ':createdAt' => $fechaMySQL
        ]);
    }

    public function getReservationById(Identifier $id): ?ReadReservation
    {
        $stmt = $this->pdo->prepare("SELECT Id, FuenteReserva, IdUsuario, FechaCreacion FROM Reserva WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null;
        }

        // CONVERSIÓN: MySQL DATETIME (string) -> TimeStamp (int)
        $timestamp = strtotime($row['FechaCreacion']);

        return new ReadReservation(
            new Identifier($row['Id']),
            new ReservationSource($row['FuenteReserva'] ?? 'Desconocido'),
            new Identifier($row['IdUsuario']),
            new TimeStamp($timestamp)
        );
    }

    public function getReservations(): array
    {
        $stmt = $this->pdo->prepare("SELECT Id, FuenteReserva, IdUsuario, FechaCreacion FROM Reserva ORDER BY FechaCreacion DESC");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $reservations = [];

        foreach ($rows as $row) {
            $timestamp = strtotime($row['FechaCreacion']);
            
            $reservations[] = new ReadReservation(
                new Identifier($row['Id']),
                new ReservationSource($row['FuenteReserva'] ?? 'Desconocido'),
                new Identifier($row['IdUsuario']),
                new TimeStamp($timestamp)
            );
        }

        return $reservations;
    }

    public function deleteReservation(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Reserva WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }

    public function getReservaCompleta(Identifier $id): array
    {
        // ¡Mira qué limpia queda la consulta en PHP!
        $sql = "SELECT * FROM Vista_Reserva_Completa WHERE ReservaID = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id->getValue()]);
        
        $result = $stmt->fetch();
        
        // Si no encuentra nada, retornamos array vacío
        return $result ?: [];
    }
}