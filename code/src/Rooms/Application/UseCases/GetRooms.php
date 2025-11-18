<?php

namespace Src\Rooms\Application\UseCases;

use Src\Rooms\Domain\Entities\ReadRoom;
use Src\Rooms\Domain\Interfaces\RoomsRepository;

class GetRooms
{
    public function __construct(
        private readonly RoomsRepository $roomsRepository
    ) {
    }
    /**
     * @return ReadRoom[]           Elementos del array son ReadRoom
     * @psalm-return list<ReadRoom> Secuencia indexada (0..n-1), sin huecos
     * @phpstan-return list<ReadRoom>
     */
    public function execute(): array
    {
        return $this->roomsRepository->getRooms();
    }
}
