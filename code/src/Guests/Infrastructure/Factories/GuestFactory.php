<?php
// Archivo: src/Guests/Infrastructure/Factories/GuestFactory.php

declare(strict_types=1);

namespace Src\Guests\Infrastructure\Factories;

use Src\Guests\Domain\Entities\WriteGuest;
// Imports de Value Objects
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Guests\Domain\ValueObjects\GuestDocumentType;
use Src\Guests\Domain\ValueObjects\GuestDocumentNumber;
use Src\Guests\Domain\ValueObjects\GuestCountry;

final class GuestFactory
{
    public static function writeGuestFromArray(array $data): WriteGuest
    {
        return new WriteGuest(
            // El ID puede venir vacío si es nuevo, o lleno si es update
            new Identifier($data['guest_id_person'] ?? ''),
            new GuestDocumentType($data['guest_document_type']),
            new GuestDocumentNumber($data['guest_document_number']),
            new GuestCountry($data['guest_country'] ?? '')
        );
    }
}