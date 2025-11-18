<?php

declare(strict_types=1);

namespace Src\Guests\Infrastructure\Factories;

use Src\Guests\Domain\Entities\WriteGuest;
use Src\Guests\Domain\ValueObjects\GuestDocumentType;
use Src\Guests\Domain\ValueObjects\GuestDocumentNumber;
use Src\Guests\Domain\ValueObjects\GuestCountry;
use Src\Shared\Domain\ValueObjects\Identifier;

final class GuestFactory
{
    public static function writeGuestFromArray(array $data): WriteGuest
    {
        return new WriteGuest(
            new Identifier($data['guest_id_person']),
            new GuestDocumentType($data['guest_document_type']),
            new GuestDocumentNumber($data['guest_document_number']),
            new GuestCountry($data['guest_country'] ?? '')
        );
    }
}
