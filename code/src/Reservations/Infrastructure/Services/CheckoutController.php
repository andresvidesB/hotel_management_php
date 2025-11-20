<?php
declare(strict_types=1);

namespace Src\Reservations\Infrastructure\Services;

use Src\Shared\Domain\ValueObjects\Identifier;
use Src\Reservations\Infrastructure\Repositories\MySqlReservationsRepository;
use Src\ReservationRooms\Infrastructure\Repositories\MySqlReservationRoomsRepository;
use Src\ReservationProducts\Infrastructure\Repositories\MySqlReservationProductsRepository;
use Src\ReservationPayments\Infrastructure\Repositories\MySqlReservationPaymentsRepository;
use Src\Rooms\Infrastructure\Repositories\MySqlRoomsRepository;
use Src\Products\Infrastructure\Repositories\MySqlProductsRepository;
use Src\ReservationStatus\Infrastructure\Repositories\MySqlReservationStatusRepository;
use Src\ReservationStatus\Domain\Entities\WriteReservationStatus;
use Src\Shared\Domain\ValueObjects\TimeStamp;
use Src\Rooms\Domain\ValueObjects\RoomState;

final class CheckoutController
{
    public static function calculateAccount(string $reservationId): array
    {
        $id = new Identifier($reservationId);

        // 1. Costo Habitación
        $repoResRooms = new MySqlReservationRoomsRepository();
        $repoRooms = new MySqlRoomsRepository();
        
        $rooms = $repoResRooms->getRoomsByReservation($id);
        $roomTotal = 0;
        $roomDetails = [];

        foreach ($rooms as $resRoom) {
            $roomData = $repoRooms->getRoomById($resRoom->getRoomId());
            $start = $resRoom->getStartDate()->getValue();
            $end = $resRoom->getEndDate()->getValue();
            $nights = ceil(($end - $start) / 86400); 
            if ($nights < 1) $nights = 1; 
            
            $price = $roomData->getPrice()->getValue();
            $subtotal = $price * $nights;
            
            $roomTotal += $subtotal;
            $roomDetails[] = [
                'room_name' => $roomData->getName()->getValue(),
                'price' => $price,
                'nights' => $nights,
                'subtotal' => $subtotal
            ];
        }

        // 2. Costo Consumos (TODOS, pagados y no pagados)
        $repoResProd = new MySqlReservationProductsRepository();
        $repoProds = new MySqlProductsRepository();
        
        $products = $repoResProd->getProductsByReservation($id);
        $productsTotal = 0;
        $productsDetails = [];

        foreach ($products as $resProd) {
            // NOTA: Ya NO filtramos los pagados. Los incluimos todos para que la factura sea completa.
            // El pago ya está registrado en la tabla de Pagos, así que el saldo se ajustará solo.
            
            $prodIdStr = $resProd['reservation_product_product_id'];
            $qty = (int) $resProd['reservation_product_quantity'];
            $isPaid = !empty($resProd['is_paid']);

            $prodData = $repoProds->getProductById(new Identifier($prodIdStr));
            
            if ($prodData) {
                $price = $prodData->getPrice()->getValue();
                $subtotal = $price * $qty;
                
                $productsTotal += $subtotal;
                $productsDetails[] = [
                    'product_name' => $prodData->getName()->getValue(),
                    'price' => $price,
                    'quantity' => $qty,
                    'subtotal' => $subtotal,
                    'status' => $isPaid ? 'Pagado' : 'A Cuenta' // Para mostrar en la factura
                ];
            }
        }

        // 3. Pagos Realizados (Incluye abonos y pagos de consumo inmediato)
        $repoPagos = new MySqlReservationPaymentsRepository();
        $pagos = $repoPagos->getPaymentsByReservation($id);
        $paidTotal = 0;
        $paymentsDetails = [];
        
        foreach ($pagos as $pago) {
            $amount = $pago->getAmount()->getValue();
            $paidTotal += $amount;
            $paymentsDetails[] = [
                'date' => date('d/m/Y', $pago->getPaymentDate()->getValue()),
                'method' => $pago->getMethod(),
                'amount' => $amount
            ];
        }

        // 4. Resumen Final
        $grandTotal = $roomTotal + $productsTotal;
        $balance = $grandTotal - $paidTotal;
        if (abs($balance) < 0.01) $balance = 0;

        return [
            'room_total' => $roomTotal,
            'room_details' => $roomDetails,
            'products_total' => $productsTotal,
            'products_details' => $productsDetails,
            'payments_details' => $paymentsDetails, // Lista detallada de pagos
            'paid_total' => $paidTotal,
            'grand_total' => $grandTotal,
            'balance' => $balance, 
            'is_paid' => $balance <= 0
        ];
    }

    public static function finalizeCheckOut(string $reservationId): void
    {
        $id = new Identifier($reservationId);
        $repoStatus = new MySqlReservationStatusRepository();
        
        // Estado Finalizada (40)
        $repoStatus->addReservationStatus(new WriteReservationStatus(
            $id, new Identifier('40'), new TimeStamp(time())
        ));

        // Liberar habitación (Limpieza)
        $repoResRooms = new MySqlReservationRoomsRepository();
        $repoRooms = new MySqlRoomsRepository();
        $rooms = $repoResRooms->getRoomsByReservation($id);
        
        foreach ($rooms as $resRoom) {
            $repoRooms->updateRoomState($resRoom->getRoomId(), new RoomState('Limpieza'));
        }
    }
}