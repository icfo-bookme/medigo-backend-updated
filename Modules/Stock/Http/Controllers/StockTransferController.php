<?php

namespace Modules\Stock\Http\Controllers;

use Exception;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use Modules\Stock\Entities\StockTransfer;
use Modules\Stock\Entities\StockTransferWarehouseProduct;
use Modules\Stock\Entities\WarehouseProduct;
use Modules\Stock\Http\Requests\StockTransferRequestForm;

class StockTransferController extends BaseController{
    public function __construct(StockTransfer $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('stock-transfer-access')){
            $setTitle = 'Stock Transfer';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            return view('stock::stockTransfer.index');
        }else{
            return $this->access_blocked();
        }
    }
    public function getDataTableData(Request $request){
        if($request->ajax() && permission('stock-transfer-access')){
            $this->set_datatable_default_properties($request);
            $list = $this->model->getDatatableList();
            $data = [];
            $no   = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action      = '';
                if (permission('stock-transfer-view')) {
                    $action .= ' <a class="dropdown-item" href="'.route("stock.transfer.show",$value->id).'">'.$this->actionButton('View').'</a>';
                }
                if (permission('stock-transfer-edit') && $value->status == 2) {
                    $action .= ' <a class="dropdown-item" href="'.route("stock.transfer.edit",$value->id).'">'.$this->actionButton('Edit').'</a>';
                }
                if(permission('stock-transfer-delete') && $value->status == 2){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->invoice_no . '">'.$this->actionButton('Delete').'</a>';
                }
                $row    = [];
                $row[]  = $no;
                $row[]  = $value->invoice_no;
                $row[]  = $value->transfer_date;
                $row[]  = $value->transferWarehouse->name;
                $row[]  = $value->receiveWarehouse->name;
                $row[]  = $value->total_qty;
                $row[]  = STATUS_LABEL[$value->status];
                $row[]  = $value->createdBy->name;
                $row[]  = action_button($action);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(), $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function create(){
        if(permission('stock-transfer-add')){
            $setTitle = 'Stock Transfer';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            $data = [
                'warehouses' => Warehouse::where('status',1)->get(),
            ];
            return view('stock::stockTransfer.create',$data);
        }else{
            return $this->access_blocked();
        }
    }
    public function store(StockTransferRequestForm $request){
        if($request->ajax() && permission('stock-transfer-add')){
            DB::beginTransaction();
            try{
                $warehouseProduct   = [];
                $invoiceNo          = 'Transfer-'.round(microtime(true) * 1000);
                $collection         = collect($request->all())->except('_token','transfer')->merge(['invoice_no' => $invoiceNo,'created_id' => auth()->user()->id]);
                $stockTransfer      = StockTransfer::create($collection->all());
                if($request->has('transfer')){
                    foreach ($request->transfer as $value){
                        if(!empty($value['product_id']) && !empty($value['qty'])){
                            $warehouseProduct[] = [
                                'stock_transfer_id' => $stockTransfer->id,
                                'product_id'        => $value['product_id'],
                                'qty'               => $value['qty'],
                            ];
                        }
                    }
                }
                StockTransferWarehouseProduct::insert($warehouseProduct);
                $output = ['status' => 'success' , 'message' => 'Stock Transfer Successfully'];
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $output = ['status' => 'error' , 'message' => $e->getMessage()];
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function show($id){
        if(permission('stock-transfer-view')){
            $setTitle = 'Transfer Details';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            $data = [
              'details' => $this->model->with('createdBy','transferWarehouse','receiveWarehouse','stockTransferWarehouseProductList','stockTransferWarehouseProductList.product','stockTransferWarehouseProductList.product.unit')->findOrFail($id)
            ];
            return view('stock::stockTransfer.details',$data);
        }else{
            return $this->access_blocked();
        }
    }
    public function edit($id){
        if(permission('stock-transfer-view')){
            $setTitle   = 'Transfer Details';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            $data = [
              'edit'       => $this->model->with('transferWarehouse','receiveWarehouse','stockTransferWarehouseProductList','stockTransferWarehouseProductList.product','stockTransferWarehouseProductList.product.unit')->findOrFail($id),
              'warehouses' => Warehouse::all()
            ];
            return view('stock::stockTransfer.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }
    public function update(StockTransferRequestForm $request){
        if($request->ajax() && permission('stock-transfer-edit')){
            DB::beginTransaction();
            try{
                $warehouseProduct   = [];
                $data               = $this->model->findOrFail($request->update_id);
                $collection         = collect($request->all())->except('_token','update_id','transfer');
                $data->update($collection->all());
                if($request->has('transfer')){
                    foreach ($request->transfer as $value){
                        if(!empty($value['product_id']) && !empty($value['qty'])){
                            $warehouseProduct[] = [
                                'stock_transfer_id' => $request->update_id,
                                'product_id'        => $value['product_id'],
                                'qty'               => $value['qty'],
                            ];
                        }
                    }
                }
                $data->stockTransferWarehouseProductList()->delete();
                StockTransferWarehouseProduct::insert($warehouseProduct);
                $output = ['status' => 'success' , 'message' => 'Data Update Successfully'];
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $output = ['status' => 'error' , 'message' => $e->getMessage()];
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function delete(Request $request){
        if($request->ajax() && permission('stock-transfer-delete')){
            DB::beginTransaction();
            try{
                $delete = $this->model->find($request->id);
                $delete->stockTransferWarehouseProductList()->delete();
                $delete->delete();
                $output = $this->delete_message($delete);
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $output = ['status' => 'error' , 'message' => $e->getMessage()];
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function warehouseProduct($warehouse_id){
        $data              = [];
        $warehouseProducts = WarehouseProduct::with('warehouse','product','product.unit')->where([['warehouse_id','=',$warehouse_id],['qty','!=',0]])->get();
        foreach ( $warehouseProducts as $value){
            $data[] = [
              'productId'     => $value->product_id,
              'productName'   => $value->product->name,
              'unitName'      => $value->product->unit->unit_name,
              'qty'           => $value->qty
            ];
        }
        return response()->json($data);
    }
}
