<?php

declare(strict_types=1);

namespace Src\Statuses\Application\UseCases;

use Src\Statuses\Domain\Entities\WriteStatus;
use Src\Statuses\Domain\Interfaces\StatusesRepository;
use Src\Shared\Domain\Interfaces\IdentifierCreator;
use Src\Shared\Domain\ValueObjects\Identifier;

final class AddStatus
{
    public function __construct(
        private readonly StatusesRepository $statusesRepository,
        private readonly IdentifierCreator $identifierCreator
    ) {
    }

    public function execute(WriteStatus $status): Identifier
    {
        $status->setId($this->identifierCreator->createIdentifier());
        return $this->statusesRepository->addStatus($status);
    }
}
