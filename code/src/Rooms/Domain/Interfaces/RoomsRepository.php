<?php

namespace Src\Rooms\Domain\Interfaces;

use Src\Rooms\Domain\Entities\ReadRoom;
use Src\Rooms\Domain\Entities\WriteRoom;
use Src\Shared\Domain\ValueObjects\Identifier;


interface RoomsRepository
{
    public function addRoom(WriteRoom $room): Identifier;
    public function updateRoom(WriteRoom $room): void;

    /** @return ReadRoom|null */
    public function getRoomById(Identifier $id): ?ReadRoom;
    public function deleteRoom(Identifier $id): void;

    /**
     * @return ReadRoom[]           Elementos del array son ReadRoom
     * @psalm-return list<ReadRoom> Secuencia indexada (0..n-1), sin huecos
     * @phpstan-return list<ReadRoom>
     */
    public function getRooms(): array;
}