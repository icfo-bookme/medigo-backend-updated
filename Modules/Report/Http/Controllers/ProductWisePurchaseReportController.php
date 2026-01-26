<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;

class ProductWisePurchaseReportController extends BaseController
{
    public function index()
    {
        if(permission('product-wise-purchase-report-access')){
            $this->setPageData('Product Wise Sales Report','Product Wise Sales Report','fas fa-file',[['name' => 'Report'],['name' => 'Product Wise Sales Report']]);
            $products = Product::orderBy('id','asc')->pluck('name','id');
            $warehouses = Warehouse::allWarehouses();
            return view('report::product-wise-purchase-report.index',compact('products','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $product_id   = $request->product_id;
        $start_date   = $request->start_date;
        $end_date     = $request->end_date;
        $warehouse_id = $request->warehouse_id;
        $product      = Product::find($product_id);
        $report_data  = DB::table('purchase_products as pp')
            ->join('purchases as p','pp.purchase_id','=','p.id')
            ->join('products as pr','pp.product_id','=','pr.id')
            ->join('units as u','pp.purchase_unit_id','=','u.id')
            ->selectRaw('pr.id, pr.name, pr.code, u.unit_name,
            SUM(pp.qty) as qty, sum(p.grand_total) as total, p.purchase_date')
            ->groupBy('p.purchase_date')
            ->groupBy('pp.purchase_unit_id')
            ->where(['pp.product_id' => $product_id])
            // ->when($warehouse_id, function($q) use ($warehouse_id){
            //     $q->where('p.warehouse_id', $warehouse_id);
            // })
            ->whereBetween('p.purchase_date', [$start_date, $end_date])
            ->orderBy('p.purchase_date', 'asc')
            ->get();

        return view('report::product-wise-purchase-report.report', compact('report_data', 'product', 'start_date', 'end_date'))->render();
    }
}
