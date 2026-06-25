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

class BulkOrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
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

            $expected = ['client_id', 'order_description', 'payment_type', 'delivery_on_customer', 'delivery_customer_amount', 'order_price', 'receiver_name', 'receiver_phone', 'city_id', 'area_id', 'address_text', 'notes'];
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

            $clientId = filter_var($row['client_id'] ?? null, FILTER_VALIDATE_INT);
            if (! $clientId || ! ClientProfile::where('id', $clientId)->exists()) {
                $rowErrors[] = "Client ID [{$row['client_id']}] not found.";
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

            if (! empty($rowErrors)) {
                $hasErrors = true;
            }

            $results[] = ['row_number' => $rowNum, 'data' => $row, 'errors' => $rowErrors];
        }

        if ($hasErrors) {
            return view('admin.orders.import_preview', [
                'results'    => $results,
                'has_errors' => true,
            ]);
        }

        session(['import_pending_rows' => array_column($results, 'data')]);

        return redirect()->route('admin.orders.import.review');
    }

    public function showReview()
    {
        $rows = session('import_pending_rows');

        if (empty($rows)) {
            return redirect()->route('admin.orders.import');
        }

        $clients = ClientProfile::where('status', 'active')->orderBy('company_name')->get(['id', 'company_name']);
        $cities  = City::where('is_active', true)->with('areas')->orderBy('name')->get();

        return view('admin.orders.import_confirm', compact('rows', 'clients', 'cities'));
    }

    public function storeConfirmed(Request $request)
    {
        $rows = $request->input('rows', []);

        if (empty($rows)) {
            return redirect()->route('admin.orders.import')->with('error', 'No order data found. Please re-upload your file.');
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
            ], Auth::user());
        }

        session()->forget('import_pending_rows');

        $count = count($rows);

        return redirect()->route('admin.orders.index')
            ->with('success', "Successfully imported {$count} orders. Batch: {$batchNumber}");
    }
}
