<?php

declare(strict_types=1);

namespace Src\Reservations\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Reservations\Domain\ValueObjects\ReservationSource;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class WriteReservation
{
    private Identifier $id;
    private ReservationSource $source;
    private Identifier $userId;
    private TimeStamp $createdAt;

    public function __construct(
        Identifier $id,
        ReservationSource $source,
        Identifier $userId,
        TimeStamp $createdAt
    ) {
        $this->id        = $id;
        $this->source    = $source;
        $this->userId    = $userId;
        $this->createdAt = $createdAt;
    }

    // GETTERS
    public function getId(): Identifier { return $this->id; }
    public function getSource(): ReservationSource { return $this->source; }
    public function getUserId(): Identifier { return $this->userId; }
    public function getCreatedAt(): TimeStamp { return $this->createdAt; }

    // SETTERS
    public function setId(Identifier $id): void { $this->id = $id; }
    public function setSource(ReservationSource $source): void { $this->source = $source; }
    public function setUserId(Identifier $userId): void { $this->userId = $userId; }
    public function setCreatedAt(TimeStamp $createdAt): void { $this->createdAt = $createdAt; }
}
