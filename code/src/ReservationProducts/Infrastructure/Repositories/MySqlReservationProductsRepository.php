<?php
declare(strict_types=1);

namespace Src\ReservationProducts\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\ReservationProducts\Domain\Entities\WriteReservationProduct;
use Src\ReservationProducts\Domain\Interfaces\ReservationProductsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;

final class MySqlReservationProductsRepository implements ReservationProductsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addConsumption(string $resId, string $prodId, int $qty, string $date, bool $isPaid): void
    {
        $paidVal = $isPaid ? 1 : 0;
        
        // Usamos INSERT ... ON DUPLICATE KEY UPDATE para sumar cantidad si ya existe
        // IMPORTANTE: Si ya existe, NO sobrescribimos el estado de pago para no mezclar cuentas.
        $sql = "INSERT INTO Reserva_Productos (IdReserva, IdProducto, Cantidad, FechaConsumo, Pagado) 
                VALUES (:resId, :prodId, :qty, :date, :paid)
                ON DUPLICATE KEY UPDATE Cantidad = Cantidad + :qty"; 
        
        $stmt = $this->pdo->prepare($sql);
        
        $stmt->execute([
            ':resId'  => $resId,
            ':prodId' => $prodId,
            ':qty'    => $qty,
            ':date'   => $date,
            ':paid'   => $paidVal
        ]);
    }

    /**
     * NUEVO MÉTODO: Marcar un consumo específico como PAGADO
     */
    public function markAsPaid(string $resId, string $prodId): void
    {
        $sql = "UPDATE Reserva_Productos SET Pagado = 1 WHERE IdReserva = :resId AND IdProducto = :prodId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':resId'  => $resId,
            ':prodId' => $prodId
        ]);
    }

    // Métodos de la interfaz
    public function addReservationProduct(WriteReservationProduct $relation): void
    {
        $date = date('Y-m-d', $relation->getConsumptionDate()->getValue());
        $this->addConsumption(
            $relation->getReservationId()->getValue(),
            $relation->getProductId()->getValue(),
            $relation->getQuantity()->getValue(),
            $date,
            false 
        );
    }

    public function updateReservationProduct(WriteReservationProduct $relation): void
    {
        // No usado en este flujo
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
        $stmt = $this->pdo->prepare("SELECT * FROM Reserva_Productos ORDER BY FechaConsumo DESC");
        $stmt->execute();
        return $this->mapRows($stmt->fetchAll());
    }

    public function getProductsByReservation(Identifier $reservationId): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM Reserva_Productos WHERE IdReserva = :id");
        $stmt->execute([':id' => $reservationId->getValue()]);
        return $this->mapRows($stmt->fetchAll());
    }

    public function getReservationsByProduct(Identifier $productId): array { return []; }

    private function mapRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'reservation_product_reservation_id' => $row['IdReserva'],
                'reservation_product_product_id' => $row['IdProducto'],
                'reservation_product_quantity' => (int)$row['Cantidad'],
                'reservation_product_consumption_date' => strtotime($row['FechaConsumo']),
                // Aseguramos que 'Pagado' se lea como booleano
                'is_paid' => isset($row['Pagado']) && $row['Pagado'] == 1
            ];
        }
        return $result;
    }
}