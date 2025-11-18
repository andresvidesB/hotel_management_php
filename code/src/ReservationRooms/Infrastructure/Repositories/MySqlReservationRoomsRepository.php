<?php
// Archivo: src/ReservationRooms/Infrastructure/Repositories/MySqlReservationRoomsRepository.php

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
        // Tabla: Reserva_Habitacion
        // PK Compuesta: (IdReserva, IdHabitacion)
        $sql = "INSERT INTO Reserva_Habitacion (IdReserva, IdHabitacion, FechaInicio, FechaFin) 
                VALUES (:reservationId, :roomId, :startDate, :endDate)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Conversión de TimeStamp (int) a DATE (string 'Y-m-d') para MySQL
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
        
        $rows = $stmt->fetchAll();
        $result = [];

        foreach ($rows as $row) {
            // Conversión de String fecha a Int Timestamp
            $start = strtotime($row['FechaInicio']);
            $end   = strtotime($row['FechaFin']);

            $result[] = new ReadReservationRoom(
                new Identifier($row['IdReserva']),
                new Identifier($row['IdHabitacion'])
            );
            // Nota: ReadReservationRoom en tu diseño actual solo recibe IDs en el constructor
            // Si quisieras las fechas en la lectura, tendrías que modificar ReadReservationRoom.
            // Por ahora, para cumplir con tu entidad actual, asignamos las fechas vía setters si existen,
            // o simplemente devolvemos la relación. 
            
            // Asumiendo que quieres las fechas en el objeto final:
             $item = end($result); // Tomamos el último insertado
             $item->setStartDate(new TimeStamp((int)$start));
             $item->setEndDate(new TimeStamp((int)$end));
        }

        return $result;
    }

    public function getRoomsByReservation(Identifier $reservationId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdHabitacion, FechaInicio, FechaFin FROM Reserva_Habitacion WHERE IdReserva = :resId");
        $stmt->execute([':resId' => $reservationId->getValue()]);
        
        $rows = $stmt->fetchAll();
        $result = [];

        foreach ($rows as $row) {
            $start = strtotime($row['FechaInicio']);
            $end   = strtotime($row['FechaFin']);

            $item = new ReadReservationRoom(
                new Identifier($row['IdReserva']),
                new Identifier($row['IdHabitacion'])
            );
            $item->setStartDate(new TimeStamp((int)$start));
            $item->setEndDate(new TimeStamp((int)$end));
            
            $result[] = $item;
        }

        return $result;
    }
}