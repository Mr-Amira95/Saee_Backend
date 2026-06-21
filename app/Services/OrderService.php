<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\FinancialLedgerEntry;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    /**
     * Create a new order with calculated pricing and logs.
     */
    public function createOrder(array $data, User $actor): Order
    {
        return DB::transaction(function () use ($data, $actor) {
            $client = ClientProfile::findOrFail($data['client_profile_id']);
            
            // Calculate internal delivery price
            $deliveryAmount = $client->getDeliveryPriceForCity((int)$data['city_id']);
            
            // Auto-calculate delivery customer amount if delivery is not on customer
            $deliveryOnCustomer = filter_var($data['delivery_on_customer'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $deliveryCustomerAmount = $deliveryOnCustomer ? (float)($data['delivery_customer_amount'] ?? 0) : null;
            
            $hasDriver = !empty($data['driver_id']);
            $initialStatus = $hasDriver ? 'picked_up' : 'pending';

            $order = Order::create([
                'client_profile_id'        => $client->id,
                'driver_id'                => $data['driver_id'] ?? null,
                'order_description'        => $data['order_description'] ?? null,
                'payment_type'             => $data['payment_type'],
                'delivery_on_customer'     => $deliveryOnCustomer,
                'delivery_customer_amount' => $deliveryCustomerAmount,
                'delivery_amount'          => $deliveryAmount,
                'order_price'              => $data['payment_type'] === 'cod' ? (float)$data['order_price'] : null,
                'receiver_name'            => $data['receiver_name'],
                'receiver_phone'           => $data['receiver_phone'],
                'city_id'                  => (int)$data['city_id'],
                'area_id'                  => (int)$data['area_id'],
                'address_text'             => $data['address_text'],
                'address_location'         => $data['address_location'] ?? null,
                'status'                   => $initialStatus,
                'payment_status'           => 'pending',
                'notes'                    => $data['notes'] ?? null,
            ]);

            // Create tracking log
            $this->logTracking($order->id, $actor->id, null, 'pending', 'Order created in the system.');

            if ($hasDriver) {
                $driver = User::find($order->driver_id);
                $driverName = $driver ? $driver->name : 'Driver';
                $this->logTracking($order->id, $actor->id, 'pending', 'picked_up', "Order assigned to driver: {$driverName} and picked up.");
            }

            // Send WhatsApp notification on order creation
            app(WhatsAppService::class)->sendNotification($order, 'order_created');

            return $order;
        });
    }

    /**
     * Update order status with tracking logs and financial transactions.
     */
    public function updateStatus(Order $order, string $newStatus, array $extra = [], User $actor): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $extra, $actor) {
            $oldStatus = $order->status;
            
            if ($oldStatus === $newStatus && !isset($extra['driver_id'])) {
                return $order; // No change
            }

            $order->status = $newStatus;

            // Handle Driver Assignment
            if (isset($extra['driver_id']) && $extra['driver_id'] != $order->driver_id) {
                $oldDriverId = $order->driver_id;
                $order->driver_id = $extra['driver_id'];
                
                $newDriver = User::find($order->driver_id);
                $newDriverName = $newDriver ? $newDriver->name : 'None';
                
                $this->logTracking(
                    $order->id, 
                    $actor->id, 
                    $oldStatus, 
                    $newStatus, 
                    "Order driver changed to: {$newDriverName}."
                );
            }

            // Handle specific status changes
            if ($newStatus === 'delivered') {
                $order->signature_path = $extra['signature_path'] ?? null;
                $order->proof_image_path = $extra['proof_image_path'] ?? null;
                $order->rejection_reason_id = null;
                
                // Determine payment status
                if ($order->payment_type === 'cod') {
                    $order->payment_status = 'with_driver';
                } elseif ($order->delivery_on_customer) {
                    $order->payment_status = 'with_driver';
                } else {
                    $order->payment_status = 'no_payment'; // Prepaid, delivery paid by client
                }

                // Record cash the driver physically collected from the customer
                // 1. COD Collection: Customer paid Driver for goods
                if ($order->payment_type === 'cod' && $order->order_price > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $order->driver_id,
                        'from_account'      => 'customer',
                        'to_account'        => 'driver',
                        'amount'            => $order->order_price,
                        'type'              => 'cod_collection',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'COD goods price collected by driver',
                    ]);
                }

                // 2. Delivery fee collected from customer (customer pays delivery, not client)
                if ($order->delivery_on_customer && $order->delivery_customer_amount > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $order->driver_id,
                        'from_account'      => 'customer',
                        'to_account'        => 'driver',
                        'amount'            => $order->delivery_customer_amount,
                        'type'              => 'delivery_collection',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery fee collected from customer by driver',
                    ]);
                }

                $this->logTracking($order->id, $actor->id, $oldStatus, 'delivered', 'Order marked as Delivered successfully.');

            } elseif ($newStatus === 'rejected') {
                $order->rejection_reason_id = $extra['rejection_reason_id'] ?? null;
                $order->notes = $extra['notes'] ?? $order->notes;
                
                $reasonText = $order->rejectionReason ? $order->rejectionReason->reason : 'Not specified';
                $this->logTracking($order->id, $actor->id, $oldStatus, 'rejected', "Order rejected. Reason: {$reasonText}. Notes: " . ($extra['notes'] ?? ''));

            } elseif ($newStatus === 'returned') {
                // If returned, we still charge client for shipping/return shipping fee (if company policy)
                // Let's log a shipping charge for returned orders if they had been picked up
                if ($oldStatus === 'picked_up') {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $order->driver_id,
                        'from_account'      => 'client',
                        'to_account'        => 'company',
                        'amount'            => $order->delivery_amount, // or return shipping fee
                        'type'              => 'shipping_charge',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery charge for returned order ' . $order->order_number,
                    ]);
                }
                $this->logTracking($order->id, $actor->id, $oldStatus, 'returned', 'Order returned to hub/client.');

            } elseif ($newStatus === 'cancelled') {
                $this->logTracking($order->id, $actor->id, $oldStatus, 'cancelled', 'Order cancelled.');
            } else {
                // General status logging (picked_up, etc.)
                $this->logTracking($order->id, $actor->id, $oldStatus, $newStatus, "Order status changed to {$newStatus}.");
            }

            $order->save();

            // Trigger WhatsApp notifications
            if ($newStatus === 'delivered') {
                app(WhatsAppService::class)->sendNotification($order, 'order_delivered');
            } elseif ($newStatus === 'rejected') {
                app(WhatsAppService::class)->sendNotification($order, 'order_rejected');
            }

            return $order;
        });
    }

    /**
     * Settle driver collected cash to the Company.
     */
    public function settleDriverCash(User $driver, array $orderIds, User $actor, ?string $ref = null, ?string $notes = null): int
    {
        return DB::transaction(function () use ($driver, $orderIds, $actor, $ref, $notes) {
            $count = 0;
            $orders = Order::whereIn('id', $orderIds)
                ->where('driver_id', $driver->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'with_driver')
                ->get();

            foreach ($orders as $order) {
                // Calculate driver collections for this order
                $driverCollected = FinancialLedgerEntry::where('order_id', $order->id)
                    ->where('driver_id', $driver->id)
                    ->where('to_account', 'driver')
                    ->sum('amount');

                if ($driverCollected > 0) {
                    // Log Driver -> Company Settlement
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $driver->id,
                        'from_account'      => 'driver',
                        'to_account'        => 'company',
                        'amount'            => $driverCollected,
                        'type'              => 'driver_settlement',
                        'reference_number'  => $ref,
                        'recorded_by'       => $actor->id,
                        'notes'             => $notes ?? 'Driver cash settled to company for order ' . $order->order_number,
                    ]);

                    // If order has no COD (or COD was prepaid), we mark it as paid.
                    // If it has COD, does the company still need to payout to the client?
                    // Yes. The company now has the cash. But the payment_status should remain 'with_driver' or change to 'paid'?
                    // The user said: payment_status: 'paid (driver or the company paid to the client)'.
                    // So once the company payouts the client, we set payment_status to 'paid'.
                    // If the order has NO COD (prepaid) but had delivery_on_customer, once driver settles to company, the payment is fully closed. So we set it to 'paid'.
                    if ($order->payment_type === 'prepaid' || $order->order_price == 0) {
                        $order->payment_status = 'paid';
                        $order->save();
                    }
                    
                    $this->logTracking(
                        $order->id, 
                        $actor->id, 
                        $order->status, 
                        $order->status, 
                        "Cash of {$driverCollected} settled from driver to company."
                    );
                    $count++;
                }
            }

            return $count;
        });
    }

    /**
     * Log company paying COD collections to the Client.
     */
    public function payoutClient(ClientProfile $client, array $orderIds, User $actor, ?string $ref = null, ?string $notes = null): int
    {
        return DB::transaction(function () use ($client, $orderIds, $actor, $ref, $notes) {
            $count = 0;
            $orders = Order::whereIn('id', $orderIds)
                ->where('client_profile_id', $client->id)
                ->where('status', 'delivered')
                ->whereIn('payment_status', ['with_driver', 'pending'])
                ->get();

            $totalCod = 0;
            $totalShipping = 0;
            $payoutLedgerEntry = null;

            foreach ($orders as $order) {
                // Shipping charge: client owes company for delivery (recorded here, not at delivery time)
                $shippingFee = $order->delivery_on_customer ? 0 : $order->delivery_amount;
                if ($shippingFee > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $client->id,
                        'driver_id'         => $order->driver_id,
                        'from_account'      => 'client',
                        'to_account'        => 'company',
                        'amount'            => $shippingFee,
                        'type'              => 'shipping_charge',
                        'reference_number'  => $ref,
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery charge for order ' . $order->order_number,
                    ]);
                    $totalShipping += $shippingFee;
                }

                // COD payout: company pays client their goods money (reverse of customer → driver COD collection)
                if ($order->payment_type === 'cod' && $order->order_price > 0) {
                    $ledger = FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $client->id,
                        'driver_id'         => $order->driver_id,
                        'from_account'      => 'company',
                        'to_account'        => 'client',
                        'amount'            => $order->order_price,
                        'type'              => 'client_payout',
                        'reference_number'  => $ref,
                        'recorded_by'       => $actor->id,
                        'notes'             => $notes ?? 'COD payout to client for order ' . $order->order_number,
                    ]);

                    if (!$payoutLedgerEntry) {
                        $payoutLedgerEntry = $ledger;
                    }

                    $totalCod += $order->order_price;
                }

                $order->payment_status = 'paid';
                $order->save();

                $this->logTracking(
                    $order->id,
                    $actor->id,
                    $order->status,
                    $order->status,
                    "Payout completed to client for order " . $order->order_number . "."
                );
                $count++;
            }

            if ($count > 0 && $payoutLedgerEntry) {
                $invoiceNumber = 'INV-' . now()->format('Ymd') . '-' . rand(1000, 9999);
                \App\Models\Invoice::create([
                    'invoice_number'         => $invoiceNumber,
                    'client_profile_id'      => $client->id,
                    'payout_ledger_entry_id' => $payoutLedgerEntry->id,
                    'total_orders'           => $count,
                    'cod_amount'             => $totalCod,
                    'shipping_amount'        => $totalShipping,
                    'net_amount'             => $totalCod - $totalShipping,
                    'status'                 => 'paid',
                    'notes'                  => $notes ?? 'Auto-generated invoice for client payout.',
                ]);
            }

            return $count;
        });
    }

    /**
     * Write tracking log helper.
     */
    private function logTracking(int $orderId, int $userId, ?string $fromStatus, string $toStatus, string $description): OrderTrackingLog
    {
        return OrderTrackingLog::create([
            'order_id'    => $orderId,
            'user_id'     => $userId,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'description' => $description,
            // GPS coords can be populated if available in request
            'latitude'    => request()->input('latitude'),
            'longitude'   => request()->input('longitude'),
        ]);
    }
}
