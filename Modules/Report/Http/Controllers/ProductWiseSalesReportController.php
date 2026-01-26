<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;

class ProductWiseSalesReportController extends BaseController
{

    public function index()
    {
        if(permission('product-wise-sales-report-access')){
            $this->setPageData('Product Wise Sales Report','Product Wise Sales Report','fas fa-file',[['name' => 'Report'],['name' => 'Product Wise Sales Report']]);
            $products = Product::toBase()->orderBy('id','asc')->pluck('name','id');
            $warehouses = Warehouse::allWarehouses();
            return view('report::product-wise-sales-report.index',compact('products','warehouses'));
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
        $report_data  = DB::table('sale_products as sp')
        ->join('sales as s','sp.sale_id','=','s.id')
        ->join('products as p','sp.product_id','=','p.id')
        ->join('units as u','sp.sale_unit_id','=','u.id')
        ->selectRaw('p.id,p.name,p.code,u.unit_name,
        SUM(sp.qty) as qty, sum(s.grand_total) as total,s.sale_date')
        ->groupBy('s.sale_date')
        ->groupBy('sp.sale_unit_id')
        ->where(['sp.product_id'=>$product_id])
//        ->when($warehouse_id,function($q) use ($warehouse_id){
//            $q->where('s.warehouse_id',$warehouse_id);
//        })
        ->whereBetween('s.sale_date',[$start_date,$end_date])
        ->orderBy('s.sale_date','asc')
        ->get();
        // dd($report_data);
        return view('report::product-wise-sales-report.report',compact('report_data','product','start_date','end_date'))->render();

    }

    public function collection_index()
    {
        if(permission('collection-report-access')){
            $this->setPageData('Product Sales Collection Report','Product Sales Collection Report','fas fa-file',[['name' => 'Report'],['name' => 'Product Sales Collection Report']]);
            $products = Product::toBase()->orderBy('id','asc')->pluck('name','id');
            $warehouses = Warehouse::allWarehouses();
            return view('report::product-sales-collection-report.index',compact('products','warehouses'));
        }else{
            return $this->access_blocked();
        }
    }

    public function report_collection_data(Request $request) {
        $product_id   = $request->product_id;
        $start_date   = $request->start_date;
        $end_date     = $request->end_date;
        $product      = Product::find($product_id);
        $report_data  = DB::table('sale_products as sp')
                        ->join('sales as s','sp.sale_id','=','s.id')
                        ->join('products as p','sp.product_id','=','p.id')
                        ->join('units as u','sp.sale_unit_id','=','u.id')
                        ->selectRaw('s.invoice_no,p.id,p.name,p.code,u.unit_name,s.total_qty as qty, s.grand_total as total,s.sale_date')
                        ->groupBy('s.sale_date')
                        ->groupBy('s.invoice_no')
//                        ->groupBy('sp.product_id')
                        ->where('s.delivery_status',2)
//                        ->when($product,function($q) use ($product){
//                            $q->where('s.delivery_status',2);
//                        })
                        ->whereBetween('s.sale_date',[$start_date,$end_date])
                        ->orderBy('s.sale_date','asc')
                        ->get();
//        return $report_data;
        return view('report::product-sales-collection-report.report',compact('report_data','product','start_date','end_date'))->render();
    }


//    public function report_collection_data(Request $request) {
//        $product_id   = $request->product_id;
//        $start_date   = $request->start_date;
//        $end_date     = $request->end_date;
//        $product      = Product::find($product_id);
//        $report_data  = DB::table('sale_products as sp')
//            ->join('sales as s','sp.sale_id','=','s.id')
//            ->join('products as p','sp.product_id','=','p.id')
//            ->join('units as u','sp.sale_unit_id','=','u.id')
//            ->selectRaw('p.id,p.name,p.code,u.unit_name,SUM(sp.qty) as qty, sum(s.grand_total) as total,s.sale_date')
//            ->groupBy('s.sale_date')
//            ->groupBy('sp.sale_unit_id')
//        ->when($product,function($q) use ($product){
//            $q->where('s.delivery_status',2);
//        })
//            ->whereBetween('s.sale_date',[$start_date,$end_date])
//            ->orderBy('s.sale_date','asc')
//            ->get();
//        return view('report::product-sales-collection-report.report',compact('report_data','product','start_date','end_date'))->render();
//    }
}
