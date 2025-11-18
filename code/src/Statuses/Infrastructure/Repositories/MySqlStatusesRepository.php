<?php
// File: src/Statuses/Infrastructure/Repositories/MySqlStatusesRepository.php

declare(strict_types=1);

namespace Src\Statuses\Infrastructure\Repositories;

use Src\Statuses\Domain\Entities\ReadStatus;
use Src\Statuses\Domain\Entities\WriteStatus;
use Src\Statuses\Domain\Interfaces\StatusesRepository;
use Src\Statuses\Domain\ValueObjects\StatusName;
use Src\Statuses\Domain\ValueObjects\StatusDescription;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlStatusesRepository implements StatusesRepository
{
    public function addStatus(WriteStatus $status): Identifier
    {
        // Mock: ID determinístico para pruebas
        return new Identifier('00000000-0000-0000-0000-000000000201');
    }

    public function updateStatus(WriteStatus $status): void
    {
        // Mock: sin persistencia
    }

    public function getStatusById(Identifier $id): ?ReadStatus
    {
        foreach ($this->seedStatuses() as $status) {
            if ($status->getId()->getValue() === $id->getValue()) {
                return $status;
            }
        }
        return null;
    }

    /**
     * @return ReadStatus[]
     * @psalm-return list<ReadStatus>
     * @phpstan-return list<ReadStatus>
     */
    public function getStatuses(): array
    {
        return $this->seedStatuses();
    }

    public function deleteStatus(Identifier $id): void
    {
        // Mock: sin persistencia
    }

    /**
     * Dataset de prueba consistente.
     * @return list<ReadStatus>
     */
    private function seedStatuses(): array
    {
        return [
            $this->makeReadStatus(
                id: '1',
                name: 'Activo',
                description: 'Recurso disponible y operativo.'
            ),
            $this->makeReadStatus(
                id: '2',
                name: 'Inactivo',
                description: 'Recurso no disponible temporalmente.'
            ),
            $this->makeReadStatus(
                id: '3',
                name: 'En mantenimiento',
                description: 'Recurso en proceso de revisión o reparación.'
            ),
            $this->makeReadStatus(
                id: '4',
                name: 'Cancelado',
                description: 'Recurso o reserva cancelada definitivamente.'
            ),
        ];
    }

    private function makeReadStatus(
        string $id,
        string $name,
        string $description
    ): ReadStatus {
        // Por qué: Garantiza VOs válidos en el mock igual que en producción.
        return new ReadStatus(
            new Identifier($id),
            new StatusName($name),
            new StatusDescription($description)
        );
    }
}
