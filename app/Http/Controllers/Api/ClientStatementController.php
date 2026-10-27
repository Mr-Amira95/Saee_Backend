<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\ClientStatement;
use App\Models\Order;
use App\Models\User;
use App\Services\ClientStatementService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Mobile API for the client Statements page (same data as client/statements on the web).
 */
class ClientStatementController extends Controller
{
    /** Amounts keep their decimal point (200.0, not 200) so typed mobile clients always get a double. */
    private const JSON_FLAGS = JSON_PRESERVE_ZERO_FRACTION;

    public function __construct(private ClientStatementService $statements)
    {
    }

    /**
     * The whole page: summary numbers, the client's statements, and the payment
     * status of every finished order (filters apply to summary + orders).
     */
    public function index(Request $request): JsonResponse
    {
        $profile = $this->resolveClientProfile($request->user());
        if (! $profile) {
            return $this->clientProfileNotFound();
        }

        $request->validate([
            'from'            => ['nullable', 'date_format:Y-m-d'],
            'to'              => ['nullable', 'date_format:Y-m-d'],
            'payment_status'  => ['nullable', Rule::in(ClientStatementService::PAYMENT_STATUSES)],
            'orders_page'     => ['nullable', 'integer', 'min:1'],
            'statements_page' => ['nullable', 'integer', 'min:1'],
            'per_page'        => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $perPage = $request->integer('per_page', 20);

        $ordersQuery = $this->statements->ordersQuery([
            'client_id'      => $profile->id,
            'from'           => $request->input('from'),
            'to'             => $request->input('to'),
            'payment_status' => $request->input('payment_status'),
        ]);

        $summary = $this->statements->totals($ordersQuery);

        $orders = $ordersQuery
            ->with(['payment', 'receiver.city', 'clientStatement'])
            ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at) DESC')
            ->paginate($perPage, ['*'], 'orders_page');

        // Superseded statements were replaced by a newer export — not shown to the client
        $statementsList = ClientStatement::where('client_profile_id', $profile->id)
            ->whereIn('status', ['exported', 'paid'])
            ->latest()
            ->paginate($perPage, ['*'], 'statements_page');

        return response()->json([
            'success'    => true,
            'message'    => __('Statements retrieved successfully.'),
            'summary'    => [
                'orders_count'      => $summary['orders'],
                'cod'               => round($summary['cod'], 2),
                'customer_delivery' => round($summary['customer_delivery'], 2),
                'saee_fee'          => round($summary['client_delivery'], 2),
                'net'               => round($summary['net'], 2),
            ],
            'statements' => [
                'data' => collect($statementsList->items())->map(fn ($s) => $this->statementData($s))->values(),
                'meta' => $this->meta($statementsList),
            ],
            'orders'     => [
                'data' => collect($orders->items())->map(fn ($o) => $this->orderData($o, $this->statements->amounts($o)))->values(),
                'meta' => $this->meta($orders),
            ],
        ], 200, [], self::JSON_FLAGS);
    }

    /**
     * One statement with its orders (amounts as recorded on the statement).
     */
    public function show(Request $request, int $statement): JsonResponse
    {
        $profile = $this->resolveClientProfile($request->user());
        if (! $profile) {
            return $this->clientProfileNotFound();
        }

        $request->validate([
            'page'     => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $model = $this->findStatement($profile, $statement);
        if (! $model) {
            return $this->statementNotFound();
        }

        $orders = $model->orders()
            ->with(['payment', 'receiver.city', 'clientStatement'])
            ->orderByRaw('COALESCE(orders.delivered_at, orders.returned_at, orders.created_at)')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => __('Statement retrieved successfully.'),
            'data'    => array_merge($this->statementData($model), [
                'orders' => [
                    'data' => collect($orders->items())->map(fn ($o) => $this->orderData($o, [
                        'cod'               => (float) $o->pivot->cod_amount,
                        'customer_delivery' => (float) $o->pivot->customer_delivery_amount,
                        'client_delivery'   => (float) $o->pivot->client_delivery_amount,
                        'net'               => (float) $o->pivot->net_amount,
                    ]))->values(),
                    'meta' => $this->meta($orders),
                ],
            ]),
        ], 200, [], self::JSON_FLAGS);
    }

    /**
     * Generate the statement PDF / Excel file, store it and return its link.
     */
    public function download(Request $request, int $statement): JsonResponse
    {
        $profile = $this->resolveClientProfile($request->user());
        if (! $profile) {
            return $this->clientProfileNotFound();
        }

        $request->validate([
            'format' => ['required', Rule::in(['pdf', 'excel'])],
        ]);

        $model = $this->findStatement($profile, $statement);
        if (! $model) {
            return $this->statementNotFound();
        }

        try {
            $file = $this->statements->store($model, $request->input('format'));
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => __('Unable to generate the statement file.'),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => __('Statement file generated successfully.'),
            'data'    => $file,
        ]);
    }

