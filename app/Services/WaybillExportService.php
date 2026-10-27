<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\PDF as DomPdf;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Single source of truth for the "Export PDF" (waybills) feature used by every
 * orders table in the admin and client portals and by the mobile API.
 *
 * The waybill layout lives in one template: resources/views/shared/orders/pdf.blade.php.
 * The button lives in resources/views/components/export-pdf-button.blade.php.
 */
class WaybillExportService
{
    /** Relations the waybill template reads. */
    public const RELATIONS = [
        'clientProfile.masterUser',
        'driverProfile.user',
        'receiver.city',
        'receiver.area',
        'payment',
    ];

    /** Upper bound per export — dompdf renders one page per order. */
    public const MAX_ORDERS = 500;

    /** Public-disk folder that stored waybill files go to (see waybills:prune). */
    public const STORAGE_DIR = 'waybills';

    public function __construct(private ArabicPdfRenderer $renderer)
    {
    }

    /**
     * Stream the waybills PDF inline (opens in the browser's PDF viewer, where
     * it can be printed or downloaded).
     *
     * @param  Builder|iterable<Order>|Order  $orders
     */
    public function render(Builder|iterable|Order $orders): Response
    {
        $orders = $this->resolve($orders);

        return $this->pdf($orders)->stream($this->filename($orders));
    }

    /**
     * Same as render(), but forces a file download.
     *
     * @param  Builder|iterable<Order>|Order  $orders
     */
    public function download(Builder|iterable|Order $orders): Response
    {
        $orders = $this->resolve($orders);

        return $this->pdf($orders)->download($this->filename($orders));
    }

    /**
     * Generate the waybills PDF and save it on the public disk.
     *
     * @param  Builder|iterable<Order>|Order  $orders
     * @return array{path: string, url: string, file_name: string, orders_count: int}
     */
    public function store(Builder|iterable|Order $orders): array
    {
        $orders = $this->resolve($orders);

        $fileName = $this->filename($orders);
        // Random folder name keeps stored links unguessable
        $path = self::STORAGE_DIR . '/' . now()->format('Y/m/d') . '/' . Str::random(40) . '/' . $fileName;

        Storage::disk('public')->put($path, $this->pdf($orders)->output());

        return [
            'path'         => $path,
            'url'          => Storage::disk('public')->url($path),
            'file_name'    => $fileName,
            'orders_count' => $orders->count(),
        ];
    }

    /**
     * Read the order IDs posted by the Export PDF button or the API
     * (comma-separated string or array).
     *
     * @return array<int, int>
     */
    public function idsFrom(Request $request, string $key = 'ids'): array
    {
        $ids = $request->input($key, []);

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        return collect((array) $ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  Builder|iterable<Order>|Order  $orders
     */
    private function resolve(Builder|iterable|Order $orders): Collection
    {
        if ($orders instanceof Builder) {
            $orders = $orders->with(self::RELATIONS)->limit(self::MAX_ORDERS + 1)->get();
        } else {
            $orders = (new Collection($orders instanceof Order ? [$orders] : $orders))
                ->loadMissing(self::RELATIONS);
        }

        abort_if($orders->isEmpty(), 404, __('No orders found.'));
        abort_if(
            $orders->count() > self::MAX_ORDERS,
            422,
            __('Too many orders to export at once (maximum :max). Please narrow your filters.', ['max' => self::MAX_ORDERS])
        );

        return $orders;
    }

    private function pdf(Collection $orders): DomPdf
    {
        return $this->renderer->render('shared.orders.pdf', compact('orders'));
    }

    private function filename(Collection $orders): string
    {
        return $orders->count() === 1
            ? "waybill-{$orders->first()->order_number}.pdf"
            : 'waybills-' . now()->format('Ymd_His') . '.pdf';
    }
}
