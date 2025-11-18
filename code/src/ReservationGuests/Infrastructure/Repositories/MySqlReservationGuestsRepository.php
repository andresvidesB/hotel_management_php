<?php
// Archivo: src/ReservationGuests/Infrastructure/Repositories/MySqlReservationGuestsRepository.php

declare(strict_types=1);

namespace Src\ReservationGuests\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\ReservationGuests\Domain\Entities\ReadReservationGuest;
use Src\ReservationGuests\Domain\Entities\WriteReservationGuest;
use Src\ReservationGuests\Domain\Interfaces\ReservationGuestsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlReservationGuestsRepository implements ReservationGuestsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addReservationGuest(WriteReservationGuest $relation): void
    {
        // Tabla: Reserva_Huesped
        // PK Compuesta: (IdReserva, IdHuesped)
        $sql = "INSERT INTO Reserva_Huesped (IdReserva, IdHuesped) 
                VALUES (:reservationId, :guestId)";
        
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':reservationId' => $relation->getReservationId()->getValue(),
            ':guestId'       => $relation->getGuestId()->getValue()
        ]);
    }

    public function deleteReservationGuest(Identifier $guestId, Identifier $reservationId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Reserva_Huesped WHERE IdReserva = :resId AND IdHuesped = :guestId");
        $stmt->execute([
            ':resId'   => $reservationId->getValue(),
            ':guestId' => $guestId->getValue()
        ]);
    }

    public function getReservationGuests(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdHuesped FROM Reserva_Huesped");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $result = [];

        foreach ($rows as $row) {
            $result[] = new ReadReservationGuest(
                new Identifier($row['IdHuesped']),
                new Identifier($row['IdReserva'])
            );
        }

        return $result;
    }

    public function getGuestsByReservation(Identifier $reservationId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdHuesped FROM Reserva_Huesped WHERE IdReserva = :resId");
        $stmt->execute([':resId' => $reservationId->getValue()]);
        
        $rows = $stmt->fetchAll();
        $result = [];

        foreach ($rows as $row) {
            $result[] = new ReadReservationGuest(
                new Identifier($row['IdHuesped']),
                new Identifier($row['IdReserva'])
            );
        }

        return $result;
    }

    public function getReservationsByGuest(Identifier $guestId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdHuesped FROM Reserva_Huesped WHERE IdHuesped = :guestId");
        $stmt->execute([':guestId' => $guestId->getValue()]);
        
        $rows = $stmt->fetchAll();
        $result = [];

        foreach ($rows as $row) {
            $result[] = new ReadReservationGuest(
                new Identifier($row['IdHuesped']),
                new Identifier($row['IdReserva'])
            );
        }

        return $result;
    }
}