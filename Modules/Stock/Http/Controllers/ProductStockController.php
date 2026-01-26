<?php

namespace Modules\Stock\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Stock\Entities\WarehouseProduct;

class ProductStockController extends BaseController{
    public function __construct(WarehouseProduct $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('finished-goods-stock-access')){
            $setTitle = 'Products Stock';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            $data = [
                'products'   => Product::where('status',1)->pluck('name','id'),
                'warehouses' => Warehouse::pluck('name','id'),
                'companies' => Brand::pluck('name','id')
            ];
            return view('stock::product.index',$data);
        }else{
            return $this->access_blocked();
        }
    }
    public function get_product_stock_data(Request $request){
        if($request->ajax() && permission('finished-goods-stock-access')){
            $fields = [
                'product_id' => 'setProductID',
                'company_id' => 'setCompanyId',
                'sort_table' => 'setTableOrder',
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();

            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;

                $name = $value->product->name;

                if(isset($value->unit->unit_name)){
                    $name .= " - {$value->unit->unit_name}";
                }


                $row    = [];
                $row[]  = $no;
                $row[]  = $name;
                $row[]  = $value->item_code;
                $row[]  = $value->product->company ? $value->product->company->name : '';
                $row[]  = $value->product->generic ? $value->product->generic->generic_name : '';
                $row[]  = $value->price;
                $row[]  = $value->qty;
                $row[]  = $value->qty * $value->price;
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(), $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }

    }
}
