<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\ClientStatement;
use App\Models\FinancialLedgerEntry;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\User;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Client statements: one report per client with every finished order's
 * COD + delivery fee paid by the customer − Saee's delivery fee.
 *
 * Order lifecycle on the statement (orders.payment_status):
 *   ready     → delivered with the cash at Saee (with_company / no_payment),
 *               or returned / rejected (delivery fee only)
 *   exported  → included in an exported statement (can be exported again —
 *               the new statement supersedes the old one)
 *   paid      → the statement's payment was confirmed (never exported again)
 */
class ClientStatementService
{
    public const PAYMENT_STATUSES = ['ready', 'exported', 'paid'];

    /** Public-disk folder for statement files generated for the mobile API (see waybills:prune). */
    public const STORAGE_DIR = 'statements';

    public function __construct(private ArabicPdfRenderer $pdf)
    {
    }

    /**
     * Orders that belong on a client statement, narrowed by the page filters:
     * client_id, from, to, payment_status (ready|exported|paid), search.
     */
    public function ordersQuery(array $filters): Builder
    {
        $query = Order::query()
            ->where(function ($q) {
                $q->where(fn ($q) => $q->where('orders.status', 'delivered')
                        ->whereIn('orders.payment_status', ['with_company', 'no_payment', 'exported', 'paid']))
                    ->orWhere(fn ($q) => $q->whereIn('orders.status', ['returned', 'rejected'])
                        ->where('orders.payment_status', '!=', 'with_driver'));
            });

        if (! empty($filters['client_id'])) {
            $query->where('orders.client_profile_id', $filters['client_id']);
        }

        $completedAt = DB::raw('DATE(COALESCE(orders.delivered_at, orders.returned_at, orders.created_at))');
        if (! empty($filters['from'])) {
            $query->where($completedAt, '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where($completedAt, '<=', $filters['to']);
        }

        match ($filters['payment_status'] ?? null) {
            'ready'    => $query->whereNotIn('orders.payment_status', ['exported', 'paid']),
            'exported' => $query->where('orders.payment_status', 'exported'),
            'paid'     => $query->where('orders.payment_status', 'paid'),
            default    => null,
        };

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('orders.order_number', 'like', "%{$search}%")
                    ->orWhereHas('receiver', fn ($r) => $r->where('receiver_name', 'like', "%{$search}%")
                        ->orWhere('receiver_phone', 'like', "%{$search}%"))
                    ->orWhereHas('clientStatement', fn ($s) => $s->where('statement_number', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    /**
     * Sum of the statement columns over a query from ordersQuery().
     *
     * @return array{orders: int, cod: float, customer_delivery: float, client_delivery: float, net: float}
     */
    public function totals(Builder $query): array
    {
        $row = (clone $query)
            ->join('order_payments as op', 'op.order_id', '=', 'orders.id')
            ->selectRaw("
                COUNT(*) as orders_count,
                SUM(CASE WHEN orders.status = 'delivered' AND op.payment_type = 'cod' THEN COALESCE(op.order_amount, 0) ELSE 0 END) as cod,
                SUM(CASE WHEN orders.status = 'delivered' AND op.delivery_on_customer = 1 THEN COALESCE(op.customer_delivery_amount, 0) ELSE 0 END) as customer_delivery,
                SUM(COALESCE(op.client_delivery_amount, 0)) as client_delivery
            ")
            ->toBase()
            ->first();

        $cod            = (float) ($row->cod ?? 0);
        $customer       = (float) ($row->customer_delivery ?? 0);
        $clientDelivery = (float) ($row->client_delivery ?? 0);

        return [
            'orders'            => (int) ($row->orders_count ?? 0),
            'cod'               => $cod,
            'customer_delivery' => $customer,
            'client_delivery'   => $clientDelivery,
            'net'               => $cod + $customer - $clientDelivery,
        ];
    }

    /**
     * Statement amounts for one order. COD and the customer's delivery fee are
     * only collected on delivery; Saee's delivery fee is charged for returned
     * and rejected orders too.
     *
     * @return array{cod: float, customer_delivery: float, client_delivery: float, net: float}
     */
    public function amounts(Order $order): array
    {
        $payment   = $order->payment;
        $delivered = $order->status === 'delivered';

        $cod            = $delivered && $payment?->payment_type === 'cod' ? (float) ($payment->order_amount ?? 0) : 0.0;
        $customer       = $delivered && $payment?->delivery_on_customer ? (float) ($payment->customer_delivery_amount ?? 0) : 0.0;
        $clientDelivery = (float) ($payment?->client_delivery_amount ?? 0);

        return [
            'cod'               => $cod,
            'customer_delivery' => $customer,
            'client_delivery'   => $clientDelivery,
            'net'               => $cod + $customer - $clientDelivery,
        ];
    }

    /**
     * Export every ready / exported order matching the filters into a new
     * statement for one client. Paid orders are never exported again.
     * Previous statements that contained any of these orders are superseded;
     * their other orders go back to ready.
     */
    public function export(ClientProfile $client, array $filters, User $actor): ClientStatement
    {
        $filters['client_id'] = $client->id;

        if (($filters['payment_status'] ?? null) === 'paid') {
            throw ValidationException::withMessages(['export' => __('Paid orders cannot be exported again.')]);
        }

        return DB::transaction(function () use ($client, $filters, $actor) {
            $orders = $this->ordersQuery($filters)
                ->where('orders.payment_status', '!=', 'paid')
                ->with('payment')
                ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at)')
                ->lockForUpdate()
                ->get();

            if ($orders->isEmpty()) {
                throw ValidationException::withMessages(['export' => __('There are no orders to export for the selected filters.')]);
            }

            $orderIds = $orders->pluck('id');

            // Status each order had before it was first exported
            $previousStatuses = DB::table('client_statement_orders')
                ->join('orders', function ($join) {
                    $join->on('orders.id', '=', 'client_statement_orders.order_id')
                        ->on('orders.client_statement_id', '=', 'client_statement_orders.client_statement_id');
                })
                ->whereIn('client_statement_orders.order_id', $orderIds)
                ->pluck('client_statement_orders.previous_payment_status', 'client_statement_orders.order_id');

            $statement = ClientStatement::create([
                'statement_number'  => 'ST-' . now()->format('YmdHis') . '-' . $client->id,
                'client_profile_id' => $client->id,
                'date_from'         => $filters['from'] ?? null,
                'date_to'           => $filters['to'] ?? null,
                'status'            => 'exported',
                'exported_by'       => $actor->id,
            ]);
            $statement->update(['statement_number' => 'ST-' . now()->format('Ymd') . '-' . str_pad($statement->id, 5, '0', STR_PAD_LEFT)]);

            $this->supersede($orders->pluck('client_statement_id')->filter()->unique(), $orderIds);

            $totals = ['cod' => 0, 'customer_delivery' => 0, 'client_delivery' => 0, 'net' => 0];
            $pivot  = [];

            foreach ($orders as $order) {
                $amounts = $this->amounts($order);
                foreach ($totals as $key => $_) {
                    $totals[$key] += $amounts[$key];
                }

                $pivot[$order->id] = [
                    'previous_payment_status'  => $order->payment_status === 'exported'
                        ? ($previousStatuses[$order->id] ?? 'with_company')
                        : $order->payment_status,
                    'cod_amount'               => $amounts['cod'],
                    'customer_delivery_amount' => $amounts['customer_delivery'],
                    'client_delivery_amount'   => $amounts['client_delivery'],
                    'net_amount'               => $amounts['net'],
                ];
            }

            $statement->orders()->attach($pivot);

            Order::whereIn('id', $orderIds)->update([
                'payment_status'      => 'exported',
                'client_statement_id' => $statement->id,
            ]);

            $statement->update([
                'orders_count'            => $orders->count(),
                'total_cod'               => $totals['cod'],
                'total_customer_delivery' => $totals['customer_delivery'],
                'total_client_delivery'   => $totals['client_delivery'],
                'net_amount'              => $totals['net'],
            ]);

            return $statement;
        });
    }

    /**
     * Confirm the client was paid: the statement's orders become "paid" and
     * the money movements are written to the financial ledger.
     */
    public function confirmPayment(ClientStatement $statement, User $actor, ?string $reference = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($statement, $actor, $reference, $notes) {
            $statement = ClientStatement::whereKey($statement->id)->lockForUpdate()->firstOrFail();

            if ($statement->status !== 'exported') {
                throw ValidationException::withMessages(['statement' => __('Only exported statements can be marked as paid.')]);
            }

            $orders = $statement->orders()
                ->where('orders.client_statement_id', $statement->id)
                ->where('orders.payment_status', 'exported')
                ->with('driverProfile')
                ->get();

            $chargedOrderIds = FinancialLedgerEntry::whereIn('order_id', $orders->pluck('id'))
                ->where('type', 'shipping_charge')
                ->pluck('order_id')
                ->flip();

            foreach ($orders as $order) {
                $driverUserId = $order->driverProfile?->user_id;
                $clientFee    = (float) $order->pivot->client_delivery_amount;
                $payout       = (float) $order->pivot->cod_amount + (float) $order->pivot->customer_delivery_amount;

                if ($clientFee > 0 && ! isset($chargedOrderIds[$order->id])) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $statement->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'client',
                        'to_account'        => 'company',
                        'amount'            => $clientFee,
                        'type'              => 'shipping_charge',
                        'reference_number'  => $statement->statement_number,
                        'recorded_by'       => $actor->id,
                        'notes'             => 'Delivery charge for order ' . $order->order_number,
                    ]);
                }

                if ($payout > 0) {
                    FinancialLedgerEntry::create([
                        'order_id'          => $order->id,
                        'client_profile_id' => $statement->client_profile_id,
                        'driver_id'         => $driverUserId,
                        'from_account'      => 'company',
                        'to_account'        => 'client',
                        'amount'            => $payout,
                        'type'              => 'client_payout',
                        'reference_number'  => $statement->statement_number,
                        'recorded_by'       => $actor->id,
                        'notes'             => $notes ?? 'Statement payout to client for order ' . $order->order_number,
                    ]);
                }

                OrderTrackingLog::create([
                    'order_id'    => $order->id,
                    'user_id'     => $actor->id,
                    'from_status' => $order->status,
                    'to_status'   => $order->status,
                    'description' => "Client statement {$statement->statement_number} marked as paid.",
                ]);
            }

            Order::whereIn('id', $orders->pluck('id'))->update(['payment_status' => 'paid']);

            $statement->update([
                'status'            => 'paid',
                'paid_by'           => $actor->id,
                'paid_at'           => now(),
                'payment_reference' => $reference,
                'notes'             => $notes,
            ]);

            rescue(fn () => app(SupportNotificationService::class)->notifyClientStatementPaid($statement, $actor->id));
        });
    }

