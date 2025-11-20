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
     * 1. CAJA REAL: Dinero que entró efectivamente (Efectivo/Bancos)
     */
    public function getCashFlow(string $start = null, string $end = null): float
    {
        if (!$start || !$end) { $start = date('Y-m-01'); $end = date('Y-m-t'); }
        
        $sql = "SELECT SUM(Cantidad) FROM Reserva_Pagos WHERE FechaPago BETWEEN :start AND :end";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    /**
     * 2. VENTAS CONSUMOS: Lo que se vendió en productos (pagado o no)
     */
    public function getConsumptionSales(string $start = null, string $end = null): float
    {
        if (!$start || !$end) { $start = date('Y-m-01'); $end = date('Y-m-t'); }

        // Sumamos Cantidad * Precio Actual del Producto
        $sql = "SELECT SUM(rp.Cantidad * p.Precio) 
                FROM Reserva_Productos rp
                JOIN Productos p ON rp.IdProducto = p.Id
                WHERE rp.FechaConsumo BETWEEN :start AND :end";
                
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    /**
     * 3. VENTAS ALOJAMIENTO: Lo que produjeron las habitaciones
     */
    public function getAccommodationSales(string $start = null, string $end = null): float
    {
        if (!$start || !$end) { $start = date('Y-m-01'); $end = date('Y-m-t'); }

        // Calculamos: Precio Habitación * Noches
        // Solo contamos reservas creadas o activas en este rango para simplificar el reporte
        // Usamos la fecha de inicio de la reserva como referencia de venta
        $sql = "SELECT SUM(h.Precio * DATEDIFF(rh.FechaFin, rh.FechaInicio)) 
                FROM Reserva_Habitacion rh
                JOIN Habitaciones h ON rh.IdHabitacion = h.Id
                WHERE rh.FechaInicio BETWEEN :start AND :end";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        return (float) ($stmt->fetchColumn() ?: 0);
    }

    // ... (Métodos auxiliares para gráficos y tablas se mantienen igual) ...
    public function getRoomStats(): array
    {
        $sql = "SELECT Estado, COUNT(*) as Total FROM Habitaciones GROUP BY Estado";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getRecentPayments(string $start = null, string $end = null): array
    {
        if (!$start || !$end) { $start = date('Y-m-01'); $end = date('Y-m-t'); }
        $sql = "SELECT p.*, (SELECT h.Nombre FROM Reserva_Habitacion rh JOIN Habitaciones h ON rh.IdHabitacion = h.Id WHERE rh.IdReserva = p.IdReserva LIMIT 1) as Habitacion 
                FROM Reserva_Pagos p WHERE p.FechaPago BETWEEN :start AND :end ORDER BY p.FechaPago DESC LIMIT 20";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':start' => $start, ':end' => $end]);
        return $stmt->fetchAll();
    }
}