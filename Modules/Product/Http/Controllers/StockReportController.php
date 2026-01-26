<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;

class StockReportController extends BaseController
{
    public function index()
    {
        if(permission('stock-report-access')){
            $this->setPageData('Stock Report','Stock Report','fas fa-file',[['name' => 'Stock Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses(),
                'products'   => Product::toBase()->get(),
            ];
            return view('product::stock-report.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {
        $product_id = $request->product_id;
        $products   = ProductUnit::with('product')
                    ->when($product_id,function ($q) use ($product_id){
                        $q->where('product_id',$product_id);
                    })->get();

        return view('product::stock-report.report',compact('products'))->render();

    }
}
