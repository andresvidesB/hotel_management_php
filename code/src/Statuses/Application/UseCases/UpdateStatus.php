<?php

declare(strict_types=1);

namespace Src\Statuses\Application\UseCases;

use Src\Statuses\Domain\Entities\WriteStatus;
use Src\Statuses\Domain\Interfaces\StatusesRepository;

final class UpdateStatus
{
    public function __construct(
        private readonly StatusesRepository $statusesRepository
    ) {
    }

    public function execute(WriteStatus $status): void
    {
        $this->statusesRepository->updateStatus($status);
    }
}
