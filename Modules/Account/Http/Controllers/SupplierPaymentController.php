<?php

namespace Modules\Account\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\SupplierPayment;
use Modules\Account\Entities\Transaction;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Modules\Account\Http\Requests\SupplierPaymentFormRequest;

class SupplierPaymentController extends BaseController{
    public function __construct(SupplierPayment $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('supplier-payment-access')){
            $setTitle = 'Accounts';
            $setSubTitle = 'Supplier Payment List';
            $this->setPageData($setSubTitle,$setSubTitle,'far fa-money-bill-alt',[['name'=>$setTitle ],['name'=>$setSubTitle]]);
            $suppliers = Supplier::with('coa')->get();
            return view('account::supplier-payment.index',compact('suppliers'));
        }else{
            return $this->access_blocked();
        }
    }
    public function get_datatable_data(Request $request){
        if($request->ajax()){
            if(permission('supplier-payment-access')){
                if (!empty($request->start_date)) {
                    $this->model->setStartDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setEndDate($request->end_date);
                }
                if (!empty($request->supplier_coa_id)) {
                    $this->model->setVendorCOAID($request->supplier_coa_id);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('supplier-payment-details')){
                        $action .= ' <a class="dropdown-item" href="'.route('supplier.payment.show',['id' => $value->id]).'">'.self::ACTION_BUTTON['View'].'</a>';
                    }
                    if(permission('supplier-payment-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->voucher_no . '" data-name="' . $value->voucher_no . '">'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    $account_head = Transaction::with('coa')->where('voucher_no',$value->voucher_no)->orderBy('id','desc')->first();
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = date('d-M-Y',strtotime($value->voucher_date));
                    $row[]  = $value->voucher_no ?? '';
                    $row[]  = $value->supplier_name;
                    $row[]  = $account_head ? $account_head->coa->name : '';
                    $row[]  = $value->description;
                    $row[]  = number_format($value->debit,2);
                    $row[]  = $value->created_by;
                    $row[]  = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function create(){
        if(permission('supplier-payment-access')){
            $this->setPageData('Supplier Payment','Supplier Payment','far fa-money-bill-alt',[['name'=>'Accounts'],['name'=>'Supplier Payment']]);
            $voucher_no = 'PM-'.date('ymd').rand(1,999);
            $suppliers = Supplier::where('status',1)->get();
            return view('account::supplier-payment.create',compact('voucher_no','suppliers'));
        }else{
            return $this->access_blocked();
        }
    }
    public function store(SupplierPaymentFormRequest $request){
        if($request->ajax()){
            if(permission('supplier-payment-access')){
                DB::beginTransaction();
                try {
                    $supplier = Supplier::with('coa')->find($request->supplier_id);
                    $vtype = 'PM';
                    /****************/
                    $supplierdebit = array(
                        'chart_of_account_id' => $supplier->coa->id,
                        'warehouse_id'        => 1,
                        'voucher_no'          => $request->voucher_no,
                        'voucher_type'        => $vtype,
                        'voucher_date'        => $request->voucher_date,
                        'description'         => $request->remarks,
                        'debit'               => $request->amount,
                        'credit'              => 0,
                        'posted'              => 1,
                        'approve'             => 1,
                        'created_by'          => auth()->user()->name,
                        'created_at'          => date('Y-m-d H:i:s')
                    );
                    if($request->payment_type == 1){
                        //Cah In Hand For Supplier
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => 1,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Paid to ' . $supplier->name,
                            'debit'               => 0,
                            'credit'              => $request->amount,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')

                        );
                    }else{
                        // Bank Ledger
                        $payment = array(
                            'chart_of_account_id' => $request->account_id,
                            'warehouse_id'        => 1,
                            'voucher_no'          => $request->voucher_no,
                            'voucher_type'        => $vtype,
                            'voucher_date'        => $request->voucher_date,
                            'description'         => 'Supplier Payment To ' . $supplier->name,
                            'debit'               => 0,
                            'credit'              => $request->amount,
                            'posted'              => 1,
                            'approve'             => 1,
                            'created_by'          => auth()->user()->name,
                            'created_at'          => date('Y-m-d H:i:s')
                        );
                    }

                    $supplier_transaction = $this->model->create($supplierdebit);
                    $payment_transaction = $this->model->create($payment);
                    if($supplier_transaction && $payment_transaction){
                        $output = ['status'=>'success','message' => 'Payment Data Saved Successfully'];
                        $output['supplier_transaction'] = $supplier_transaction->id;
                    }else{
                        $output = ['status'=>'error','message' => 'Failed To Save Payment Data'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function show(int $id){
        if(permission('supplier-payment-details')){
            $setTitle = 'Supplier Payment Voucher Print';
            $this->setPageData($setTitle,$setTitle,'far fa-money-bill-alt',[['name'=>$setTitle]]);
            $data = $this->model->with('coa')->find($id);
            return view('account::supplier-payment.print',compact('data'));
        }else{
            return $this->access_blocked();
        }
    }
    public function delete(Request $request){
        if($request->ajax()){
            if(permission('supplier-payment-delete')){
                try {
                    $result  = $this->model->where('voucher_no',$request->id)->delete();
                    $output   = $this->delete_message($result);
                } catch (\Throwable $th) {
                    $output = ['status' => 'error','message' => $th->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json($this->unauthorized());
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