    private function statementData(ClientStatement $statement): array
    {
        return [
            'id'               => $statement->id,
            'statement_number' => $statement->statement_number,
            'period'           => [
                'from'  => $statement->date_from?->format('Y-m-d'),
                'to'    => $statement->date_to?->format('Y-m-d'),
                'label' => $this->statements->periodLabel($statement),
            ],
            'orders_count'     => (int) $statement->orders_count,
            'totals'           => [
                'cod'               => (float) $statement->total_cod,
                'customer_delivery' => (float) $statement->total_customer_delivery,
                'saee_fee'          => (float) $statement->total_client_delivery,
                'net'               => (float) $statement->net_amount,
            ],
            'status'            => $statement->status,
            'status_label'      => $statement->status === 'paid' ? __('Paid') : __('Awaiting Payment'),
            'created_at'        => $statement->created_at?->toIso8601String(),
            'paid_at'           => $statement->paid_at?->toIso8601String(),
            'payment_reference' => $statement->payment_reference,
        ];
    }

    /**
     * @param  array{cod: float, customer_delivery: float, client_delivery: float, net: float}  $amounts
     */
    private function orderData(Order $order, array $amounts): array
    {
        $paymentStatus = in_array($order->payment_status, ['exported', 'paid'], true) ? $order->payment_status : 'ready';

        return [
            'id'                   => $order->id,
            'order_number'         => $order->order_number,
            'status'               => $order->status,
            'status_label'         => __(ucfirst($order->status)),
            'completed_at'         => $this->statements->completedAt($order)?->format('Y-m-d'),
            'receiver'             => [
                'name'  => $order->receiver?->receiver_name,
                'phone' => $order->receiver?->receiver_phone,
                'city'  => $order->receiver?->city?->name,
            ],
            'payment_type'         => $order->payment?->payment_type,
            'amounts'              => [
                'cod'               => round($amounts['cod'], 2),
                'customer_delivery' => round($amounts['customer_delivery'], 2),
                'saee_fee'          => round($amounts['client_delivery'], 2),
                'net'               => round($amounts['net'], 2),
            ],
            'payment_status'       => $paymentStatus,
            'payment_status_label' => match ($paymentStatus) {
                'paid'     => __('Paid'),
                'exported' => __('Exported'),
                default    => __('Pending Statement'),
            },
            'statement'            => $paymentStatus !== 'ready' && $order->clientStatement
                ? ['id' => $order->clientStatement->id, 'statement_number' => $order->clientStatement->statement_number]
                : null,
        ];
    }

    private function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
        ];
    }

    private function findStatement(ClientProfile $profile, int $id): ?ClientStatement
    {
        return ClientStatement::where('client_profile_id', $profile->id)
            ->whereIn('status', ['exported', 'paid'])
            ->find($id);
    }

    private function resolveClientProfile(User $user): ?ClientProfile
    {
        if ($user->isClientMaster()) {
            return $user->clientProfile;
        }

        if ($user->isClientEmployee()) {
            return $user->clientEmployee?->clientProfile;
        }

        return null;
    }

    private function clientProfileNotFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('Access denied. Client profile not found.'),
        ], 403);
    }

    private function statementNotFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('Statement not found.'),
        ], 404);
    }
}
