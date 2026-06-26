<?php

namespace App\Services;

use App\Enums\DeliveryInvoiceStatus;
use App\Models\ClientDeliveryInvoice;
use App\Models\ClientProfile;
use App\Models\FinancialLedgerEntry;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ClientDeliveryBillingService
{
    /**
     * Generate a draft delivery fee invoice for orders delivered in the period
     * that have not yet been billed (delivery_on_customer = false, not in any invoice).
     */
    public function generateDraftInvoice(
        ClientProfile $client,
        Carbon $periodStart,
        Carbon $periodEnd,
        User $actor
    ): ClientDeliveryInvoice {
        return DB::transaction(function () use ($client, $periodStart, $periodEnd, $actor) {
            $alreadyBilledIds = DB::table('client_delivery_invoice_orders')->pluck('order_id');

            $start = $periodStart->startOfDay();
            $end   = $periodEnd->endOfDay();

            // Returned order IDs that have a shipping_charge ledger entry (driver attempted delivery)
            $returnedWithChargeIds = FinancialLedgerEntry::where('client_profile_id', $client->id)
                ->where('type', 'shipping_charge')
                ->pluck('order_id');

            $orders = Order::where('client_profile_id', $client->id)
                ->whereNotIn('id', $alreadyBilledIds)
                ->where(function ($q) use ($start, $end, $returnedWithChargeIds) {
                    $q->where(function ($q) use ($start, $end) {
                        $q->where('status', 'delivered')
                          ->whereBetween('delivered_at', [$start, $end]);
                    })->orWhere(function ($q) use ($start, $end, $returnedWithChargeIds) {
                        $q->where('status', 'returned')
                          ->whereBetween('returned_at', [$start, $end])
                          ->whereIn('id', $returnedWithChargeIds);
                    });
                })
                ->with('payment')
                ->get()
                ->filter(fn($o) =>
                    $o->payment && $o->payment->client_delivery_amount > 0 &&
                    ($o->status === 'returned' || ! $o->payment->delivery_on_customer)
                );

            $deliveryAmount = $orders->sum(fn($o) => (float) $o->payment->client_delivery_amount);
            $net = max(0, $deliveryAmount);

            $invoice = ClientDeliveryInvoice::create([
                'invoice_number'    => $this->generateInvoiceNumber(),
                'client_profile_id' => $client->id,
                'period_start'      => $periodStart->toDateString(),
                'period_end'        => $periodEnd->toDateString(),
                'total_orders'      => $orders->count(),
                'billable_orders'   => $orders->count(),
                'delivery_amount'   => $deliveryAmount,
                'discount_amount'   => 0,
                'net_amount'        => $net,
                'status'            => DeliveryInvoiceStatus::Draft,
                'created_by'        => $actor->id,
            ]);

            if ($orders->isNotEmpty()) {
                $invoice->orders()->attach($orders->pluck('id'));
            }

            return $invoice;
        });
    }

    public function issueInvoice(
        ClientDeliveryInvoice $invoice,
        ?Carbon $dueDate,
        User $actor
    ): ClientDeliveryInvoice {
        $invoice->update([
            'status'   => DeliveryInvoiceStatus::Issued,
            'due_date' => $dueDate?->toDateString(),
        ]);

        return $invoice->fresh();
    }

    public function recordPayment(
        ClientDeliveryInvoice $invoice,
        string $paymentMethod,
        ?string $reference,
        User $actor
    ): ClientDeliveryInvoice {
        $invoice->update([
            'status'           => DeliveryInvoiceStatus::Paid,
            'payment_method'   => $paymentMethod,
            'reference_number' => $reference,
            'paid_at'          => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Mark issued invoices past their due date as overdue.
     * Intended for a scheduled artisan command.
     */
    public function markOverdueInvoices(): int
    {
        return ClientDeliveryInvoice::where('status', DeliveryInvoiceStatus::Issued)
            ->whereNotNull('due_date')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => DeliveryInvoiceStatus::Overdue]);
    }

    private function generateInvoiceNumber(): string
    {
        $seq = (ClientDeliveryInvoice::max('id') ?? 0) + 1;
        return 'DLV-' . now()->format('Ymd') . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
