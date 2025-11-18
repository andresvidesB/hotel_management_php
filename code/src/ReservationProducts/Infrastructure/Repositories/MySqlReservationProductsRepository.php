<?php
// Archivo: src/ReservationProducts/Infrastructure/Repositories/MySqlReservationProductsRepository.php

declare(strict_types=1);

namespace Src\ReservationProducts\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\ReservationProducts\Domain\Entities\ReadReservationProduct;
use Src\ReservationProducts\Domain\Entities\WriteReservationProduct;
use Src\ReservationProducts\Domain\Interfaces\ReservationProductsRepository;
use Src\ReservationProducts\Domain\ValueObjects\ReservationProductQuantity;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationProductsRepository implements ReservationProductsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addReservationProduct(WriteReservationProduct $relation): void
    {
        // Tabla: Reserva_Productos
        // PK Compuesta: (IdReserva, IdProducto)
        $sql = "INSERT INTO Reserva_Productos (IdReserva, IdProducto, Cantidad, FechaConsumo) 
                VALUES (:reservationId, :productId, :quantity, :consumptionDate)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Conversión de Fecha: TimeStamp (int) -> MySQL DATE (string)
        $date = date('Y-m-d', $relation->getConsumptionDate()->getValue());

        $stmt->execute([
            ':reservationId'   => $relation->getReservationId()->getValue(),
            ':productId'       => $relation->getProductId()->getValue(),
            ':quantity'        => $relation->getQuantity()->getValue(),
            ':consumptionDate' => $date
        ]);
    }

    public function updateReservationProduct(WriteReservationProduct $relation): void
    {
        // Actualizamos la cantidad o fecha de un producto ya agregado
        $sql = "UPDATE Reserva_Productos SET 
                    Cantidad = :quantity,
                    FechaConsumo = :consumptionDate
                WHERE IdReserva = :reservationId AND IdProducto = :productId";

        $stmt = $this->pdo->prepare($sql);
        $date = date('Y-m-d', $relation->getConsumptionDate()->getValue());

        $stmt->execute([
            ':reservationId'   => $relation->getReservationId()->getValue(),
            ':productId'       => $relation->getProductId()->getValue(),
            ':quantity'        => $relation->getQuantity()->getValue(),
            ':consumptionDate' => $date
        ]);
    }

    public function deleteReservationProduct(Identifier $reservationId, Identifier $productId): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM Reserva_Productos WHERE IdReserva = :resId AND IdProducto = :prodId");
        $stmt->execute([
            ':resId'  => $reservationId->getValue(),
            ':prodId' => $productId->getValue()
        ]);
    }

    public function getReservationProducts(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdProducto, Cantidad, FechaConsumo FROM Reserva_Productos");
        $stmt->execute();
        return $this->mapRows($stmt->fetchAll());
    }

    public function getProductsByReservation(Identifier $reservationId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdProducto, Cantidad, FechaConsumo FROM Reserva_Productos WHERE IdReserva = :id");
        $stmt->execute([':id' => $reservationId->getValue()]);
        return $this->mapRows($stmt->fetchAll());
    }

    public function getReservationsByProduct(Identifier $productId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, IdProducto, Cantidad, FechaConsumo FROM Reserva_Productos WHERE IdProducto = :id");
        $stmt->execute([':id' => $productId->getValue()]);
        return $this->mapRows($stmt->fetchAll());
    }

    // Helper para evitar repetir el mapeo en cada función
    private function mapRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $dateInt = strtotime($row['FechaConsumo']);

            $result[] = new ReadReservationProduct(
                new Identifier($row['IdReserva']),
                new Identifier($row['IdProducto']),
                new ReservationProductQuantity((int)$row['Cantidad']),
                new TimeStamp((int)$dateInt)
            );
        }
        return $result;
    }
}