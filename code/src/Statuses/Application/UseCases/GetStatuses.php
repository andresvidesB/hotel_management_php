<?php

declare(strict_types=1);

namespace Src\Statuses\Application\UseCases;

use Src\Statuses\Domain\Entities\ReadStatus;
use Src\Statuses\Domain\Interfaces\StatusesRepository;

final class GetStatuses
{
    public function __construct(
        private readonly StatusesRepository $statusesRepository
    ) {
    }

    /**
     * @return ReadStatus[]
     * @psalm-return list<ReadStatus>
     * @phpstan-return list<ReadStatus>
     */
    public function execute(): array
    {
        return $this->statusesRepository->getStatuses();
    }
}
