<?php

declare(strict_types=1);

namespace Src\Statuses\Infrastructure\Factories;

use Src\Statuses\Domain\Entities\WriteStatus;
use Src\Statuses\Domain\ValueObjects\StatusName;
use Src\Statuses\Domain\ValueObjects\StatusDescription;
use Src\Shared\Domain\ValueObjects\Identifier;

final class StatusFactory
{
    public static function writeStatusFromArray(array $data): WriteStatus
    {
        return new WriteStatus(
            new Identifier($data['status_id'] ?? ''), // puede venir vacío para Add
            new StatusName($data['status_name']),
            new StatusDescription($data['status_description'] ?? '')
        );
    }
}
