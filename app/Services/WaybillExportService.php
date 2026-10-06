<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/**
 * Single source of truth for the "Export PDF" (printable waybills) feature used
 * by every orders table in the admin and client portals. Change the waybill
 * layout in shared/orders/print.blade.php and the button in
 * components/export-pdf-button.blade.php — both apply everywhere.
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

    /**
     * Render the printable waybills page for a query or a set of orders.
     *
     * @param  Builder|iterable<Order>|Order  $orders
     */
    public function render(Builder|iterable|Order $orders): View
    {
        if ($orders instanceof Builder) {
            $orders = $orders->with(self::RELATIONS)->get();
        } else {
            $orders = (new Collection($orders instanceof Order ? [$orders] : $orders))
                ->loadMissing(self::RELATIONS);
        }

        return view('shared.orders.print', compact('orders'));
    }

    /**
     * Read the order IDs posted by the Export PDF button (comma-separated
     * string or array).
     *
     * @return array<int, int>
     */
    public function idsFrom(Request $request): array
    {
        $ids = $request->input('ids', []);

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
}
