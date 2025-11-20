<?php
declare(strict_types=1);

namespace Src\ReservationProducts\Infrastructure\Services;

use Src\ReservationProducts\Application\UseCases\DeleteReservationProduct;
use Src\ReservationProducts\Application\UseCases\GetReservationProducts;
use Src\ReservationProducts\Application\UseCases\GetProductsByReservation;
use Src\ReservationProducts\Infrastructure\Repositories\MySqlReservationProductsRepository;
use Src\Shared\Domain\ValueObjects\Identifier;
use Src\ReservationPayments\Infrastructure\Services\ReservationPaymentsController;
use Src\Products\Infrastructure\Services\ProductsController;

final class ReservationProductsController
{
    // ACTUALIZADO: Recibe $paymentMethod
    public static function addConsumption(string $resId, string $prodId, int $qty, bool $isPaid, string $paymentMethod = 'Efectivo'): void
    {
        // 1. Descontar Stock
        ProductsController::decreaseStock($prodId, $qty);
        
        // 2. Registrar Consumo (En la BD de productos queda marcado como 'Pagado' si aplica)
        self::repo()->addConsumption($resId, $prodId, $qty, date('Y-m-d'), $isPaid);
        
        // 3. Si es pago inmediato, registrar el ingreso en la CAJA con el método correcto
        if ($isPaid) {
            self::registerPayment($resId, $prodId, $qty, $paymentMethod);
        }
    }

    // ACTUALIZADO: Recibe $paymentMethod
    public static function payConsumption(string $resId, string $prodId, int $qty, string $paymentMethod = 'Efectivo'): void
    {
        // 1. Marcar como pagado en la BD
        self::repo()->markAsPaid($resId, $prodId);

        // 2. Registrar el dinero en Pagos
        self::registerPayment($resId, $prodId, $qty, $paymentMethod);
    }

    public static function deleteReservationProduct(string $resId, string $prodId): void
    {
        // 1. BUSCAR EL PRODUCTO ANTES DE BORRARLO (Para saber si estaba pagado)
        // Como el repositorio no tiene 'getOne', buscamos en la lista de la reserva
        $items = self::repo()->getProductsByReservation(new \Src\Shared\Domain\ValueObjects\Identifier($resId));
        $itemToDelete = null;
        
        foreach ($items as $item) {
            if ($item['reservation_product_product_id'] === $prodId) {
                $itemToDelete = $item;
                break;
            }
        }

        if ($itemToDelete) {
            // 2. SI ESTABA PAGADO, HACEMOS EL REVERSO DEL DINERO
            if (!empty($itemToDelete['is_paid'])) {
                // Calcular cuánto costó para devolverlo
                $prods = ProductsController::getProducts();
                $price = 0;
                foreach($prods as $p) { 
                    if($p['product_id'] == $prodId) { $price = $p['product_price']; break; } 
                }
                
                $qty = $itemToDelete['reservation_product_quantity'];
                $totalRefund = $price * $qty;

                if ($totalRefund > 0) {
                    // Registramos un pago NEGATIVO (Devolución)
                    \Src\ReservationPayments\Infrastructure\Services\ReservationPaymentsController::addReservationPayment([
                        'reservation_payment_reservation_id' => $resId,
                        'reservation_payment_amount' => -$totalRefund, // Negativo para restar
                        'reservation_payment_date' => date('Y-m-d'),
                        'reservation_payment_method' => 'Sistema (Anulación Consumo)'
                    ]);
                }
            }
        }

        // 3. AHORA SÍ LO BORRAMOS DE LA LISTA
        $useCase = new DeleteReservationProduct(self::repo());
        $useCase->execute(new Identifier($resId), new Identifier($prodId));
    }

    public static function getReservationProducts(): array
    {
        return self::repo()->getReservationProducts();
    }

    public static function getProductsByReservation(string $resId): array
    {
        return self::repo()->getProductsByReservation(new Identifier($resId));
    }

    private static function repo(): MySqlReservationProductsRepository
    {
        return new MySqlReservationProductsRepository();
    }

    // Helper actualizado para usar el método de pago real
    private static function registerPayment(string $resId, string $prodId, int $qty, string $method): void
    {
        $prods = ProductsController::getProducts();
        $price = 0;
        $name = 'Producto';
        foreach($prods as $p) { 
            if($p['product_id'] == $prodId) { 
                $price = $p['product_price']; 
                $name = $p['product_name'];
                break; 
            } 
        }
        
        $total = $price * $qty;
        if ($total > 0) {
            ReservationPaymentsController::addReservationPayment([
                'reservation_payment_reservation_id' => $resId,
                'reservation_payment_amount' => $total,
                'reservation_payment_date' => date('Y-m-d'),
                'reservation_payment_method' => $method . ' (Consumo: ' . $name . ')' // Guardamos el método elegido
            ]);
        }
    }
}