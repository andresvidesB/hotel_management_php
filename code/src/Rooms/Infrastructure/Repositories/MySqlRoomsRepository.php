<?php
// Archivo: src/Rooms/Infrastructure/Repositories/MySqlRoomsRepository.php
declare(strict_types=1);

namespace Src\Rooms\Infrastructure\Repositories;

// 1. ¡Importamos las clases de PDO y nuestra nueva clase de Base de Datos!
use \PDO;
use Src\Shared\Infrastructure\Database; 
// (El resto de tus imports)
use Src\Rooms\Domain\Entities\ReadRoom;
use Src\Rooms\Domain\Entities\WriteRoom;
use Src\Rooms\Domain\Interfaces\RoomsRepository;
use Src\Rooms\Domain\ValueObjects\RoomCapacity;
use Src\Rooms\Domain\ValueObjects\RoomName;
use Src\Rooms\Domain\ValueObjects\RoomType;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

final class MySqlRoomsRepository implements RoomsRepository
{
    // 2. Propiedad para guardar la conexión
    private PDO $pdo;

    public function __construct()
    {
        // 3. Obtenemos la conexión PDO al crear el repositorio
        $this->pdo = (new Database())->getConnection();
    }

    /**
     * CONSULTA (INSERT): Añadir una habitación.
     */
    public function addRoom(WriteRoom $room): Identifier
    {
        $sql = "INSERT INTO Habitaciones (Id, Nombre, Tipo, Precio, Capacidad) 
                VALUES (:id, :nombre, :tipo, :precio, :capacidad)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // 4. Usamos 'execute' con un array para prevenir Inyección SQL.
        $stmt->execute([
            ':id' => $room->getId()->getValue(),
            ':nombre' => $room->getName()->getValue(),
            ':tipo' => $room->getType()->getValue(),
            ':precio' => $room->getPrice()->getValue(),
            ':capacidad' => $room->getCapacity()->getValue()
        ]);
        
        return $room->getId();
    }

    /**
     * CONSULTA (UPDATE): Actualizar una habitación.
     */
    public function updateRoom(WriteRoom $room): void
    {
        $sql = "UPDATE Habitaciones SET 
                    Nombre = :nombre, 
                    Tipo = :tipo, 
                    Precio = :precio, 
                    Capacidad = :capacidad 
                WHERE Id = :id";
                
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':id' => $room->getId()->getValue(),
            ':nombre' => $room->getName()->getValue(),
            ':tipo' => $room->getType()->getValue(),
            ':precio' => $room->getPrice()->getValue(),
            ':capacidad' => $room->getCapacity()->getValue()
        ]);
    }

    /**
     * CONSULTA (SELECT BY ID): Obtener una habitación por ID.
     */
    public function getRoomById(Identifier $id): ?ReadRoom
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre, Tipo, Precio, Capacidad FROM Habitaciones WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
        
        $row = $stmt->fetch();

        if (!$row) {
            return null; // No se encontró
        }

        // 5. Mapeamos los datos de la BD a nuestras Entidades y Value Objects
        return new ReadRoom(
            new Identifier($row['Id']),
            new RoomName($row['Nombre']),
            new RoomType($row['Tipo']),
            new Price((float)$row['Precio']),
            new RoomCapacity((int)$row['Capacidad'])
        );
    }

    /**
     * CONSULTA (SELECT): Obtener todas las habitaciones.
     *
     * @return ReadRoom[]
     * @psalm-return list<ReadRoom>
     * @phpstan-return list<ReadRoom>
     */
    public function getRooms(): array
    {
        $stmt = $this->pdo->prepare("SELECT Id, Nombre, Tipo, Precio, Capacidad FROM Habitaciones");
        $stmt->execute();
        
        $rows = $stmt->fetchAll();
        $rooms = [];

        foreach ($rows as $row) {
            $rooms[] = new ReadRoom(
                new Identifier($row['Id']),
                new RoomName($row['Nombre']),
                new RoomType($row['Tipo']),
                new Price((float)$row['Precio']),
                new RoomCapacity((int)$row['Capacidad'])
            );
        }

        return $rooms;
    }

    /**
     * CONSULTA (DELETE): Borrar una habitación.
     */
    public function deleteRoom(Identifier $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Habitaciones WHERE Id = :id");
        $stmt->execute([':id' => $id->getValue()]);
    }
}