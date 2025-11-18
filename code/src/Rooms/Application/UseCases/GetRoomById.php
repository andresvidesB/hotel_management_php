<?php
namespace Src\Rooms\Application\UseCases;

use Src\Rooms\Domain\Entities\ReadRoom;
use Src\Rooms\Domain\Interfaces\RoomsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

class GetRoomById
{
    public function __construct(
        private readonly RoomsRepository $roomsRepository
    ) {
    }

    public function execute(Identifier $id): ReadRoom
    {
        return $this->roomsRepository->getRoomById($id);
    }
}
