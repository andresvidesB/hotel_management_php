<?php
namespace Src\Rooms\Domain\ValueObjects;

use Src\Shared\Domain\ValueObjects\CustomString;

final class RoomState extends CustomString
{
    public function verifyValue(): void
    {
        // Aquí podrías validar que sea uno de los estados permitidos:
        // 'Disponible', 'Ocupada', 'Mantenimiento', etc.
    }
}