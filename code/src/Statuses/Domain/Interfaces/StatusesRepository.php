<?php

declare(strict_types=1);

namespace Src\Statuses\Domain\Interfaces;

use Src\Statuses\Domain\Entities\ReadStatus;
use Src\Statuses\Domain\Entities\WriteStatus;
use Src\Shared\Domain\ValueObjects\Identifier;

interface StatusesRepository
{
    public function addStatus(WriteStatus $status): Identifier;
    public function updateStatus(WriteStatus $status): void;

    /** @return ReadStatus|null */
    public function getStatusById(Identifier $id): ?ReadStatus;

    public function deleteStatus(Identifier $id): void;

    /**
     * @return ReadStatus[]           Elementos del array son ReadStatus
     * @psalm-return list<ReadStatus> Secuencia indexada (0..n-1), sin huecos
     * @phpstan-return list<ReadStatus>
     */
    public function getStatuses(): array;
}
