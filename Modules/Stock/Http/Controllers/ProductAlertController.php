<?php

namespace Modules\Stock\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use Modules\Stock\Entities\ProductAlert;

class ProductAlertController extends BaseController {
    public function __construct(ProductAlert $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('product-alert-access')){
            $setTitle  = 'Product Alert';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            $products  = Product::all();
            return view('stock::productAlert.index',compact('products'));
        }else{
            return $this->access_blocked();
        }
    }
    public function getDataTableData(Request $request){
        if($request->ajax() && permission('product-alert-access')){
            if (!empty($request->product_id)) {
                $this->model->setProductID($request->product_id);
            }
            $this->set_datatable_default_properties($request);
            $list = $this->model->getDatatableList();
            $data = [];
            $no   = $request->input('start');
            foreach ($list as $value) {
                if($value->alertQty >= $value->warehouseQty){
                    $no++;
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = '<span class="label label-danger label-pill label-inline" style="min-width:100px !important;">'.$value->productName.'</span>';
                    $row[]  = $value->productCode;
                    $row[]  = '<span class="label label-danger label-pill label-inline" style="min-width:100px !important;">'.$value->categoryName.'</span>';
                    $row[]  = $value->unitName.'('. $value->unitCode .')';
                    $row[]  = '<span class="label label-warning label-pill label-inline" style="min-width:100px !important;">'.$value->alertQty.'</span>';
                    $row[]  = '<span class="label label-danger label-pill label-inline" style="min-width:100px !important;">'.$value->warehouseQty.'</span>';
                    $data[] = $row;
                }
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(), $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
