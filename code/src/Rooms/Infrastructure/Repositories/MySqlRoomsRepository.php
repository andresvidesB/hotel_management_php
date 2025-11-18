<?php
// File: src/Rooms/Infrastructure/Repositories/MySqlRoomsRepository.php
declare(strict_types=1);

namespace Src\Rooms\Infrastructure\Repositories;

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
    public function addRoom(WriteRoom $room): Identifier
    {
        // Mock: ID determinístico para pruebas
        return new Identifier('00000000-0000-0000-0000-000000000001');
    }

    public function updateRoom(WriteRoom $room): void
    {
        // Mock: sin persistencia
    }

    public function getRoomById(Identifier $id): ?ReadRoom
    {
        foreach ($this->seedRooms() as $room) {
            if ($room->getId()->getValue() === $id->getValue()) {
                return $room;
            }
        }
        return null;
    }

    /**
     * @return ReadRoom[]
     * @psalm-return list<ReadRoom>
     * @phpstan-return list<ReadRoom>
     */
    public function getRooms(): array
    {
        return $this->seedRooms();
    }

    public function deleteRoom(Identifier $id): void
    {
        // Mock: sin persistencia
    }

    /**
     * Dataset de prueba consistente.
     * @return list<ReadRoom>
     */
    private function seedRooms(): array
    {
        return [
            $this->makeReadRoom(
                id: '101',
                name: 'Deluxe King',
                type: 'king',
                price: 129.99,
                capacity: 2
            ),
            $this->makeReadRoom(
                id: '102',
                name: 'Family Suite',
                type: 'suite',
                price: 199.50,
                capacity: 4
            ),
            $this->makeReadRoom(
                id: '201',
                name: 'Standard Twin',
                type: 'twin',
                price: 89.00,
                capacity: 2
            ),
        ];
    }

    private function makeReadRoom(
        string $id,
        string $name,
        string $type,
        float $price,
        int $capacity
    ): ReadRoom {
        // Por qué: Garantiza VOs válidos en el mock igual que en producción.
        return new ReadRoom(
            new Identifier($id),
            new RoomName($name),
            new RoomType($type),
            new Price($price),
            new RoomCapacity($capacity)
        );
    }
}
