<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\WaybillExportService;
use Illuminate\Http\Request;

class WaybillController extends Controller
{
    /**
     * Export PDF for an explicit set of orders (used by order tables that show
     * a fixed list: invoices, settlements, handovers, billing...).
     */
    public function export(Request $request, WaybillExportService $waybills)
    {
        $ids = $waybills->idsFrom($request);
        abort_if(empty($ids), 404, __('No orders found.'));

        return $waybills->render(Order::whereIn('id', $ids)->latest());
    }
}
