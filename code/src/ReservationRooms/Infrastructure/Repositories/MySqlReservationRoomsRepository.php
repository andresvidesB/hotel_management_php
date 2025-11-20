<?php
declare(strict_types=1);

namespace Src\ReservationRooms\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\ReservationRooms\Domain\Entities\ReadReservationRoom;
use Src\ReservationRooms\Domain\Entities\WriteReservationRoom;
use Src\ReservationRooms\Domain\Interfaces\ReservationRoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationRoomsRepository implements ReservationRoomsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addReservationRoom(WriteReservationRoom $reservationRoom): void
    {
        $sql = "INSERT INTO Reserva_Habitacion (IdReserva, IdHabitacion, FechaInicio, FechaFin) 
                VALUES (:reservationId, :roomId, :startDate, :endDate)";
        
        $stmt = $this->pdo->prepare($sql);
        
        $start = date('Y-m-d', $reservationRoom->getStartDate()->getValue());
        $end   = date('Y-m-d', $reservationRoom->getEndDate()->getValue());

        $stmt->execute([
            ':reservationId' => $reservationRoom->getReservationId()->getValue(),
            ':roomId'        => $reservationRoom->getRoomId()->getValue(),
            ':startDate'     => $start,
            ':endDate'       => $end
        ]);
    }

    public function deleteReservationRoom(Identifier $reservationId, Identifier $roomId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Reserva_Habitacion WHERE IdReserva = :resId AND IdHabitacion = :roomId");
        $stmt->execute([
            ':resId'  => $reservationId->getValue(),
            ':roomId' => $roomId->getValue()
        ]);
    }

    public function getReservationRooms(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdHabitacion, FechaInicio, FechaFin FROM Reserva_Habitacion");
        $stmt->execute();
        return $this->mapRows($stmt->fetchAll());
    }

    public function getRoomsByReservation(Identifier $reservationId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdHabitacion, FechaInicio, FechaFin FROM Reserva_Habitacion WHERE IdReserva = :resId");
        $stmt->execute([':resId' => $reservationId->getValue()]);
        return $this->mapRows($stmt->fetchAll());
    }

    public function isRoomAvailable(string $roomId, string $startDate, string $endDate, string $ignoreReservationId = null): bool
    {
        // 1. PRIMERO: Verificar estado físico CRÍTICO
        // Solo bloqueamos si está en MANTENIMIENTO o BLOQUEADA. 
        // Si está 'Ocupada' o 'Limpieza' HOY, no importa, porque el cliente podría quererla para la próxima semana.
        $stmtCheck = $this->pdo->prepare("SELECT Estado FROM Habitaciones WHERE Id = :id");
        $stmtCheck->execute([':id' => $roomId]);
        $estadoFisico = $stmtCheck->fetchColumn();

        if ($estadoFisico === 'Mantenimiento' || $estadoFisico === 'Bloqueada') {
            return false; // Está rota/cerrada indefinidamente
        }

        // 2. SEGUNDO: Verificar solapamiento de fechas (Lo importante)
        // Revisamos si choca con reservas CONFIRMADAS u OCUPADAS en ese rango.
        $sql = "SELECT COUNT(*) FROM Vista_Reserva_Completa 
                WHERE IdHabitacion = :roomId 
                AND (EstadoActual IS NULL OR EstadoActual NOT IN ('Cancelada', 'Finalizada', 'Check-out')) 
                AND NOT (FechaFin <= :startDate OR FechaInicio >= :endDate)";
        
        if ($ignoreReservationId) {
            $sql .= " AND ReservaID != :ignoreId";
        }

        $stmt = $this->pdo->prepare($sql);
        $params = [':roomId' => $roomId, ':startDate' => $startDate, ':endDate' => $endDate];
        if ($ignoreReservationId) $params[':ignoreId'] = $ignoreReservationId;
        
        $stmt->execute($params);
        
        return $stmt->fetchColumn() == 0;
    }

    // Helper privado para mapear resultados
    private function mapRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $start = strtotime($row['FechaInicio']);
            $end   = strtotime($row['FechaFin']);

            $item = new ReadReservationRoom(
                new Identifier($row['IdReserva']),
                new Identifier($row['IdHabitacion'])
            );
            // Asignamos las fechas manualmente ya que el constructor original solo pedía IDs
            $item->setStartDate(new TimeStamp((int)$start));
            $item->setEndDate(new TimeStamp((int)$end));
            
            $result[] = $item;
        }
        return $result;
    }
}