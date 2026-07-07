<?php

namespace App\Services;

use App\Jobs\SendWhatsappMessageJob;
use App\Models\Attendance;
use App\Models\DriverProfile;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\FinancialLedgerEntry;
use App\Models\ClientProfile;
use App\Models\User;
use App\Models\HandoverRequest;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Create a new order with calculated pricing and logs.
     *
     * Expects $data keys: client_profile_id, driver_id (user id, optional),
     * order_description, payment_type, delivery_on_customer,
     * delivery_customer_amount (if delivery_on_customer), order_price (if cod),
     * receiver_name, receiver_phone, city_id, area_id, address_text, notes.
     */
    public function createOrder(array $data, User $actor): Order
    {
        // $attempts > 1 lets Laravel automatically retry the whole transaction
        // if two concurrent order creations deadlock while reserving the next
        // order_number sequence for the same client/day.
        return DB::transaction(function () use ($data, $actor) {
            $client = ClientProfile::findOrFail($data['client_profile_id']);

            // Calculate delivery fee for the client based on city rates
            $clientDeliveryAmount = $client->getDeliveryPriceForCity((int) $data['city_id']);

            $deliveryOnCustomer = filter_var($data['delivery_on_customer'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $customerDeliveryAmount = $deliveryOnCustomer ? (float) ($data['delivery_customer_amount'] ?? 0) : null;

            // Resolve driver profile from user id
            $driverProfileId = null;
            if (!empty($data['driver_id'])) {
                $driverProfile = DriverProfile::where('user_id', $data['driver_id'])->first();
                $driverProfileId = $driverProfile?->id;
            }

            $hasDriver = $driverProfileId !== null;
            $initialStatus = $hasDriver ? 'assigned' : 'pending';

            $order = Order::create([
                'client_profile_id' => $client->id,
                'driver_profile_id' => $driverProfileId,
                'order_description' => $data['order_description'] ?? null,
                'status'            => $initialStatus,
                'payment_status'    => 'pending',
                'notes'             => $data['notes'] ?? null,
                'batch_number'      => $data['batch_number'] ?? null,
                'delivery_shift'    => $data['delivery_shift'] ?? 'doesnt_matter',
            ]);

            $order->payment()->create([
                'payment_type'             => $data['payment_type'],
                'order_amount'             => $data['payment_type'] === 'cod' ? (float) ($data['order_price'] ?? 0) : null,
                'delivery_on_customer'     => $deliveryOnCustomer,
                'customer_delivery_amount' => $customerDeliveryAmount,
                'client_delivery_amount'   => $clientDeliveryAmount,
            ]);

            $order->receiver()->create([
                'receiver_name' => $data['receiver_name'],
                'receiver_phone' => $data['receiver_phone'],
                'city_id'        => (int) $data['city_id'],
                'area_id'        => (int) $data['area_id'],
                'address_text'   => $data['address_text'],
            ]);

            $this->logTracking($order->id, $actor->id, null, 'pending', 'Order created in the system.');

            if ($hasDriver) {
                $driverName = $driverProfile?->user?->name ?? 'Driver';
                $this->logTracking($order->id, $actor->id, 'pending', 'assigned', "Order assigned to driver: {$driverName}.");
            }

            // Use data array directly — avoids loading the receiver relation for the job
            $driverUser = $driverProfile?->user ?? null;
            SendWhatsappMessageJob::dispatch(
                'order_created',
                $data['receiver_phone'],
                [
                    'customer_name' => $data['receiver_name'] ?? '',
                    'order_number'  => $order->order_number ?? '',
                    'driver_name'   => $driverUser?->name  ?? '',
                    'driver_phone'  => $driverUser?->phone ?? '',
                    'location_link' => rescue(fn () => route('public.share-location', ['order_number' => $order->order_number]), ''),
                ],
                $order->id,
            )->onQueue(config('whatsapp.queue', 'default'));

            return $order;
        }, 5);
    }

    /**
     * Update order status with tracking logs and financial transactions.
     *
     * $extra may contain: driver_id (user id), signature_path, proof_image_path,
     * rejection_reason_id, notes.
     */
    public function updateStatus(Order $order, string $newStatus, array $extra = [], User $actor): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $extra, $actor) {
            $oldStatus = $order->status;

            if ($oldStatus === $newStatus && !isset($extra['driver_id'])) {
                return $order;
            }

            $order->status = $newStatus;

            // Handle driver assignment (extra['driver_id'] is a user id)
            if (isset($extra['driver_id'])) {
                $newDriverProfile = DriverProfile::where('user_id', $extra['driver_id'])->first();
                $newDriverProfileId = $newDriverProfile?->id;

                if ($newDriverProfileId != $order->driver_profile_id) {
                    $order->driver_profile_id = $newDriverProfileId;

                    $newDriverName = $newDriverProfile?->user?->name ?? 'None';
                    $this->logTracking(
                        $order->id,
                        $actor->id,
                        $oldStatus,
                        $newStatus,
                        "Order driver changed to: {$newDriverName}."
                    );
                }
            }

            $driverUserId = $order->driverProfile?->user_id;

            if ($newStatus === 'delivered') {
                $order->delivered_at     = now();
                $order->signature_path   = $extra['signature_path'] ?? null;
                $order->proof_image_path = $extra['proof_image_path'] ?? null;
                $order->national_id_attachment_path = $extra['national_id_attachment_path'] ?? $order->national_id_attachment_path;
                $order->rejection_reason_id = null;

                $payment = $order->payment;

                if ($payment->payment_type === 'cod') {
                    $order->payment_status = 'with_driver';
                } elseif ($payment->delivery_on_customer) {
                    $order->payment_status = 'with_driver';
                } else {
                    $order->payment_status = 'no_payment';
                }

                if ($payment->payment_type === 'cod' && $payment->order_amount > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'customer',
                        'to_account'        => 'driver',
                        'amount'            => $payment->order_amount,
                        'type'              => 'cod_collection',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'COD goods price collected by driver',
                    ]);
                }

                if ($payment->delivery_on_customer && $payment->customer_delivery_amount > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'customer',
                        'to_account'        => 'driver',
                        'amount'            => $payment->customer_delivery_amount,
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
                $order->returned_at = now();
                if ($oldStatus === 'picked_up') {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'client',
                        'to_account'        => 'company',
                        'amount'            => $order->payment->client_delivery_amount,
                        'type'              => 'shipping_charge',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery charge for returned order ' . $order->order_number,
                    ]);
                }
                $this->logTracking($order->id, $actor->id, $oldStatus, 'returned', 'Order returned to hub/client.');

            } elseif ($newStatus === 'cancelled') {
                $this->logTracking($order->id, $actor->id, $oldStatus, 'cancelled', 'Order cancelled.');
            } else {
                $this->logTracking($order->id, $actor->id, $oldStatus, $newStatus, "Order status changed to {$newStatus}.");
            }

            $order->save();

            if (in_array($newStatus, ['delivered', 'rejected', 'picked_up'])) {
                rescue(fn () => app(SupportNotificationService::class)->notifyClientOrderStatusChanged($order, $newStatus, $actor->id));
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
            $driverProfile = DriverProfile::where('user_id', $driver->id)->first();

            $count = 0;
            $orders = Order::whereIn('id', $orderIds)
                ->where('driver_profile_id', $driverProfile?->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'with_driver')
                ->with('payment')
                ->get();

            foreach ($orders as $order) {
                $driverCollected = FinancialLedgerEntry::where('order_id', $order->id)
                    ->where('driver_id', $driver->id)
                    ->where('to_account', 'driver')
                    ->sum('amount');

                if ($driverCollected > 0) {
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

                    $payment = $order->payment;
                    if ($payment->delivery_on_customer) {
                        $order->payment_status = 'with_company';
                    } elseif ($payment->payment_type === 'prepaid' || $payment->order_amount == 0) {
                        $order->payment_status = 'paid';
                    } else {
                        $order->payment_status = 'with_company';
                    }
                    $order->save();

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
    public function payoutClient(ClientProfile $client, array $orderIds, User $actor, ?string $ref = null, ?string $notes = null, ?string $attachmentPath = null): int
    {
        $ref = $ref ?: 'PAY-' . now()->format('YmdHis') . '-C' . $client->id;

        return DB::transaction(function () use ($client, $orderIds, $actor, $ref, $notes, $attachmentPath) {
            $count = 0;
            $orders = Order::whereIn('id', $orderIds)
                ->where('client_profile_id', $client->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'with_company')
                ->with('payment')
                ->get();

            $totalCod = 0;
            $totalShipping = 0;
            $totalCustomerDelivery = 0;
            $payoutLedgerEntry = null;

            foreach ($orders as $order) {
                $payment = $order->payment;
                $driverUserId = $order->driverProfile?->user_id;

                $shippingFee = (float) ($payment->client_delivery_amount ?? 0);
                if ($shippingFee > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $client->id,
                        'driver_id'         => $driverUserId,
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

                if ($payment->payment_type === 'cod' && $payment->order_amount > 0) {
                    $customerDelivery = $payment->delivery_on_customer ? (float) ($payment->customer_delivery_amount ?? 0) : 0;
                    $ledger = FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $client->id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'company',
                        'to_account'        => 'client',
                        'amount'            => $payment->order_amount + $customerDelivery,
                        'type'              => 'client_payout',
                        'reference_number'  => $ref,
                        'recorded_by'       => $actor->id,
                        'notes'             => $notes ?? 'COD payout to client for order ' . $order->order_number,
                    ]);

                    if (!$payoutLedgerEntry) {
                        $payoutLedgerEntry = $ledger;
                    }

                    $totalCod += $payment->order_amount;
                    $totalCustomerDelivery += $customerDelivery;
                } elseif ($payment->delivery_on_customer && ($payment->customer_delivery_amount ?? 0) > 0) {
                    $custDel = (float) $payment->customer_delivery_amount;
                    $ledger = FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $client->id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'company',
                        'to_account'        => 'client',
                        'amount'            => $custDel,
                        'type'              => 'client_payout',
                        'reference_number'  => $ref,
                        'recorded_by'       => $actor->id,
                        'notes'             => $notes ?? 'Delivery fee payout to client for order ' . $order->order_number,
                    ]);

                    if (!$payoutLedgerEntry) {
                        $payoutLedgerEntry = $ledger;
                    }

                    $totalCustomerDelivery += $custDel;
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
                $invoice = \App\Models\Invoice::create([
                    'invoice_number'         => $invoiceNumber,
                    'client_profile_id'      => $client->id,
                    'payout_ledger_entry_id' => $payoutLedgerEntry->id,
                    'total_orders'           => $count,
                    'cod_amount'                  => $totalCod,
                    'shipping_amount'             => $totalShipping,
                    'customer_delivery_amount'    => $totalCustomerDelivery,
                    'net_amount'                  => $totalCod + $totalCustomerDelivery - $totalShipping,
                    'status'                 => 'paid',
                    'notes'                  => $notes ?? 'Auto-generated invoice for client payout.',
                    'attachment_path'        => $attachmentPath,
                ]);

                // Notify client users about the payout
                rescue(fn () => app(SupportNotificationService::class)->notifyClientPayoutMade($invoice, $actor->id));
            }

            return $count;
        });
    }

    /**
     * Submit a handover request for approval (bulk-return rejected and settle delivered COD cash).
     */
    public function confirmHandover(User $driver, ?string $notes = null, ?string $location = null, ?string $paymentMethod = null, ?string $proofImagePath = null): array
    {
        return DB::transaction(function () use ($driver, $notes, $location, $paymentMethod, $proofImagePath) {
            $driverProfile = DriverProfile::where('user_id', $driver->id)->firstOrFail();

            $rejectedOrders = Order::where('driver_profile_id', $driverProfile->id)
                ->where('status', 'rejected')
                ->whereNull('handover_request_id')
                ->get();

            $deliveredOrders = Order::where('driver_profile_id', $driverProfile->id)
                ->where('status', 'delivered')
                ->where('payment_status', 'with_driver')
                ->whereNull('handover_request_id')
                ->get();

            $handoverRequest = null;

            if ($rejectedOrders->isNotEmpty() || $deliveredOrders->isNotEmpty()) {
                $handoverRequest = HandoverRequest::create([
                    'driver_id'         => $driver->id,
                    'status'            => 'pending',
                    'notes'             => $notes,
                    'payment_method'    => $paymentMethod,
                    'proof_image_path'  => $proofImagePath,
                ]);

                foreach ($rejectedOrders as $order) {
                    $order->update(['handover_request_id' => $handoverRequest->id]);
                }

                foreach ($deliveredOrders as $order) {
                    $order->update(['handover_request_id' => $handoverRequest->id]);
                }

                // Send notification to admins
                app(SupportNotificationService::class)->notifyAdminsNewHandoverRequest($handoverRequest);
            }

            // Handover marks the end of the shift, so close out today's open attendance session here too.
            $attendance = Attendance::where('user_id', $driver->id)
                ->whereDate('date', now()->toDateString())
                ->whereNull('check_out_at')
                ->latest('check_in_at')
                ->first();

            if ($attendance) {
                $attendance->update([
                    'check_out_at'       => now(),
                    'check_out_location' => $location,
                ]);
            }

            return [
                'returned'    => $rejectedOrders->count(),
                'settled'     => $deliveredOrders->count(),
                'checked_out' => (bool) $attendance,
            ];
        });
    }

    /**
     * Approve a handover request: perform cash settlement and return rejected orders.
     */
    public function approveHandover(HandoverRequest $handoverRequest, User $actor): void
    {
        DB::transaction(function () use ($handoverRequest, $actor) {
            if ($handoverRequest->status !== 'pending') {
                throw new \Exception("Only pending handover requests can be approved.");
            }

            $deliveredOrders = $handoverRequest->orders()
                ->where('status', 'delivered')
                ->where('payment_status', 'with_driver')
                ->get();

            foreach ($deliveredOrders as $order) {
                $driverCollected = FinancialLedgerEntry::where('order_id', $order->id)
                    ->where('driver_id', $handoverRequest->driver_id)
                    ->where('to_account', 'driver')
                    ->sum('amount');

                if ($driverCollected > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $handoverRequest->driver_id,
                        'from_account'      => 'driver',
                        'to_account'        => 'company',
                        'amount'            => $driverCollected,
                        'type'              => 'driver_settlement',
                        'recorded_by'       => $actor->id,
                        'notes'             => ($handoverRequest->notes ?? 'Driver confirmed cash handover to Saee') . ' — order ' . $order->order_number,
                    ]);
                }

                $order->payment_status = 'with_company';
                $order->save();

                $this->logTracking(
                    $order->id,
                    $actor->id,
                    $order->status,
                    $order->status,
                    'Driver cash transfer approved by admin. Amount: ' . $driverCollected . '.'
                );
            }

            $rejectedOrders = $handoverRequest->orders()
                ->where('status', 'rejected')
                ->with('payment')
                ->get();

            foreach ($rejectedOrders as $order) {
                $order->update([
                    'status'         => 'returned',
                    'payment_status' => 'no_payment',
                    'returned_at'    => now(),
                ]);

                $clientDeliveryAmount = $order->payment?->client_delivery_amount ?? 0;
                if ($clientDeliveryAmount > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $handoverRequest->driver_id,
                        'from_account'      => 'client',
                        'to_account'        => 'company',
                        'amount'            => $clientDeliveryAmount,
                        'type'              => 'shipping_charge',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery charge for returned order ' . $order->order_number,
                    ]);
                }

                $description = 'Driver handover approved by admin — order returned to client.';
                if ($handoverRequest->notes) {
                    $description .= " Notes: {$handoverRequest->notes}";
                }

                $this->logTracking($order->id, $actor->id, 'rejected', 'returned', $description);
            }

            $handoverRequest->update([
                'status'      => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
        });
    }

    private function logTracking(int $orderId, int $userId, ?string $fromStatus, string $toStatus, string $description): OrderTrackingLog
    {
        return OrderTrackingLog::create([
            'order_id'    => $orderId,
            'user_id'     => $userId,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'description' => $description,
            'latitude'    => request()->input('latitude'),
            'longitude'   => request()->input('longitude'),
        ]);
    }
}
