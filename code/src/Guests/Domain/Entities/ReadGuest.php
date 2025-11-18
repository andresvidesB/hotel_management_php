<?php

declare(strict_types=1);

namespace Src\Guests\Domain\Entities;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Guests\Domain\ValueObjects\GuestDocumentType;
use Src\Guests\Domain\ValueObjects\GuestDocumentNumber;
use Src\Guests\Domain\ValueObjects\GuestCountry;

final class ReadGuest
{
    private Identifier $idPerson;
    private GuestDocumentType $documentType;
    private GuestDocumentNumber $documentNumber;
    private GuestCountry $country;

    public function __construct(
        Identifier $idPerson,
        GuestDocumentType $documentType,
        GuestDocumentNumber $documentNumber,
        GuestCountry $country
    ) {
        $this->idPerson       = $idPerson;
        $this->documentType   = $documentType;
        $this->documentNumber = $documentNumber;
        $this->country        = $country;
    }

    // GETTERS
    public function getIdPerson(): Identifier
    {
        return $this->idPerson;
    }

    public function getDocumentType(): GuestDocumentType
    {
        return $this->documentType;
    }

    public function getDocumentNumber(): GuestDocumentNumber
    {
        return $this->documentNumber;
    }

    public function getCountry(): GuestCountry
    {
        return $this->country;
    }

    // SETTERS
    public function setIdPerson(Identifier $id): void
    {
        $this->idPerson = $id;
    }

    public function setDocumentType(GuestDocumentType $type): void
    {
        $this->documentType = $type;
    }

    public function setDocumentNumber(GuestDocumentNumber $number): void
    {
        $this->documentNumber = $number;
    }

    public function setCountry(GuestCountry $country): void
    {
        $this->country = $country;
    }

    public function toArray(): array
    {
        return [
            'guest_id_person'      => $this->idPerson->getValue(),
            'guest_document_type'  => $this->documentType->getValue(),
            'guest_document_number'=> $this->documentNumber->getValue(),
            'guest_country'        => $this->country->getValue(),
        ];
    }
}
