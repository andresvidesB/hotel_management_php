<?php
declare(strict_types=1);

namespace Src\Statuses\Infrastructure\Services;

use Src\Statuses\Application\UseCases\AddStatus;
use Src\Statuses\Application\UseCases\UpdateStatus;
use Src\Statuses\Application\UseCases\DeleteStatus;
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
     * MÉTODO ACTUALIZADO: 
     * Obtiene el array limpio directamente del repositorio.
     */
    public static function getStatuses(): array
    {
        // Llamamos directo al repositorio para obtener el array ya formateado
        // (status_id, status_name)
        return self::repo()->getStatuses();
    }

    public static function getStatusById(string $id): array
    {
        $useCase = new GetStatusById(self::repo());
        $read    = $useCase->execute(new Identifier($id));
        return $read instanceof ReadStatus ? $read->toArray() : [];
    }

    private static function repo(): MySqlStatusesRepository
    {
        return new MySqlStatusesRepository();
    }

    private static function idCreator(): UuidIdentifierCreator
    {
        return new UuidIdentifierCreator();
    }
}