    public function downloadCsv(ClientStatement $statement): StreamedResponse
    {
        [$statement, $orders] = $this->loadForDownload($statement);

        return response()->streamDownload(function () use ($statement, $orders) {
            $file = fopen('php://output', 'w');
            $this->writeCsv($file, $statement, $orders);
            fclose($file);
        }, $statement->statement_number . '.csv', ['Content-Type' => 'text/csv']);
    }

    public function downloadPdf(ClientStatement $statement): Response
    {
        return $this->pdfDocument($statement)->download($statement->statement_number . '.pdf');
    }

    /**
     * Generate the statement file and save it on the public disk (mobile API
     * returns the link). Old files are removed by the waybills:prune command.
     *
     * @param  'pdf'|'excel'  $format
     * @return array{url: string, file_name: string, format: string}
     */
    public function store(ClientStatement $statement, string $format): array
    {
        if ($format === 'pdf') {
            $fileName = $statement->statement_number . '.pdf';
            $content  = $this->pdfDocument($statement)->output();
        } else {
            [$statement, $orders] = $this->loadForDownload($statement);
            $fileName = $statement->statement_number . '.csv';
            $file     = fopen('php://temp', 'w+');
            $this->writeCsv($file, $statement, $orders);
            rewind($file);
            $content  = stream_get_contents($file);
            fclose($file);
        }

        // Random folder name keeps stored links unguessable
        $path = self::STORAGE_DIR . '/' . now()->format('Y/m/d') . '/' . Str::random(40) . '/' . $fileName;
        Storage::disk('public')->put($path, $content);

        return [
            'url'       => Storage::disk('public')->url($path),
            'file_name' => $fileName,
            'format'    => $format,
        ];
    }

