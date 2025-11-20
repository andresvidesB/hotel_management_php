<?php
declare(strict_types=1);

namespace Src\ReservationStatus\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\ReservationStatus\Domain\Entities\ReadReservationStatus;
use Src\ReservationStatus\Domain\Entities\WriteReservationStatus;
use Src\ReservationStatus\Domain\Interfaces\ReservationStatusRepository;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationStatusRepository implements ReservationStatusRepository
{
    // 1. PROPIEDAD CRÍTICA PARA LA CONEXIÓN
    private PDO $pdo;

    // 2. CONSTRUCTOR QUE INICIA LA CONEXIÓN
    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addReservationStatus(WriteReservationStatus $relation): void
    {
        $sql = "INSERT INTO Reserva_Estado (IdReserva, IdEstado, FechaCambio) 
                VALUES (:resId, :statusId, :changedAt)";
        
        $stmt = $this->pdo->prepare($sql);
        
        $date = date('Y-m-d H:i:s', $relation->getChangedAt()->getValue());

        $stmt->execute([
            ':resId'    => $relation->getReservationId()->getValue(),
            ':statusId' => $relation->getStatusId()->getValue(),
            ':changedAt'=> $date
        ]);
    }

    public function updateReservationStatus(WriteReservationStatus $relation): void
    {
        $sql = "UPDATE Reserva_Estado SET 
                    FechaCambio = :changedAt
                WHERE IdReserva = :resId AND IdEstado = :statusId";

        $stmt = $this->pdo->prepare($sql);
        $date = date('Y-m-d H:i:s', $relation->getChangedAt()->getValue());

        $stmt->execute([
            ':resId'    => $relation->getReservationId()->getValue(),
            ':statusId' => $relation->getStatusId()->getValue(),
            ':changedAt'=> $date
        ]);
    }

    public function deleteReservationStatus(Identifier $reservationId, Identifier $statusId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Reserva_Estado WHERE IdReserva = :resId AND IdEstado = :statusId");
        $stmt->execute([
            ':resId'    => $reservationId->getValue(),
            ':statusId' => $statusId->getValue()
        ]);
    }

    public function getReservationStatuses(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdEstado, FechaCambio FROM Reserva_Estado");
        $stmt->execute();
        return $this->mapRows($stmt->fetchAll());
    }

    public function getStatusesByReservation(Identifier $reservationId): array
    {
        // Incluye el ORDER BY para asegurar que el estado actual sea el último
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdEstado, FechaCambio 
                                     FROM Reserva_Estado 
                                     WHERE IdReserva = :id 
                                     ORDER BY FechaCambio ASC");
        $stmt->execute([':id' => $reservationId->getValue()]);
        return $this->mapRows($stmt->fetchAll());
    }

    private function mapRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $dateInt = strtotime($row['FechaCambio']);
            $result[] = new ReadReservationStatus(
                new Identifier($row['IdReserva']),
                new Identifier($row['IdEstado']),
                new TimeStamp((int)$dateInt)
            );
        }
        return $result;
    }
}