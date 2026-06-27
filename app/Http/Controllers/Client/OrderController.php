<?php

namespace App\Http\Controllers\Client;

use App\Models\Area;
use App\Models\City;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\SupportNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): View
    {
        $profile = $this->getClientProfile();

        $clientId = $profile->id;

        $stats = [
            'pending'   => Order::where('client_profile_id', $clientId)->whereIn('status', ['pending', 'picked_up', 'rejected'])->count(),
            'delivered' => Order::where('client_profile_id', $clientId)->where('status', 'delivered')->count(),
            'returned'  => Order::where('client_profile_id', $clientId)->where('status', 'returned')->count(),
            'pending_cash' => Order::where('client_profile_id', $clientId)
                ->whereIn('payment_status', ['with_driver', 'with_company'])
                ->whereNotIn('status', ['returned', 'cancelled'])
                ->join('order_payments', 'orders.id', '=', 'order_payments.order_id')
                ->where('order_payments.payment_type', 'cod')
                ->selectRaw('COALESCE(SUM(order_payments.order_amount + IF(order_payments.delivery_on_customer = 1, COALESCE(order_payments.customer_delivery_amount, 0), 0)), 0) as total')
                ->value('total') ?? 0,
        ];

        $query = Order::where('client_profile_id', $clientId)
            ->with(['receiver', 'payment']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_type')) {
            $query->whereHas('payment', fn ($pq) => $pq->where('payment_type', $request->payment_type));
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }
        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                  ->orWhereHas('receiver', fn ($rq) => $rq
                      ->where('receiver_name', 'like', "%{$term}%")
                      ->orWhere('receiver_phone', 'like', "%{$term}%")
                  );
            });
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('client.orders.index', compact('orders', 'profile', 'stats'));
    }

    public function create(): View
    {
        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.create', compact('cities'));
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $this->getClientProfile();

        $validated = $request->validate([
            'order_description'        => ['nullable', 'string', 'max:1000'],
            'payment_type'             => ['required', 'in:cod,prepaid'],
            'order_price'              => ['required_if:payment_type,cod', 'nullable', 'numeric', 'min:0'],
            'delivery_on_customer'     => ['nullable', 'boolean'],
            'delivery_customer_amount' => ['required_if:delivery_on_customer,1', 'nullable', 'numeric', 'min:0'],
            'receiver_name'            => ['required', 'string', 'max:255'],
            'receiver_phone'           => ['required', 'string', 'max:20'],
            'city_id'                  => ['required', 'exists:cities,id'],
            'area_id'                  => ['required', 'exists:areas,id'],
            'address_text'             => ['required', 'string', 'max:1000'],
            'address_location'         => ['nullable', 'string', 'max:255'],
            'notes'                    => ['nullable', 'string', 'max:500'],
        ]);

        $validated['client_profile_id'] = $profile->id;
        $validated['delivery_on_customer'] = $request->boolean('delivery_on_customer');

        $order = $this->orderService->createOrder($validated, Auth::user());

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsNewOrder($order));

        return redirect()->route('client.orders.show', $order)
            ->with('success', "Order #{$order->order_number} created successfully.");
    }

    public function show(Order $order): View
    {
        $profile = $this->getClientProfile();

        abort_if($order->client_profile_id !== $profile->id, 403);

        $order->load(['receiver.city', 'receiver.area', 'payment', 'trackingLogs.user', 'driverProfile.user']);

        return view('client.orders.show', compact('order', 'profile'));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $profile = $this->getClientProfile();

        abort_if($order->client_profile_id !== $profile->id, 403);
        abort_if($order->status !== 'pending', 403, 'Only pending orders can be deleted.');

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsCancelOrder($order));

        $order->delete();

        return redirect()->route('client.orders.index')
            ->with('success', "Order #{$order->order_number} deleted.");
    }

    public function showImport(): View
    {
        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.import', compact('cities'));
    }

    public function downloadTemplate(): Response
    {
        $headers = [
            'order_description',
            'payment_type',
            'delivery_on_customer',
            'delivery_customer_amount',
            'order_price',
            'receiver_name',
            'receiver_phone',
            'city_id',
            'area_id',
            'address_text',
            'notes',
        ];

        $sample = [
            'E-commerce order (Shoes)',
            'cod',
            'false',
            '0.00',
            '150.00',
            'Ahmed Mansour',
            '0791234567',
            '1',
            '2',
            'King Abdullah II St, Amman',
            'Deliver after 5 PM please.',
        ];

        $callback = function () use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_import_template.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        $path = $request->file('csv_file')->getRealPath();
        $data = [];

        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            $expected = ['order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount', 'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id', 'address_text', 'notes'];

            if (! $headers || count(array_intersect($headers, $expected)) < 5) {
                return redirect()->back()->with('error', 'Invalid CSV format. Please make sure to use the template provided.');
            }

            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (count($headers) !== count($row)) {
                    continue;
                }
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }

        if (empty($data)) {
            return redirect()->back()->with('error', 'The CSV file is empty.');
        }

        $results   = [];
        $hasErrors = false;

        foreach ($data as $index => $row) {
            $rowErrors = [];
            $rowNum    = $index + 2;

            $paymentType = strtolower($row['payment_type'] ?? '');
            if (! in_array($paymentType, ['cod', 'prepaid'])) {
                $rowErrors[] = "Payment type must be 'cod' or 'prepaid'.";
            }

            $orderPrice = filter_var($row['order_price'] ?? null, FILTER_VALIDATE_FLOAT);
            if ($paymentType === 'cod' && ($orderPrice === false || $orderPrice < 0)) {
                $rowErrors[] = 'Order price must be a positive number for COD orders.';
            }

            $deliveryOnCustomer = filter_var($row['delivery_on_customer'] ?? 'false', FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($deliveryOnCustomer === null) {
                $rowErrors[] = "delivery_on_customer must be 'true' or 'false'.";
            }

            $deliveryCustomerAmt = filter_var($row['delivery_customer_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
            if ($deliveryOnCustomer && ($deliveryCustomerAmt === false || $deliveryCustomerAmt < 0)) {
                $rowErrors[] = 'delivery_customer_amount must be a valid number.';
            }

            $cityId = filter_var($row['city_id'] ?? null, FILTER_VALIDATE_INT);
            if (! $cityId || ! City::where('id', $cityId)->exists()) {
                $rowErrors[] = "City ID [{$row['city_id']}] does not exist.";
            }

            $areaId = filter_var($row['area_id'] ?? null, FILTER_VALIDATE_INT);
            if (! $areaId || ! Area::where('id', $areaId)->where('city_id', $cityId)->exists()) {
                $rowErrors[] = "Area ID [{$row['area_id']}] does not exist or does not belong to City [{$row['city_id']}].";
            }

            if (empty($row['receiver_name']))  { $rowErrors[] = 'Receiver name is required.'; }
            if (empty($row['receiver_phone'])) { $rowErrors[] = 'Receiver phone is required.'; }
            if (empty($row['address_text']))   { $rowErrors[] = 'Address text is required.'; }

            if (! empty($rowErrors)) {
                $hasErrors = true;
            }

            $results[] = ['row_number' => $rowNum, 'data' => $row, 'errors' => $rowErrors];
        }

        if ($hasErrors) {
            $cities = City::where('is_active', true)
                ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
                ->orderBy('name')
                ->get();

            return view('client.orders.import_preview', compact('results', 'cities'));
        }

        session(['client_import_pending_rows' => array_column($results, 'data')]);

        return redirect()->route('client.orders.import.review');
    }

    public function showReview(): View|RedirectResponse
    {
        $rows = session('client_import_pending_rows');

        if (empty($rows)) {
            return redirect()->route('client.orders.import');
        }

        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.import_confirm', compact('rows', 'cities'));
    }

    public function storeConfirmed(Request $request): RedirectResponse
    {
        $rows    = $request->input('rows', []);
        $profile = $this->getClientProfile();

        if (empty($rows)) {
            return redirect()->route('client.orders.import')->with('error', 'No order data found. Please re-upload your file.');
        }

        $batchNumber = 'BATCH-' . now()->format('ymd') . '-' . $profile->id . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        foreach ($rows as $rowData) {
            $this->orderService->createOrder([
                'client_profile_id'        => $profile->id,
                'order_description'        => $rowData['order_description'] ?? null,
                'payment_type'             => strtolower($rowData['payment_type']),
                'delivery_on_customer'     => filter_var($rowData['delivery_on_customer'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
                'delivery_customer_amount' => isset($rowData['delivery_customer_amount']) ? (float) $rowData['delivery_customer_amount'] : 0.00,
                'order_price'              => isset($rowData['order_price']) ? (float) $rowData['order_price'] : 0.00,
                'receiver_name'            => $rowData['receiver_name'],
                'receiver_phone'           => $rowData['receiver_phone'],
                'city_id'                  => (int) $rowData['city_id'],
                'area_id'                  => (int) $rowData['area_id'],
                'address_text'             => $rowData['address_text'],
                'notes'                    => $rowData['notes'] ?? null,
                'driver_id'                => null,
                'batch_number'             => $batchNumber,
            ], Auth::user());
        }

        session()->forget('client_import_pending_rows');

        $count = count($rows);

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsOrdersImported(
            $profile->company_name,
            $count,
            $batchNumber,
        ));

        return redirect()->route('client.orders.index')
            ->with('success', "Successfully imported {$count} orders. Batch: {$batchNumber}");
    }
}
