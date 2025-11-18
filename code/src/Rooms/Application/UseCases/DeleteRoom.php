<?php

namespace Src\Rooms\Application\UseCases;

use Src\Rooms\Domain\Interfaces\RoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

class DeleteRoom
{
    public function __construct(
        private readonly RoomsRepository $roomsRepository
    ) {
    }

    public function execute(Identifier $id): void
    {
        $this->roomsRepository->deleteRoom($id);
    }
}
