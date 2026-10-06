<?php

namespace App\Http\Controllers\Client;

use App\Models\Order;
use App\Services\WaybillExportService;
use Illuminate\Http\Request;

class WaybillController extends Controller
{
    /**
     * Export PDF for an explicit set of orders, restricted to the client's own.
     */
    public function export(Request $request, WaybillExportService $waybills)
    {
        $profile = $this->getClientProfile();

        $ids = $waybills->idsFrom($request);
        abort_if(empty($ids), 404, __('No orders found.'));

        return $waybills->render(
            Order::where('client_profile_id', $profile->id)->whereIn('id', $ids)->latest()
        );
    }
}
