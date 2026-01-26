<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Report\Http\Requests\ExpiryReportFormRequest;
use Modules\Setting\Entities\Warehouse;

class ProductExpiryReportController extends BaseController
{
    public function index()
    {
        if (permission('expiry-date-wise-product-report-access')) {
            $this->setPageData('Expiry Date Wise Product Report', 'Expiry Date Wise Product Report', 'fas fa-file', [['name' => 'Report'], ['name' => 'Expiry Date Wise Product Report']]);
            $warehouses = Warehouse::allWarehouses();
            return view('report::expiry-date-wise-product-report.index', compact('warehouses'));
        } else {
            return $this->access_blocked();
        }
    }

    public function report_data(ExpiryReportFormRequest $request)
    {
        $start_date = Carbon::parse($request->start_date)->format('Y-m-d');
        $end_date = Carbon::parse($request->end_date)->format('Y-m-d');

        $warehouse_id = $request->warehouse_id;

        $report_data = PurchaseProduct::with(['purchase:id,invoice_no', 'product:id,name', 'unit:id,unit_name', 'product_variant:id,item_code'])
            ->select(
                'purchase_id', 'product_id', 'product_variant_id', 'purchase_unit_id', 'expiry_date',
                DB::raw('SUM(qty) as total_qty')
            )
            ->whereBetween('expiry_date', [$start_date, $end_date])
            ->groupBy('expiry_date', 'product_id')
            ->orderBy('expiry_date', 'asc')
            ->get();

        return view('report::expiry-date-wise-product-report.report', compact('report_data', 'start_date', 'end_date'))->render();
    }
}
