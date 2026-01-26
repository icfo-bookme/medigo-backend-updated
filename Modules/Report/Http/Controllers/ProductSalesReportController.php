<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;

class ProductSalesReportController extends BaseController
{

    public function index()
    {
        if(permission('product-sales-report-access')){
            $this->setPageData('Product Sales Report','Product Sales Report','fas fa-file',[['name' => 'Report'],['name' => 'Product Sales Report']]);
            $products = Product::orderBy('id','asc')->pluck('name','id');
            $warehouses = Warehouse::allWarehouses();
            return view('report::product-sales-report.index',compact('products','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $warehouse_id = $request->warehouse_id;

        $report_data = DB::table('products as p')
        ->select('p.id','p.name','p.code','u.unit_name', 'saleData.*')
        ->leftJoin('units as u','p.unit_id','=','u.id');
        if($warehouse_id)
        {
            $report_data->leftJoin(DB::raw("(SELECT sp.product_id,
            ifnull(SUM(sp.qty),0) as qty,ifnull(sum(sp.total),0) as total FROM sale_products as sp
            INNER JOIN sales as s ON sp.sale_id = s.id  WHERE (s.warehouse_id='$warehouse_id' AND s.sale_date BETWEEN '$start_date' AND '$end_date') GROUP BY sp.product_id) as saleData"),
            function($join)
            {
            $join->on('p.id', '=', 'saleData.product_id');
            });
        }else{
            $report_data->leftJoin(DB::raw("(SELECT sp.product_id,
            ifnull(SUM(sp.qty),0) as qty,ifnull(sum(sp.total),0) as total FROM sale_products as sp
            INNER JOIN sales as s ON sp.sale_id = s.id  WHERE s.sale_date BETWEEN '$start_date' AND '$end_date' GROUP BY sp.product_id) as saleData"),
            function($join)
            {
            $join->on('p.id', '=', 'saleData.product_id');
            });
        }


        $report_data = $report_data->orderBy('p.id', 'ASC')->get();

        return view('report::product-sales-report.report',compact('report_data','start_date','end_date'))->render();

    }
}
