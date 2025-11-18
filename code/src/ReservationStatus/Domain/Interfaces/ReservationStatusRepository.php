<?php

declare(strict_types=1);

namespace Src\ReservationStatus\Domain\Interfaces;

use Src\ReservationStatus\Domain\Entities\ReadReservationStatus;
use Src\ReservationStatus\Domain\Entities\WriteReservationStatus;
use Src\Shared\Domain\ValueObjects\Identifier;

interface ReservationStatusRepository
{
    public function addReservationStatus(WriteReservationStatus $relation): void;

    public function updateReservationStatus(WriteReservationStatus $relation): void;

    public function deleteReservationStatus(
        Identifier $reservationId,
        Identifier $statusId
    ): void;

    /**
     * @return ReadReservationStatus[]
     */
    public function getReservationStatuses(): array;

    /**
     * @return ReadReservationStatus[]
     */
    public function getStatusesByReservation(Identifier $reservationId): array;
}
