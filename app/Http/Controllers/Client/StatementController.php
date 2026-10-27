<?php

namespace App\Http\Controllers\Client;

use App\Models\ClientStatement;
use App\Services\ClientStatementService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Client portal Statements page: the payment status of every finished order
 * and the statements Saee exported for this client (read-only).
 */
class StatementController extends Controller
{
    public function __construct(private ClientStatementService $statements)
    {
    }

    public function index(Request $request): View
    {
        $profile = $this->getClientProfile();

        $request->validate([
            'from'           => ['nullable', 'date'],
            'to'             => ['nullable', 'date'],
            'payment_status' => ['nullable', Rule::in(ClientStatementService::PAYMENT_STATUSES)],
            'search'         => ['nullable', 'string', 'max:100'],
        ]);

        $filters = [
            'client_id'      => $profile->id,
            'from'           => $request->input('from') ?: null,
            'to'             => $request->input('to') ?: null,
            'payment_status' => $request->input('payment_status') ?: null,
            'search'         => trim((string) $request->input('search')) ?: null,
        ];

        $ordersQuery = $this->statements->ordersQuery($filters);
        $totals      = $this->statements->totals($ordersQuery);

        $orders = $ordersQuery
            ->with(['payment', 'receiver.city', 'clientStatement'])
            ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at) DESC')
            ->paginate(25, ['*'], 'orders_page')
            ->withQueryString();

        // Superseded statements were replaced by a newer export — not shown to the client
        $statementsList = ClientStatement::where('client_profile_id', $profile->id)
            ->whereIn('status', ['exported', 'paid'])
            ->latest()
            ->paginate(10, ['*'], 'statements_page')
            ->withQueryString();

        return view('client.statements.index', compact('filters', 'totals', 'orders', 'statementsList'));
    }

    public function download(ClientStatement $statement, string $format)
    {
        abort_unless((int) $statement->client_profile_id === (int) $this->getClientProfile()->id, 404);
        abort_unless($statement->status !== 'superseded', 404);
        abort_unless(in_array($format, ['csv', 'pdf'], true), 404);

        return $format === 'csv'
            ? $this->statements->downloadCsv($statement)
            : $this->statements->downloadPdf($statement);
    }
}
