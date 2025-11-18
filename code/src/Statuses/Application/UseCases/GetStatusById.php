<?php

declare(strict_types=1);

namespace Src\Statuses\Application\UseCases;

use Src\Statuses\Domain\Entities\ReadStatus;
use Src\Statuses\Domain\Interfaces\StatusesRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GetStatusById
{
    public function __construct(
        private readonly StatusesRepository $statusesRepository
    ) {
    }

    public function execute(Identifier $id): ?ReadStatus
    {
        return $this->statusesRepository->getStatusById($id);
    }
}
