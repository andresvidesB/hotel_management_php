<?php

namespace Src\Rooms\Application\UseCases;

use Src\Rooms\Domain\Entities\WriteRoom;
use Src\Rooms\Domain\Interfaces\RoomsRepository;
use Src\Shared\Domain\Interfaces\IdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

class AddRoom
{
    public function __construct(
        private readonly RoomsRepository $roomsRepository,
        private readonly IdentifierCreator $identifierCreator
    ) {
    }

    public function execute(WriteRoom $room): Identifier
    {
        $room->setId($this->identifierCreator->createIdentifier());
        return $this->roomsRepository->addRoom($room);
    }
}