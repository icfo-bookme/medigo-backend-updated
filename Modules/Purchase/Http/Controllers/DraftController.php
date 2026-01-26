<?php

namespace Modules\Purchase\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Tax;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Modules\Purchase\Entities\Draft;
use Modules\Purchase\Entities\Purchase;
use Modules\Purchase\Http\Requests\DraftFormRequest;
use Modules\Supplier\Entities\Supplier;

class DraftController extends BaseController
{
    private const INVOICE_NO = 1001;
    public function __construct(Draft $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('draft-access')){
            $setTitle = __('Draft');
            $this->setPageData($setTitle,$setTitle,'fas fa-th-list',[['name' => $setTitle]]);
            return view('purchase::draft.index');
        }else{
            return $this->access_blocked();
        }
    }
    public function getDataTableData(Request $request){
        if($request->ajax() && permission('draft-access')){
            if (!empty($request->draft_no)) {
                $this->model->setInvoiceNo($request->draft_no);
            }
            if (!empty($request->status)) {
                $this->model->setStatus($request->status);
            }
            $this->set_datatable_default_properties($request);
            $list = $this->model->getDatatableList();
            $data = [];
            $no   = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if(permission('draft-edit') && $value->status !=2){
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '" data-name="' . $value->draft_no . '" data-description="'. $value->description .'" data-amount="'. $value->amount .'">'.$this->actionButton('Edit').'</a>';

                    $action .= ' <a class="dropdown-item status-change" data-id="' . $value->id . '" data-status="' . $value->status . '">'.$this->actionButton('Change Status').'</a>';
                }
                if(permission('draft-delete') && $value->status !=2){
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->draft_no . '">'.$this->actionButton('Delete').'</a>';
                }
                if(permission('purchase-add') && $value->status == 2){
                    $action .= ' <a class="dropdown-item" href="'. route('draft.create.purchase', $value->id) .'" >'.$this->actionButton('Purchase').'</a>';
                }
                $row    = [];
                $row[]  = $no;
                $row[]  = $value->draft_no;
                $row[]  = $value->amount;
                $row[]  = DRAFT_LABEL[$value->status];
                $row[]  = $value->created_by;
                $row[]  = $value->modified_by != null ? $value->modified_by : '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;"></span>';
                $row[]  = action_button($action);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(), $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function storeOrUpdate(DraftFormRequest $request){
        
        if($request->ajax() && permission('draft-add')){
            $collection   = collect($request->validated());
            $collection   = $this->track_data($collection,$request->update_id);
            $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
            $output       = $this->store_message($result, $request->update_id);
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function delete(Request $request){
        if($request->ajax() && permission('draft-delete')){
            $result   = $this->model->find($request->id)->delete();
            $output   = $this->delete_message($result);
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function changeStatus(Request $request){
        if($request->ajax() && permission('draft-edit')){
            $result   = $this->model->find($request->draft_id)->update(['status' => $request->status]);
            $output   = $result ? ['status' => 'success','message' => $this->responseMessage('Status Changed')] : ['status' => 'error','message' => $this->responseMessage('Status Changed Failed')];
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function createPurchase($id)
    {
        if (permission('purchase-add')) {
            $this->setPageData('Add Purchase', 'Add Purchase', 'fas fa-shopping-cart', [['name' => 'Add Purchase']]);

            $purchase = Purchase::select('invoice_no')->orderBy('invoice_no', 'desc')->first();
            $data = [
                'suppliers' => Supplier::where('status', 1)->get(),
                'taxes' => Tax::activeTaxes(),
                'purchase_data' => Session::get('purchase_data'),
                'invoice_no' => 'PINV-' . ($purchase ? explode('PINV-', $purchase->invoice_no)[1] + 1 : self::INVOICE_NO),
                'draftValue' => $this->model->find($id)
            ];
            return view('purchase::draft.purchase', $data);
        } else {
            return $this->access_blocked();
        }
    }
}
