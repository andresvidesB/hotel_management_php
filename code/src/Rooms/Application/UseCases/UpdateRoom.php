<?php

namespace Src\Rooms\Application\UseCases;

use Src\Rooms\Domain\Entities\WriteRoom;
use Src\Rooms\Domain\Interfaces\RoomsRepository;

class UpdateRoom
{
    public function __construct(
        private readonly RoomsRepository $roomsRepository
    ) {
    }

    public function execute(WriteRoom $room): void
    {
        $this->roomsRepository->updateRoom($room);
    }
}
