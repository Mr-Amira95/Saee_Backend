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

            return $order;
        }, 5);
    }

    /**
     * Update order status with tracking logs and financial transactions.
     *
     * $extra may contain: driver_id (user id), signature_path, proof_image_path,
     * rejection_reason_id, cancellation_reason, notes.
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
                $order->delivery_latitude  = $extra['delivery_latitude'] ?? null;
                $order->delivery_longitude = $extra['delivery_longitude'] ?? null;
                $order->rejection_reason_id = null;
                $order->rejection_fee_status = null;

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

                // Only a driver can have collected the customer's delivery fee
                $feeCollected = $driverUserId && ($extra['delivery_fee_collected'] ?? false);
                $order->rejection_fee_status = $this->rejectionFeeStatus($order, (bool) $feeCollected);

                $reasonText = $order->rejectionReason ? $order->rejectionReason->reason : 'Not specified';
                $description = "Order rejected. Reason: {$reasonText}. Notes: " . ($extra['notes'] ?? '');

                if ($order->rejection_fee_status === Order::REJECTION_FEE_COLLECTED) {
                    $fee = $order->customerDeliveryFee();
                    $order->payment_status = 'with_driver';

                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'customer',
                        'to_account'        => 'driver',
                        'amount'            => $fee,
                        'type'              => 'delivery_collection',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery fee collected from customer on rejected order',
                    ]);

                    $description .= " Delivery fee of {$fee} collected from the customer.";
                }

                $this->logTracking($order->id, $actor->id, $oldStatus, 'rejected', $description);

            } elseif ($newStatus === 'in_office') {
                $order->return_driver_profile_id = null;

                $this->logTracking($order->id, $actor->id, $oldStatus, 'in_office', 'Order is at the office, waiting to be returned to the client.');

            } elseif ($newStatus === 'returned') {
                $order->returned_at = now();
                $order->return_confirmed_at = null;
                $order->return_confirmed_by = null;
                $order->rejection_fee_status ??= $this->rejectionFeeStatus($order, false);

                // Keep the status if the order is on a client statement, or the driver still holds its cash
                if (! in_array($order->payment_status, ['exported', 'paid', 'with_driver'], true)) {
                    $order->payment_status = 'no_payment';
                }

                $clientDeliveryAmount = (float) ($order->payment?->client_delivery_amount ?? 0);
                $alreadyCharged = FinancialLedgerEntry::where('order_id', $order->id)->where('type', 'shipping_charge')->exists();

                if ($order->chargesClientDeliveryFee() && $clientDeliveryAmount > 0 && ! $alreadyCharged) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $order->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'client',
                        'to_account'        => 'company',
                        'amount'            => $clientDeliveryAmount,
                        'type'              => 'shipping_charge',
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery charge for returned order ' . $order->order_number,
                    ]);
                }

                $this->logTracking($order->id, $actor->id, $oldStatus, 'returned', 'Order returned to the client.');

            } elseif ($newStatus === 'cancelled') {
                $order->cancellation_reason = $extra['cancellation_reason'] ?? $order->cancellation_reason;

                $description = isset($extra['cancellation_reason'])
                    ? "Order cancelled. Reason: {$extra['cancellation_reason']}"
                    : 'Order cancelled.';
                $this->logTracking($order->id, $actor->id, $oldStatus, 'cancelled', $description);
            } else {
                $this->logTracking($order->id, $actor->id, $oldStatus, $newStatus, "Order status changed to {$newStatus}.");
            }

            $order->save();

            if (in_array($newStatus, ['delivered', 'rejected', 'picked_up', 'returned'])) {
                rescue(fn () => app(SupportNotificationService::class)->notifyClientOrderStatusChanged($order, $newStatus, $actor->id));
            }

            // The picked-up WhatsApp message is sent in a daily 8 AM batch
            // by the whatsapp:send-pickup-messages command.

            if ($newStatus === 'delivered') {
                $receiver = $order->receiver;
                SendWhatsappMessageJob::dispatch(
                    'delivered_order',
                    $receiver?->receiver_phone ?? '',
                    [
                        'cutomer_name' => $receiver?->receiver_name ?? '',
                        'order_number' => $order->order_number ?? '',
                    ],
                    $order->id,
                    [],
                    'ar',
                )->onQueue(config('whatsapp.queue', 'default'));
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
                ->whereIn('status', ['delivered', 'rejected'])
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

                    // Cash is now at Saee; the order becomes "paid" only once its client statement is paid
                    $order->payment_status = 'with_company';
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
     * Approve a handover request: settle the driver's cash and receive the
     * rejected orders at the office (in_office), ready to be assigned to a
     * driver who takes them back to the client.
     */
    public function approveHandover(HandoverRequest $handoverRequest, User $actor): void
    {
        DB::transaction(function () use ($handoverRequest, $actor) {
            if ($handoverRequest->status !== 'pending') {
                throw new \Exception("Only pending handover requests can be approved.");
            }

            // Delivered orders, plus rejected ones whose delivery fee the driver collected
            $cashOrders = $handoverRequest->orders()
                ->whereIn('status', ['delivered', 'rejected'])
                ->where('payment_status', 'with_driver')
                ->get();

            foreach ($cashOrders as $order) {
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
                ->get();

            foreach ($rejectedOrders as $order) {
                $order->update(['status' => 'in_office']);

                $description = 'Driver handover approved by admin — order received at the office.';
                if ($handoverRequest->notes) {
                    $description .= " Notes: {$handoverRequest->notes}";
                }

                $this->logTracking($order->id, $actor->id, 'rejected', 'in_office', $description);
            }

            $handoverRequest->update([
                'status'      => 'approved',
                'approved_by' => $actor->id,
                'approved_at' => now(),
            ]);
        });
    }

    /**
     * Assign (or re-assign) the driver who takes an in-office order back to
     * the client. The delivery driver (driver_profile_id) is left untouched.
     */
    public function assignReturnDriver(Order $order, User $driver, User $actor): Order
    {
        return DB::transaction(function () use ($order, $driver, $actor) {
            if (! in_array($order->status, ['in_office', 'out_for_return'], true)) {
                throw new \InvalidArgumentException('Only orders at the office can be assigned to a driver for return.');
            }

            $driverProfile = DriverProfile::where('user_id', $driver->id)->firstOrFail();
            $oldStatus = $order->status;

            if ($oldStatus === 'out_for_return' && $order->return_driver_profile_id === $driverProfile->id) {
                return $order;
            }

            $order->update([
                'status'                   => 'out_for_return',
                'return_driver_profile_id' => $driverProfile->id,
            ]);

            $this->logTracking($order->id, $actor->id, $oldStatus, 'out_for_return', "Order assigned to driver {$driver->name} to be returned to the client.");

            return $order;
        });
    }

    /**
     * The client confirms receiving a returned order.
     */
    public function confirmReturn(Order $order, User $actor): Order
    {
        return DB::transaction(function () use ($order, $actor) {
            if (! $order->awaitsReturnConfirmation()) {
                return $order;
            }

            $order->update([
                'return_confirmed_at' => now(),
                'return_confirmed_by' => $actor->id,
            ]);

            $this->logTracking($order->id, $actor->id, 'returned', 'returned', 'Client confirmed receiving the returned order.');

            rescue(fn () => app(SupportNotificationService::class)->notifyAdminsReturnConfirmed($order));

            return $order;
        });
    }

    /**
     * Who bears the delivery fee of a rejected order, from the client's
     * "rejected orders are payable" option and what the driver collected.
     */
    private function rejectionFeeStatus(Order $order, bool $collectedFromCustomer): string
    {
        if (! $order->clientProfile?->charge_rejected_orders) {
            return Order::REJECTION_FEE_NOT_CHARGED;
        }

        return $collectedFromCustomer && $order->customerDeliveryFee() > 0
            ? Order::REJECTION_FEE_COLLECTED
            : Order::REJECTION_FEE_CLIENT_CHARGED;
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