    private function pdfDocument(ClientStatement $statement): DomPdf
    {
        [$statement, $orders] = $this->loadForDownload($statement);

        return $this->pdf->render('shared.statements.pdf', [
            'statement' => $statement,
            'orders'    => $orders,
            'period'    => $this->periodLabel($statement),
            'service'   => $this,
        ], 'landscape');
    }

    /**
     * @param  resource  $file
     */
    private function writeCsv($file, ClientStatement $statement, $orders): void
    {
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM so Excel renders Arabic correctly

        fputcsv($file, ['Statement', $statement->statement_number]);
        fputcsv($file, ['Client', $statement->clientProfile?->company_name]);
        fputcsv($file, ['Period', $this->periodLabel($statement)]);
        fputcsv($file, ['Exported At', $statement->created_at->format('Y-m-d H:i')]);
        fputcsv($file, ['Status', ucfirst($statement->status)]);
        fputcsv($file, []);

        fputcsv($file, [
            'Order #', 'Date', 'Order Status', 'Receiver Name', 'Receiver Phone', 'City', 'Area',
            'Payment Type', 'COD (JD)', 'Customer Delivery (JD)', 'Saee Delivery Fee (JD)', 'Net (JD)',
        ]);

        foreach ($orders as $order) {
            fputcsv($file, [
                $order->order_number,
                $this->completedAt($order)?->toDateString(),
                ucfirst($order->status),
                $order->receiver?->receiver_name,
                $order->receiver?->receiver_phone,
                $order->receiver?->city?->name,
                $order->receiver?->area?->name,
                strtoupper($order->payment?->payment_type ?? ''),
                number_format($order->pivot->cod_amount, 2, '.', ''),
                number_format($order->pivot->customer_delivery_amount, 2, '.', ''),
                number_format($order->pivot->client_delivery_amount, 2, '.', ''),
                number_format($order->pivot->net_amount, 2, '.', ''),
            ]);
        }

        fputcsv($file, []);
        fputcsv($file, [
            'Total', '', '', '', '', '', '', $statement->orders_count . ' orders',
            number_format($statement->total_cod, 2, '.', ''),
            number_format($statement->total_customer_delivery, 2, '.', ''),
            number_format($statement->total_client_delivery, 2, '.', ''),
            number_format($statement->net_amount, 2, '.', ''),
        ]);
    }

