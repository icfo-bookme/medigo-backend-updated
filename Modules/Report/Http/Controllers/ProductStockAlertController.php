<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;

class ProductStockAlertController extends BaseController
{
    public function index()
    {
        if(permission('product-stock-alert-report-access')){
            $this->setPageData('Product Stock Alert Report','Product Stock Alert Report','fas fa-file',[['name' => 'Product Stock Alert Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses(),
                'products' => Product::toBase()->get(),
            ];
            return view('report::stock-alert.index',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function report_data(Request $request)
    {

        $product_id = $request->product_id;
        $warehouse_id = $request->warehouse_id;
        $products = Product::with('category','unit','brand')
        ->when($product_id,function($q) use ($product_id){
            $q->where('id',$product_id);
        })->get();

        return view('report::stock-alert.report',compact('products','warehouse_id'))->render();

    }
}
