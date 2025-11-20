<?php
declare(strict_types=1);

namespace Src\Shared\Infrastructure\Services;

use Src\Shared\Infrastructure\Database;
use PDO;

final class ReportService
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = (new Database())->getConnection();
    }

    /**
     * Obtener Ingresos filtrados por fecha
     */
    public function getIncomeStats(string $start = null, string $end = null): array
    {
        // Si no hay fechas, usamos el mes actual por defecto
        if (!$start || !$end) {
            $start = date('Y-m-01');
            $end = date('Y-m-t');
        }

        // 1. Ingresos en el rango seleccionado
        $sqlRange = "SELECT SUM(Cantidad) FROM Reserva_Pagos WHERE FechaPago BETWEEN :start AND :end";
        $stmt = $this->pdo->prepare($sqlRange);
        $stmt->execute([':start' => $start, ':end' => $end]);
        $totalRange = $stmt->fetchColumn() ?: 0;

        // 2. Ingresos de HOY (Dato rápido comparativo)
        $sqlToday = "SELECT SUM(Cantidad) FROM Reserva_Pagos WHERE FechaPago = CURDATE()";
        $stmt = $this->pdo->query($sqlToday);
        $today = $stmt->fetchColumn() ?: 0;

        return [
            'range_total' => (float)$totalRange,
            'today' => (float)$today
        ];
    }

    /**
     * Estado actual de habitaciones (Snapshot actual, no depende de fechas)
     */
    public function getRoomStats(): array
    {
        $sql = "SELECT Estado, COUNT(*) as Total FROM Habitaciones GROUP BY Estado";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Movimientos filtrados por fecha
     */
    public function getRecentPayments(string $start = null, string $end = null): array
    {
        if (!$start || !$end) {
            $start = date('Y-m-01');
            $end = date('Y-m-t');
        }

        $sql = "SELECT 
                    p.FechaPago, 
                    p.Cantidad, 
                    p.MetodoPago,
                    p.IdReserva,
                    (SELECT h.Nombre FROM Reserva_Habitacion rh 
                     JOIN Habitaciones h ON rh.IdHabitacion = h.Id 
                     WHERE rh.IdReserva = p.IdReserva LIMIT 1) as Habitacion
                FROM Reserva_Pagos p
                WHERE p.FechaPago BETWEEN :start AND :end
                ORDER BY p.FechaPago DESC, p.IdReserva DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        return $stmt->fetchAll();
    }
}