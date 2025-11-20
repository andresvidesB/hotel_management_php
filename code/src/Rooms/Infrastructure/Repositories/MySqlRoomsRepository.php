<?php
declare(strict_types=1);

namespace Src\Rooms\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\Rooms\Domain\Entities\ReadRoom;
use Src\Rooms\Domain\Entities\WriteRoom;
use Src\Rooms\Domain\Interfaces\RoomsRepository;
use Src\Rooms\Domain\ValueObjects\RoomCapacity;
use Src\Rooms\Domain\ValueObjects\RoomName;
use Src\Rooms\Domain\ValueObjects\RoomType;
use Src\Rooms\Domain\ValueObjects\RoomState;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class MySqlRoomsRepository implements RoomsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addRoom(WriteRoom $room): Identifier
    {
        $sql = "INSERT INTO Habitaciones (Id, Nombre, Tipo, Precio, Capacidad, Estado) 
                VALUES (:id, :nombre, :tipo, :precio, :capacidad, :estado)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $room->getId()->getValue(),
            ':nombre' => $room->getName()->getValue(),
            ':tipo' => $room->getType()->getValue(),
            ':precio' => $room->getPrice()->getValue(),
            ':capacidad' => $room->getCapacity()->getValue(),
            ':estado' => $room->getState()->getValue()
        ]);
        return $room->getId();
    }

    public function updateRoom(WriteRoom $room): void
    {
        $sql = "UPDATE Habitaciones SET 
                    Nombre = :nombre, 
                    Tipo = :tipo, 
                    Precio = :precio, 
                    Capacidad = :capacidad,
                    Estado = :estado
                WHERE Id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $room->getId()->getValue(),
            ':nombre' => $room->getName()->getValue(),
            ':tipo' => $room->getType()->getValue(),
            ':precio' => $room->getPrice()->getValue(),
            ':capacidad' => $room->getCapacity()->getValue(),
            ':estado' => $room->getState()->getValue()
        ]);
    }

    public function getRoomById(Identifier $id): ?ReadRoom
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Habitaciones WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch();

        if (!$row) return null;
        return $this->mapRow($row);
    }

    public function getRooms(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Habitaciones");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        
        $rooms = [];
        foreach ($rows as $row) {
            $rooms[] = $this->mapRow($row);
        }
        return $rooms;
    }

    public function deleteRoom(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Habitaciones WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }

    private function mapRow(array $row): ReadRoom
    {
        return new ReadRoom(
            new Identifier($row['Id']),
            new RoomName($row['Nombre']),
            new RoomType($row['Tipo']),
            new Price((float)$row['Precio']),
            new RoomCapacity((int)$row['Capacidad']),
            new RoomState($row['Estado'] ?? 'Disponible')
        );
    }

    /**
     * NUEVO MÉTODO: Actualizar solo el estado de la habitación
     */
    public function updateRoomState(Identifier $id, RoomState $state): void
    {
        $sql = "UPDATE Habitaciones SET Estado = :state WHERE Id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':state' => $state->getValue(),
            ':id' => $id->getValue()
        ]);
    }
}