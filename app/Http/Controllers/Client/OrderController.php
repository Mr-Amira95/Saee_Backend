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
use App\Services\OpenAIService;
use App\Traits\NormalizesOrderImportValues;

class OrderController extends Controller
{
    use NormalizesOrderImportValues;

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
                ->selectRaw('COALESCE(SUM(COALESCE(order_payments.order_amount, 0) + COALESCE(order_payments.customer_delivery_amount, 0)), 0) as total')
                ->value('total') ?? 0,
        ];

        $query = $this->getFilteredQuery($request, $clientId, true);
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
            'delivery_shift'           => ['nullable', 'string', 'in:doesnt_matter,before_12pm,after_12pm'],
        ]);

        $validated['client_profile_id'] = $profile->id;
        $validated['delivery_on_customer'] = $request->boolean('delivery_on_customer');
        $validated['delivery_shift'] = $validated['delivery_shift'] ?? 'doesnt_matter';

        $order = $this->orderService->createOrder($validated, Auth::user());
        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsNewOrder($order));

        return redirect()->route('client.orders.show', $order)
            ->with('success', "Order #{$order->order_number} created successfully.");
    }

    public function edit(Order $order): View
    {
        $profile = $this->getClientProfile();
        abort_if($order->client_profile_id !== $profile->id, 403);
        abort_if($order->status !== 'pending', 403, 'Only pending orders can be edited.');

        $order->load(['receiver', 'payment']);

        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.edit', compact('order', 'cities'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $profile = $this->getClientProfile();
        abort_if($order->client_profile_id !== $profile->id, 403);
        abort_if($order->status !== 'pending', 403, 'Only pending orders can be edited.');

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
            'delivery_shift'           => ['nullable', 'string', 'in:doesnt_matter,before_12pm,after_12pm'],
        ]);

        $deliveryOnCustomer = $request->boolean('delivery_on_customer');

        // Recalculate client delivery fee if city changed
        $cityChanged = $order->receiver->city_id != (int) $validated['city_id'];
        $clientDeliveryAmount = $cityChanged
            ? $profile->getDeliveryPriceForCity((int) $validated['city_id'])
            : $order->payment->client_delivery_amount;

        $order->update([
            'order_description' => $validated['order_description'] ?? null,
            'notes'             => $validated['notes'] ?? null,
            'delivery_shift'    => $validated['delivery_shift'] ?? 'doesnt_matter',
        ]);

        $order->payment->update([
            'payment_type'             => $validated['payment_type'],
            'order_amount'             => $validated['payment_type'] === 'cod' ? (float) ($validated['order_price'] ?? 0) : null,
            'delivery_on_customer'     => $deliveryOnCustomer,
            'customer_delivery_amount' => $deliveryOnCustomer ? (float) ($validated['delivery_customer_amount'] ?? 0) : null,
            'client_delivery_amount'   => $clientDeliveryAmount,
        ]);

        $order->receiver->update([
            'receiver_name'  => $validated['receiver_name'],
            'receiver_phone' => $validated['receiver_phone'],
            'city_id'        => (int) $validated['city_id'],
            'area_id'        => (int) $validated['area_id'],
            'address_text'   => $validated['address_text'],
        ]);

        return redirect()->route('client.orders.show', $order)
            ->with('success', "Order #{$order->order_number} updated successfully.");
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

    public function showImportImage(): View
    {
        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.import_image', compact('cities'));
    }

    public function importImage(Request $request, OpenAIService $openAIService): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $imagePath = $request->file('image')->getRealPath();

        $profile = $this->getClientProfile();
        $citiesList = City::where('is_active', true)->with('areas')->get();

        // Simplify lists for OpenAI token efficiency (no clients list needed on client portal)
        $citiesData = $citiesList->map(fn($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'name_ar' => $c->name_ar,
            'areas' => $c->areas->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'name_ar' => $a->name_ar])->toArray()
        ])->toArray();

        try {
            $parsedOrders = $openAIService->parseImageForOrders($imagePath, [], $citiesData);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'AI Processing failed: ' . $e->getMessage());
        }

        if (empty($parsedOrders)) {
            return redirect()->back()->with('error', 'No order details could be extracted from the image.');
        }

        $rows = [];
        $errors = [];
        $hasErrors = false;

        foreach ($parsedOrders as $index => $o) {
            // 1. Resolve city_id
            $cityId = $o['city_id'] ?? null;
            // Validate city exists in database list
            if ($cityId && !$citiesList->contains('id', $cityId)) {
                $cityId = null;
            }
            // Fallback string matching
            if (!$cityId && !empty($o['city_name'])) {
                $cityName = strtolower(trim($o['city_name']));
                $matchedCity = $citiesList->first(function ($c) use ($cityName) {
                    return str_contains(strtolower($c->name), $cityName) ||
                           str_contains(strtolower($c->name_ar), $cityName) ||
                           str_contains($cityName, strtolower($c->name)) ||
                           str_contains($cityName, strtolower($c->name_ar));
                });
                if ($matchedCity) {
                    $cityId = $matchedCity->id;
                }
            }

            // 2. Resolve area_id
            $areaId = $o['area_id'] ?? null;
            // Validate area exists under matched city
            if ($cityId) {
                $cityObj = $citiesList->firstWhere('id', $cityId);
                if ($cityObj) {
                    if ($areaId && !$cityObj->areas->contains('id', $areaId)) {
                        $areaId = null;
                    }
                    // Fallback string matching
                    if (!$areaId && !empty($o['area_name'])) {
                        $areaName = strtolower(trim($o['area_name']));
                        $matchedArea = $cityObj->areas->first(function ($a) use ($areaName) {
                            return str_contains(strtolower($a->name), $areaName) ||
                                   str_contains(strtolower($a->name_ar), $areaName) ||
                                   str_contains($areaName, strtolower($a->name)) ||
                                   str_contains($areaName, strtolower($a->name_ar));
                        });
                        if ($matchedArea) {
                            $areaId = $matchedArea->id;
                        }
                    }
                }
            }

            // Construct row formatted as expected by ClientOrderController@validateImportRow
            $row = [
                'client_profile_id'        => $profile->id,
                'client_id'                => $profile->id,
                'order_description'        => $o['order_description'] ?? '',
                'payment_type'             => strtolower($this->normalizePaymentTypeValue($o['payment_type'] ?? 'cod')),
                'delivery_on_customer'     => filter_var($this->normalizeYesNoValue($o['delivery_on_customer'] ?? 'false'), FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
                'delivery_customer_amount' => number_format((float)($o['delivery_customer_amount'] ?? 0.00), 2, '.', ''),
                'order_price'              => number_format((float)($o['order_price'] ?? 0.00), 2, '.', ''),
                'receiver_name'            => $o['receiver_name'] ?? '',
                'receiver_phone'           => $o['receiver_phone'] ?? '',
                'city_id'                  => $cityId,
                'area_id'                  => $areaId,
                'address_text'             => $o['address_text'] ?? '',
                'notes'                    => $o['notes'] ?? '',
                'delivery_shift'           => strtolower($o['delivery_shift'] ?? 'doesnt_matter'),
            ];

            // Normalize delivery_shift
            if (!in_array($row['delivery_shift'], ['doesnt_matter', 'before_12pm', 'after_12pm'])) {
                $row['delivery_shift'] = 'doesnt_matter';
            }

            $rowErrors = $this->validateImportRow($row);
            if (!empty($rowErrors)) {
                $hasErrors = true;
                $errors[$index] = $rowErrors;
            }

            $rows[$index] = $row;
        }

        session(['client_import_pending_rows' => $rows]);
        session(['client_import_errors' => $errors]);

        if ($hasErrors) {
            return redirect()->route('client.orders.import.review')
                ->with('error', 'AI parsed the image, but some details need manual selection or correction.');
        }

        return redirect()->route('client.orders.import.review');
    }

    public function showImport(): View
    {
        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.import', compact('cities'));
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $fields = [
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
            'delivery_shift',
        ];

        $locale = app()->getLocale();

        $headers = $this->localizeImportHeaders($fields, $locale);

        $sample = $locale === 'ar' ? [
            'طلب تجارة إلكترونية (أحذية)',
            'عند التسليم',
            'لا',
            '0.00',
            '150.00',
            'أحمد منصور',
            '0791234567',
            '1',
            '2',
            'شارع الملك عبدالله الثاني، عمان',
            'يرجى التوصيل بعد الساعة 5 مساءً.',
            'doesnt_matter',
        ] : [
            'E-commerce order (Shoes)',
            'cod',
            'No',
            '0.00',
            '150.00',
            'Ahmed Mansour',
            '0791234567',
            '1',
            '2',
            'King Abdullah II St, Amman',
            'Deliver after 5 PM please.',
            'doesnt_matter',
        ];

        $callback = function () use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM so Excel renders Arabic correctly
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
            $headers = $headers ? $this->normalizeImportHeaderRow($headers) : $headers;

            $expected = ['order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount', 'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id', 'address_text', 'notes', 'delivery_shift'];

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

        $rows = [];
        $errors = [];
        $hasErrors = false;

        foreach ($data as $index => $row) {
            $row['payment_type']         = $this->normalizePaymentTypeValue($row['payment_type'] ?? '');
            $row['delivery_on_customer'] = $this->normalizeYesNoValue($row['delivery_on_customer'] ?? 'false');

            $rowErrors = $this->validateImportRow($row);

            $deliveryShift = isset($row['delivery_shift']) ? strtolower(trim($row['delivery_shift'])) : 'doesnt_matter';
            if ($deliveryShift === '') {
                $deliveryShift = 'doesnt_matter';
            }
            $row['delivery_shift'] = $deliveryShift;

            if (! empty($rowErrors)) {
                $hasErrors = true;
                $errors[$index] = $rowErrors;
            }
            $rows[$index] = $row;
        }

        session(['client_import_pending_rows' => $rows]);
        session(['client_import_errors' => $errors]);

        if ($hasErrors) {
            return redirect()->route('client.orders.import.review')
                ->with('error', 'CSV parsed but some rows failed validation. Please correct them below.');
        }

        return redirect()->route('client.orders.import.review');
    }

    public function showReview(): View|RedirectResponse
    {
        $rows = session('client_import_pending_rows');
        $rowErrors = session('client_import_errors', []);

        if (empty($rows)) {
            return redirect()->route('client.orders.import');
        }

        $cities = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('client.orders.import_confirm', compact('rows', 'rowErrors', 'cities'));
    }

    public function storeConfirmed(Request $request): RedirectResponse
    {
        $rows    = $request->input('rows', []);
        $profile = $this->getClientProfile();

        if (empty($rows)) {
            return redirect()->route('client.orders.import')->with('error', 'No order data found. Please re-upload your file.');
        }

        $errors = [];
        $hasErrors = false;

        foreach ($rows as $index => $rowData) {
            $rowErrors = $this->validateImportRow($rowData);
            if (! empty($rowErrors)) {
                $hasErrors = true;
                $errors[$index] = $rowErrors;
            }
        }

        if ($hasErrors) {
            session(['client_import_pending_rows' => $rows]);
            session(['client_import_errors' => $errors]);

            return redirect()->route('client.orders.import.review')
                ->with('error', 'Some rows still have validation errors. Please correct them.');
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
                'delivery_shift'           => $rowData['delivery_shift'] ?? 'doesnt_matter',
            ], Auth::user());
        }

        session()->forget('client_import_pending_rows');
        session()->forget('client_import_errors');

        $count = count($rows);

        rescue(fn () => app(SupportNotificationService::class)->notifyAdminsOrdersImported(
            $profile->company_name,
            $count,
            $batchNumber,
        ));

        return redirect()->route('client.orders.index')
            ->with('success', "Successfully imported {$count} orders. Batch: {$batchNumber}");
    }

    private function validateImportRow(array $row): array
    {
        $rowErrors = [];

        $paymentType = strtolower($this->normalizePaymentTypeValue($row['payment_type'] ?? ''));
        if (! in_array($paymentType, ['cod', 'prepaid'])) {
            $rowErrors[] = "Payment type must be 'cod'/'عند التسليم' or 'prepaid'/'مدفوع'.";
        }

        $orderPrice = filter_var($row['order_price'] ?? null, FILTER_VALIDATE_FLOAT);
        if ($paymentType === 'cod' && ($orderPrice === false || $orderPrice < 0)) {
            $rowErrors[] = 'Order price must be a positive number for COD orders.';
        }

        $deliveryOnCustomer = filter_var($this->normalizeYesNoValue($row['delivery_on_customer'] ?? 'false'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($deliveryOnCustomer === null) {
            $rowErrors[] = "delivery_on_customer must be 'yes'/'نعم' or 'no'/'لا'.";
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

        $deliveryShift = isset($row['delivery_shift']) ? strtolower(trim($row['delivery_shift'])) : 'doesnt_matter';
        if ($deliveryShift === '') {
            $deliveryShift = 'doesnt_matter';
        }
        if (! in_array($deliveryShift, ['doesnt_matter', 'before_12pm', 'after_12pm'])) {
            $rowErrors[] = "Delivery shift must be 'doesnt_matter', 'before_12pm', or 'after_12pm'.";
        }

        return $rowErrors;
    }

    public function export(Request $request)
    {
        $profile = $this->getClientProfile();
        $orders = $this->getFilteredQuery($request, $profile->id, true)->latest()->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="orders_export_' . now()->format('Ymd_His') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'Order Number',
                'Batch Number',
                'Status',
                'Payment Status',
                'Receiver Name',
                'Receiver Phone',
                'City',
                'Area',
                'Address',
                'Payment Type',
                'Order Price (COD)',
                'Delivery Customer Amount',
                'Delivery Shift',
                'Created At'
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->batch_number,
                    $order->status,
                    $order->payment_status,
                    $order->receiver?->receiver_name,
                    $order->receiver?->receiver_phone,
                    $order->receiver?->city?->name ?? 'N/A',
                    $order->receiver?->area?->name ?? 'N/A',
                    $order->receiver?->address_text,
                    $order->payment?->payment_type,
                    $order->payment?->order_amount,
                    $order->payment?->customer_delivery_amount,
                    $order->delivery_shift,
                    $order->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function printAll(Request $request)
    {
        $profile = $this->getClientProfile();
        $orders = $this->getFilteredQuery($request, $profile->id, true)->latest()->get();
        return view('shared.orders.print', compact('orders'));
    }

    public function printOrder(Order $order)
    {
        $profile = $this->getClientProfile();
        if ($order->client_profile_id !== $profile->id) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['clientProfile', 'driverProfile.user', 'receiver.city', 'receiver.area', 'payment']);
        return view('shared.orders.print', ['orders' => [$order]]);
    }

    private function getFilteredQuery(Request $request, $clientId, $withRelations = true)
    {
        $query = Order::where('client_profile_id', $clientId);

        if ($withRelations) {
            $query->with(['receiver.city', 'receiver.area', 'payment', 'clientProfile']);
        }

        if ($request->filled('status')) {
            if ($request->status === 'in_transit') {
                $query->whereIn('status', ['assigned', 'picked_up']);
            } elseif ($request->status === 'returned_failed') {
                $query->whereIn('status', ['returned', 'rejected']);
            } else {
                $query->where('status', $request->status);
            }
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

        return $query;
    }
}
