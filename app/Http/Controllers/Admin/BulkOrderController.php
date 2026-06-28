<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ClientProfile;
use App\Models\City;
use App\Models\Area;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Services\OpenAIService;

class BulkOrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function showImportImage()
    {
        $clients = ClientProfile::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);
        $cities  = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.orders.import_image', compact('clients', 'cities'));
    }

    public function importImage(Request $request, OpenAIService $openAIService)
    {
        $request->validate([
            'image' => 'required|image|max:10240',
        ]);

        $imagePath = $request->file('image')->getRealPath();

        try {
            $parsedOrders = $openAIService->parseImageForOrders($imagePath);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'AI Processing failed: ' . $e->getMessage());
        }

        if (empty($parsedOrders)) {
            return redirect()->back()->with('error', 'No order details could be extracted from the image.');
        }

        // Fetch clients, cities, areas to map names to IDs
        $clientsList = ClientProfile::where('status', 'active')->get(['id', 'company_name']);
        $citiesList  = City::where('is_active', true)->with('areas')->get();

        $rows = [];
        $errors = [];
        $hasErrors = false;

        foreach ($parsedOrders as $index => $o) {
            // Attempt to resolve client_id
            $clientId = null;
            if (!empty($o['client_id_or_name'])) {
                $clientName = strtolower(trim($o['client_id_or_name']));
                $matchedClient = $clientsList->first(function ($c) use ($clientName) {
                    return str_contains(strtolower($c->company_name), $clientName) ||
                           str_contains($clientName, strtolower($c->company_name));
                });
                if ($matchedClient) {
                    $clientId = $matchedClient->id;
                }
            }

            // Attempt to resolve city_id
            $cityId = null;
            if (!empty($o['city_name'])) {
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

            // Attempt to resolve area_id
            $areaId = null;
            if ($cityId && !empty($o['area_name'])) {
                $areaName = strtolower(trim($o['area_name']));
                $cityObj = $citiesList->firstWhere('id', $cityId);
                if ($cityObj) {
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

            // Construct row formatted as expected by BulkOrderController@validateImportRow
            $row = [
                'client_profile_id'        => $clientId,
                'client_id'                => $clientId,
                'order_description'        => $o['order_description'] ?? '',
                'payment_type'             => strtolower($o['payment_type'] ?? 'cod'),
                'delivery_on_customer'     => filter_var($o['delivery_on_customer'] ?? 'false', FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false',
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

        session(['import_pending_rows' => $rows]);
        session(['import_errors' => $errors]);

        if ($hasErrors) {
            return redirect()->route('admin.orders.import.review')
                ->with('error', 'AI parsed the image, but some details need manual selection or correction.');
        }

        return redirect()->route('admin.orders.import.review');
    }

    public function showImport()
    {
        $clients = ClientProfile::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);
        $cities  = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.orders.import', compact('clients', 'cities'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'client_id',
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

        $sample = [
            '1',
            'E-commerce order (Shoes)',
            'cod',
            'false',
            '0.00',
            '150.00',
            'Ahmed Mansour',
            '0501234567',
            '1',
            '2',
            'King Fahd Road, Al Malaz',
            'Deliver after 5 PM please.',
            'doesnt_matter',
        ];

        $callback = function () use ($headers, $sample) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fputcsv($file, $sample);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bulk_orders_template.csv"',
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

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $data = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            $expected = ['client_id', 'order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount', 'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id', 'address_text', 'notes', 'delivery_shift'];
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

        session(['import_pending_rows' => $rows]);
        session(['import_errors' => $errors]);

        if ($hasErrors) {
            return redirect()->route('admin.orders.import.review')
                ->with('error', 'CSV parsed but some rows failed validation. Please correct them below.');
        }

        return redirect()->route('admin.orders.import.review');
    }

    public function showReview()
    {
        $rows = session('import_pending_rows');
        $rowErrors = session('import_errors', []);

        if (empty($rows)) {
            return redirect()->route('admin.orders.import');
        }

        $clients = ClientProfile::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);
        $cities  = City::where('is_active', true)
            ->with(['areas' => fn ($q) => $q->where('is_active', true)->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('admin.orders.import_confirm', compact('rows', 'rowErrors', 'clients', 'cities'));
    }

    public function storeConfirmed(Request $request)
    {
        $rows = $request->input('rows', []);

        if (empty($rows)) {
            return redirect()->route('admin.orders.import')->with('error', 'No order data found. Please re-upload your file.');
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
            session(['import_pending_rows' => $rows]);
            session(['import_errors' => $errors]);

            return redirect()->route('admin.orders.import.review')
                ->with('error', 'Some rows still have validation errors. Please correct them.');
        }

        $firstClientId = $rows[0]['client_profile_id'] ?? 'X';
        $batchNumber   = 'BATCH-' . now()->format('ymd') . '-' . $firstClientId . '-' . strtoupper(substr(md5(uniqid()), 0, 4));

        foreach ($rows as $rowData) {
            $this->orderService->createOrder([
                'client_profile_id'        => (int) $rowData['client_profile_id'],
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

        session()->forget('import_pending_rows');
        session()->forget('import_errors');

        $count = count($rows);

        return redirect()->route('admin.orders.index')
            ->with('success', "Successfully imported {$count} orders. Batch: {$batchNumber}");
    }

    private function validateImportRow(array $row): array
    {
        $rowErrors = [];

        $clientId = $row['client_profile_id'] ?? ($row['client_id'] ?? null);
        $clientId = filter_var($clientId, FILTER_VALIDATE_INT);
        if (! $clientId || ! ClientProfile::where('id', $clientId)->exists()) {
            $rowErrors[] = "Client ID [{$clientId}] not found.";
        }

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

        $deliveryShift = isset($row['delivery_shift']) ? strtolower(trim($row['delivery_shift'])) : 'doesnt_matter';
        if ($deliveryShift === '') {
            $deliveryShift = 'doesnt_matter';
        }
        if (! in_array($deliveryShift, ['doesnt_matter', 'before_12pm', 'after_12pm'])) {
            $rowErrors[] = "Delivery shift must be 'doesnt_matter', 'before_12pm', or 'after_12pm'.";
        }

        return $rowErrors;
    }
}
