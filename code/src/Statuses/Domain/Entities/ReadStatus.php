<?php

declare(strict_types=1);

namespace Src\Statuses\Domain\Entities;

use Src\Statuses\Domain\ValueObjects\StatusName;
use Src\Statuses\Domain\ValueObjects\StatusDescription;
use Src\Shared\Domain\ValueObjects\Identifier;

final class ReadStatus
{
    private Identifier $id;
    private StatusName $name;
    private StatusDescription $description;

    public function __construct(
        Identifier $id,
        StatusName $name,
        StatusDescription $description
    ) {
        $this->id          = $id;
        $this->name        = $name;
        $this->description = $description;
    }

    // GETTERS
    public function getId(): Identifier
    {
        return $this->id;
    }

    public function getName(): StatusName
    {
        return $this->name;
    }

    public function getDescription(): StatusDescription
    {
        return $this->description;
    }

    // SETTERS
    public function setId(Identifier $id): void
    {
        $this->id = $id;
    }

    public function setName(StatusName $name): void
    {
        $this->name = $name;
    }

    public function setDescription(StatusDescription $description): void
    {
        $this->description = $description;
    }

    public function toArray(): array
    {
        return [
            'status_id'          => $this->getId()->getValue(),
            'status_name'        => $this->getName()->getValue(),
            'status_description' => $this->getDescription()->getValue(),
        ];
    }
}
