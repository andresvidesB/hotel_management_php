<?php
// File: src/Statuses/Infrastructure/Services/StatusesController.php

declare(strict_types=1);

namespace Src\Statuses\Infrastructure\Services;

use Src\Statuses\Application\UseCases\AddStatus;
use Src\Statuses\Application\UseCases\UpdateStatus;
use Src\Statuses\Application\UseCases\DeleteStatus;
use Src\Statuses\Application\UseCases\GetStatuses;
use Src\Statuses\Application\UseCases\GetStatusById;
use Src\Statuses\Domain\Entities\ReadStatus;
use Src\Statuses\Infrastructure\Factories\StatusFactory;
use Src\Statuses\Infrastructure\Repositories\MySqlStatusesRepository;
use Src\Shared\Infrastructure\Services\UuidIdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class StatusesController
{
    public static function addStatus(array $status): Identifier
    {
        $statusEntity = StatusFactory::writeStatusFromArray($status);
        $useCase      = new AddStatus(self::repo(), self::idCreator());

        return $useCase->execute($statusEntity);
    }

    public static function updateStatus(array $status): void
    {
        $statusEntity = StatusFactory::writeStatusFromArray($status);
        $useCase      = new UpdateStatus(self::repo());

        $useCase->execute($statusEntity);
    }

    public static function deleteStatus(string $id): void
    {
        $useCase = new DeleteStatus(self::repo());
        $useCase->execute(new Identifier($id));
    }

    /**
     * @return array<array<string,mixed>>
     */
    public static function getStatuses(): array
    {
        $useCase = new GetStatuses(self::repo());

        /** @var ReadStatus[] $items */
        $items = $useCase->execute();

        $statuses = [];
        foreach ($items as $status) {
            if (!$status instanceof ReadStatus) { // por qué: evitar respuestas inconsistentes si el repo cambia
                continue;
            }
            $statuses[] = $status->toArray();
        }

        return $statuses;
    }

    /**
     * @return array<string,mixed> Empty array si no existe.
     */
    public static function getStatusById(string $id): array
    {
        $useCase = new GetStatusById(self::repo());
        $read    = $useCase->execute(new Identifier($id));

        return $read instanceof ReadStatus ? $read->toArray() : [];
    }

    /** Helpers estáticos para dependencias */
    private static function repo(): MySqlStatusesRepository
    {
        return new MySqlStatusesRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }
}
