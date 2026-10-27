<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\ClientStatement;
use App\Services\ClientStatementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Client Statements: one report per client (COD + delivery paid by customer −
 * Saee delivery fee). Export → orders "exported"; Confirm Payment → "paid".
 */
class ClientStatementController extends Controller
{
    public function __construct(private ClientStatementService $statements)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $tab     = $request->input('tab') === 'statements' ? 'statements' : 'orders';
        $clients = ClientProfile::orderBy('company_name')->get(['id', 'company_name']);

        $ordersQuery = $this->statements->ordersQuery($filters);
        $totals      = $this->statements->totals($ordersQuery);

        // What "Export" would include right now (paid orders are never re-exported)
        $exportable = $filters['client_id'] && $filters['payment_status'] !== 'paid'
            ? $this->statements->totals((clone $ordersQuery)->where('orders.payment_status', '!=', 'paid'))
            : null;

        $orders = $statementsList = null;

        if ($tab === 'orders') {
            $orders = $ordersQuery
                ->with(['payment', 'receiver.city', 'clientProfile', 'clientStatement'])
                ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at) DESC')
                ->paginate(25)
                ->withQueryString();
        } else {
            $statementsList = ClientStatement::with(['clientProfile', 'exporter', 'payer'])
                ->when($filters['client_id'], fn ($q, $id) => $q->where('client_profile_id', $id))
                ->when($filters['from'], fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
                ->when($filters['to'], fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
                ->when(in_array($request->input('statement_status'), ['exported', 'paid', 'superseded'], true),
                    fn ($q) => $q->where('status', $request->input('statement_status')))
                ->when($filters['search'], fn ($q, $search) => $q->where('statement_number', 'like', "%{$search}%"))
                ->latest()
                ->paginate(25)
                ->withQueryString();
        }

        return view('admin.financials.client_statements.index', compact(
            'filters', 'tab', 'clients', 'totals', 'exportable', 'orders', 'statementsList'
        ));
    }

    public function export(Request $request)
    {
        abort_unless($request->user()->hasAdminAction('finances.client_statements'), 403);

        $request->validate([
            'client_id' => ['required', 'exists:client_profiles,id'],
            'format'    => ['required', Rule::in(['csv', 'pdf'])],
        ]);

        $client    = ClientProfile::findOrFail($request->input('client_id'));
        $statement = $this->statements->export($client, $this->filters($request), $request->user());

        return redirect()
            ->route('admin.financials.client-statements.show', $statement)
            ->with('success', __('Statement :number exported. :count orders are now marked as exported.', [
                'number' => $statement->statement_number,
                'count'  => $statement->orders_count,
            ]))
            ->with('download_url', route('admin.financials.client-statements.download', [$statement, $request->input('format')]));
    }

    public function show(ClientStatement $statement)
    {
        $statement->load(['clientProfile', 'exporter', 'payer']);

        $orders = $statement->orders()
            ->with(['payment', 'receiver.city'])
            ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at)')
            ->paginate(50);

        return view('admin.financials.client_statements.show', compact('statement', 'orders'));
    }

    public function download(ClientStatement $statement, string $format)
    {
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        return $format === 'csv'
            ? $this->statements->downloadCsv($statement)
            : $this->statements->downloadPdf($statement);
    }

    public function confirmPayment(Request $request, ClientStatement $statement)
    {
        abort_unless($request->user()->hasAdminAction('finances.client_statements'), 403);

        $request->validate([
            'payment_reference' => ['nullable', 'string', 'max:100'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ]);

        $this->statements->confirmPayment(
            $statement,
            $request->user(),
            $request->input('payment_reference'),
            $request->input('notes')
        );

        return redirect()
            ->route('admin.financials.client-statements.show', $statement)
            ->with('success', __('Payment confirmed. All orders in statement :number are now paid.', ['number' => $statement->statement_number]));
    }

    /**
     * @return array{client_id: ?string, from: ?string, to: ?string, payment_status: ?string, search: ?string}
     */
    private function filters(Request $request): array
    {
        $request->validate([
            'client_id'      => ['nullable', 'integer'],
            'from'           => ['nullable', 'date'],
            'to'             => ['nullable', 'date'],
            'payment_status' => ['nullable', Rule::in(ClientStatementService::PAYMENT_STATUSES)],
            'search'         => ['nullable', 'string', 'max:100'],
        ]);

        return [
            'client_id'      => $request->input('client_id') ?: null,
            'from'           => $request->input('from') ?: null,
            'to'             => $request->input('to') ?: null,
            'payment_status' => $request->input('payment_status') ?: null,
            'search'         => trim((string) $request->input('search')) ?: null,
        ];
    }
}