    public function completedAt(Order $order): ?\Illuminate\Support\Carbon
    {
        return $order->delivered_at ?? $order->returned_at ?? $order->created_at;
    }

    public function periodLabel(ClientStatement $statement): string
    {
        $from = $statement->date_from?->format('Y-m-d');
        $to   = $statement->date_to?->format('Y-m-d');

        return match (true) {
            $from && $to => "{$from} → {$to}",
            (bool) $from => __('From') . " {$from}",
            (bool) $to   => __('Until') . " {$to}",
            default      => __('All dates'),
        };
    }

    private function loadForDownload(ClientStatement $statement): array
    {
        $statement->loadMissing('clientProfile');

        $orders = $statement->orders()
            ->with(['payment', 'receiver.city', 'receiver.area'])
            ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at)')
            ->get();

        return [$statement, $orders];
    }

    /**
     * Mark the given statements superseded and put their orders that are not
     * part of the new export back to the status they had before.
     */
    private function supersede(iterable $statementIds, iterable $keepOrderIds): void
    {
        $statements = ClientStatement::whereIn('id', collect($statementIds))->where('status', 'exported')->get();

        foreach ($statements as $old) {
            $old->orders()
                ->where('orders.client_statement_id', $old->id)
                ->where('orders.payment_status', 'exported')
                ->whereNotIn('orders.id', collect($keepOrderIds))
                ->get()
                ->each(fn (Order $order) => $order->update([
                    'payment_status'      => $order->pivot->previous_payment_status,
                    'client_statement_id' => null,
                ]));

            $old->update(['status' => 'superseded']);
        }
    }
}
