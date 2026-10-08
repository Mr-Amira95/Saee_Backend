<?php

namespace App\Services;

use App\Models\Order;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
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

    /** Max characters per line when shaping Arabic text (lines are wrapped before dompdf sees them). */
    private const ARABIC_LINE_CHARS = 60;

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
        @set_time_limit(300);

        $html = $this->shapeArabic(view('shared.orders.pdf', compact('orders'))->render());

        return Pdf::loadHTML($html)->setPaper('a4');
    }

    private function filename(Collection $orders): string
    {
        return $orders->count() === 1
            ? "waybill-{$orders->first()->order_number}.pdf"
            : 'waybills-' . now()->format('Ymd_His') . '.pdf';
    }

    /**
     * dompdf cannot join Arabic letters or apply right-to-left ordering, so
     * every Arabic fragment of the HTML is pre-shaped into presentation forms
     * in visual order (ar-php's documented dompdf recipe).
     */
    private function shapeArabic(string $html): string
    {
        $arabic = new Arabic();
        $positions = $arabic->arIdentify($html);

        for ($i = count($positions) - 1; $i >= 0; $i -= 2) {
            $start  = $positions[$i - 1];
            $length = $positions[$i] - $start;

            // Diacritics (tashkeel) can't be positioned by dompdf — drop them before shaping
            $text   = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', substr($html, $start, $length));
            $shaped = $arabic->utf8Glyphs($text, self::ARABIC_LINE_CHARS, false);
            $html   = substr_replace($html, str_replace("\n", '<br>', $shaped), $start, $length);
        }

        return $html;
    }
}
