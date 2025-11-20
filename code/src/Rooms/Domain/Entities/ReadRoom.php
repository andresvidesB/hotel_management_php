<?php
namespace Src\Rooms\Domain\Entities;

use Src\Rooms\Domain\ValueObjects\RoomCapacity;
use Src\Rooms\Domain\ValueObjects\RoomName;
use Src\Rooms\Domain\ValueObjects\RoomType;
use Src\Rooms\Domain\ValueObjects\RoomState; // Nuevo
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;

class ReadRoom
{
    private Identifier $id;
    private RoomName $name;
    private RoomType $type;
    private Price $price;
    private RoomCapacity $capacity;
    private RoomState $state; // Nuevo

    public function __construct(
        Identifier $id,
        RoomName $name,
        RoomType $type,
        Price $price,
        RoomCapacity $capacity,
        RoomState $state // Nuevo
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->price = $price;
        $this->capacity = $capacity;
        $this->state = $state;
    }

    public function getId(): Identifier { return $this->id; }
    public function getName(): RoomName { return $this->name; }
    public function getType(): RoomType { return $this->type; }
    public function getPrice(): Price { return $this->price; }
    public function getCapacity(): RoomCapacity { return $this->capacity; }
    public function getState(): RoomState { return $this->state; } // Nuevo

    public function toArray(): array{
        return [
            "room_id"=> $this->getId()->getValue(),
            "room_name"=> $this->getName()->getValue(),
            "room_type"=> $this->getType()->getValue(),
            "room_price"=> $this->getPrice()->getValue(),
            "room_capacity"=> $this->getCapacity()->getValue(),
            "room_state"=> $this->getState()->getValue() // Nuevo
        ];
    }
}