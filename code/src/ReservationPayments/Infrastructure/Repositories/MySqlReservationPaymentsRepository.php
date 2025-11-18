<?php
declare(strict_types=1);

namespace Src\ReservationPayments\Infrastructure\Repositories;

use \PDO;
use Src\Shared\Infrastructure\Database;
use Src\ReservationPayments\Domain\Entities\ReadReservationPayment;
use Src\ReservationPayments\Domain\Entities\WriteReservationPayment;
use Src\ReservationPayments\Domain\Interfaces\ReservationPaymentsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Shared\Domain\ValueObjects\Price;
use Src\Shared\Domain\ValueObjects\TimeStamp;

final class MySqlReservationPaymentsRepository implements ReservationPaymentsRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    public function addReservationPayment(WriteReservationPayment $payment): void
    {
        // Tabla: Reserva_Pagos
        $sql = "INSERT INTO Reserva_Pagos (IdReserva, Cantidad, FechaPago) 
                VALUES (:reservationId, :amount, :paymentDate)";
        
        $stmt = $this->pdo->prepare($sql);
        
        // Conversión: TimeStamp -> MySQL DATE
        $date = date('Y-m-d', $payment->getPaymentDate()->getValue());

        $stmt->execute([
            ':reservationId' => $payment->getReservationId()->getValue(),
            ':amount'        => $payment->getAmount()->getValue(),
            ':paymentDate'   => $date
        ]);
    }

    public function updateReservationPayment(WriteReservationPayment $payment): void
    {
        // Nota: Como la tabla Reserva_Pagos no tiene un ID único por pago,
        // actualizar un pago específico es difícil.
        // Aquí actualizamos por fecha y reserva (asumiendo un pago por día).
        $sql = "UPDATE Reserva_Pagos SET 
                    Cantidad = :amount
                WHERE IdReserva = :reservationId AND FechaPago = :paymentDate";

        $stmt = $this->pdo->prepare($sql);
        $date = date('Y-m-d', $payment->getPaymentDate()->getValue());

        $stmt->execute([
            ':reservationId' => $payment->getReservationId()->getValue(),
            ':amount'        => $payment->getAmount()->getValue(),
            ':paymentDate'   => $date
        ]);
    }

    public function deleteReservationPayment(Identifier $reservationId): void
    {
        // Borra TODOS los pagos de una reserva
        $stmt = $this->pdo->prepare("DELETE FROM Reserva_Pagos WHERE IdReserva = :id");
        $stmt->execute([':id' => $reservationId->getValue()]);
    }

    public function getReservationPayments(): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, Cantidad, FechaPago FROM Reserva_Pagos");
        $stmt->execute();
        return $this->mapRows($stmt->fetchAll());
    }

    public function getPaymentsByReservation(Identifier $reservationId): array
    {
        $stmt = $this->pdo->prepare("SELECT IdReserva, Cantidad, FechaPago FROM Reserva_Pagos WHERE IdReserva = :id");
        $stmt->execute([':id' => $reservationId->getValue()]);
        return $this->mapRows($stmt->fetchAll());
    }

    private function mapRows(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $dateInt = strtotime($row['FechaPago']);

            $result[] = new ReadReservationPayment(
                new Identifier($row['IdReserva']),
                new Price((float)$row['Cantidad']),
                new TimeStamp((int)$dateInt)
            );
        }
        return $result;
    }
}