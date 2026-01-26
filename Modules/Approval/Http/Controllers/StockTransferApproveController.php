<?php

namespace Modules\Approval\Http\Controllers;

use Exception;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Stock\Entities\WarehouseProduct;
use Illuminate\Support\Facades\DB;
use Modules\Approval\Entities\StockTransferApprove;

class StockTransferApproveController extends BaseController {
    public function __construct(StockTransferApprove $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('stock-transfer-approve-access')){
            $setTitle = 'Stock Transfer Approve';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            return view('approval::stockTransfer.index');
        }else{
            return $this->access_blocked();
        }
    }
    public function getDataTableData(Request $request){
        if($request->ajax() && permission('stock-transfer-approve-access')){
            $this->set_datatable_default_properties($request);
            $list = $this->model->getDatatableList();
            $data = [];
            $no   = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action      = '';
                if (permission('stock-transfer-view')) {
                    $action .= ' <a class="dropdown-item" href="'.route("stock.transfer.approve.show",$value->id).'">'.$this->actionButton('View').'</a>';
                }
                if(permission('stock-transfer-change-status') && $value->status == 2){
                    $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-name="' . $value->invoice_no . '" data-status="' . $value->status . '">'.$this->actionButton('Change Status').'</a>';
                }
                $row    = [];
                $row[]  = $no;
                $row[]  = $value->invoice_no;
                $row[]  = $value->transfer_date;
                $row[]  = $value->transferWarehouse->name;
                $row[]  = $value->receiveWarehouse->name;
                $row[]  = $value->total_qty;
                $row[]  = STATUS_LABEL[$value->status];
                $row[]  = '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">'. $value->createdBy->name .'</span>';
                $row[]  = !empty($value->approved_id) ? '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">'. $value->approvedBy->username .'</span>' : '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;"></span>';
                $row[]  = action_button($action);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(), $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function show($id){
        if(permission('stock-transfer-approve-view')){
            $setTitle = 'Transfer Details';
            $this->setPageData($setTitle,$setTitle,'fas fa-boxes',[['name' => $setTitle]]);
            $data = [
                'details' => $this->model->with('createdBy','approvedBy','transferWarehouse','receiveWarehouse','stockTransferWarehouseProductList','stockTransferWarehouseProductList.product','stockTransferWarehouseProductList.product.unit')->findOrFail($id)
            ];
            return view('approval::stockTransfer.details',$data);
        }else{
            return $this->access_blocked();
        }
    }
    public function changeStatus(Request $request){
        if($request->ajax() && permission('stock-transfer-approve-change-status')){
            DB::beginTransaction();
            try{
                $data = $this->model->with('stockTransferWarehouseProductList')->find($request->id);
                $data->update(['status' => 1,'approved_id' => auth()->user()->id]);
                if(!empty($data->stockTransferWarehouseProductList)){
                    foreach($data->stockTransferWarehouseProductList as $value){
                        $transferProductWarehouse = WarehouseProduct::where(['warehouse_id' => $data->transfer_warehouse_id , 'product_id' => $value->product_id])->first();
                        $receiveProductWarehouse  = WarehouseProduct::where(['warehouse_id' => $data->receive_warehouse_id , 'product_id' => $value->product_id])->first();
                        $transferProductWarehouse->update(['qty' => $transferProductWarehouse->qty - $value->qty]);
                        if(!empty($receiveProductWarehouse)){
                            $receiveProductWarehouse->update(['qty' => $receiveProductWarehouse->qty + $value->qty]);
                        }else{
                            WarehouseProduct::create(['warehouse_id' => $data->receive_warehouse_id , 'product_id' => $value->product_id , 'qty' => $value->qty]);
                        }
                    }
                }
                $output = ['status' => 'success' , 'message' => 'Data Status Change Successfully'];
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
}
