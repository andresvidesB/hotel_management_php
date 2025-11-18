<?php

declare(strict_types=1);

namespace Src\Statuses\Application\UseCases;

use Src\Statuses\Domain\Interfaces\StatusesRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class DeleteStatus
{
    public function __construct(
        private readonly StatusesRepository $statusesRepository
    ) {
    }

    public function execute(Identifier $id): void
    {
        $this->statusesRepository->deleteStatus($id);
    }
}
