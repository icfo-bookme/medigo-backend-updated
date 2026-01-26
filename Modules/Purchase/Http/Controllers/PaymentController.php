<?php

namespace Modules\Purchase\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Purchase\Entities\Purchase;
use Modules\Supplier\Entities\Supplier;
use Modules\Account\Entities\Transaction;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\Purchase\Http\Requests\PaymentFormRequest;

class PaymentController extends Controller
{
    public function store_or_update(PaymentFormRequest $request)
    {
        if($request->ajax())
        {
            if(permission('purchase-payment-add')){
                DB::beginTransaction();
                try {
                    $purchase_data = Purchase::find($request->purchase_id);
                    if($purchase_data){
                        $supplier = Supplier::with('coa')->find($purchase_data->supplier_id);
                        $payment_data = [
                            'payment_id'      => $request->payment_id,
                            'account_id'      => $request->account_id,
                            'purchase_id'     => $request->purchase_id,
                            'purchase_no'     => $purchase_data->invoice_no,
                            'amount'          => $request->amount,
                            'payment_method'  => $request->payment_method,
                            'reference_no'    => $request->payment_method == 2 ? $request->reference_no : null,
                            'supplier_coa_id' => $supplier->coa->id,
                            'supplier_name'   => $supplier->name,
                            'purchase_date'   => $purchase_data->purchase_date,
                            'status'          => $purchase_data->status
                        ];
                        if(empty($request->payment_id)){
                            $purchase_data->paid_amount += $request->amount;
                            $balance = $purchase_data->grand_total - $purchase_data->paid_amount;
                            if($balance == 0)
                            {
                                $purchase_data->payment_status = 1;//paid
                            }else if($balance == $purchase_data->grand_total)
                            {
                                $purchase_data->payment_status = 3;//due
                            }else{
                                $purchase_data->payment_status = 2;//partial
                            }
                            $payment_data['supplier_debit_transaction_id'] = '';
                            $payment_data['transaction_id'] = '';
                        }else{
                            $payment = PurchasePayment::find($request->payment_id);
                            $amount_diff = $payment->amount - $request->amount;
                            $purchase_data->paid_amount -= $amount_diff;
                            $balance = $purchase_data->grand_total - $purchase_data->paid_amount;
                            if($balance == 0)
                            {
                                $purchase_data->payment_status = 1;//paid
                            }else if($balance == $purchase_data->grand_total)
                            {
                                $purchase_data->payment_status = 3;//due
                            }else{
                                $purchase_data->payment_status = 2;//partial
                            }

                            $payment_data['supplier_debit_transaction_id'] = $payment->supplier_debit_transaction_id;
                            $payment_data['transaction_id'] = $payment->transaction_id;
                        }
                        
                        $purchase_data->due_amount = $balance;
                        $purchase_data->modified_by = auth()->user()->name;
                        $purchase_data->update();
                        $result = $this->purchase_payment($payment_data);
                        $output = $result ? ['status'=>'success','message'=> 'Payment Data Saved Successfully'] : ['status'=>'error','message'=> 'Failed to Save Payment Data'];
                        
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status'=>'error','message'=>$e->getMessage()];
                }
                return response()->json($output);

            }else{
                return response()->json(['status'=>'error','message'=>'Unauthorized Access Blocked']);
            }
        }
    }

    private function purchase_payment(array $payment_data)
    {
        /****************/
        $supplierdebit = array(
            'chart_of_account_id' => $payment_data['supplier_coa_id'],
            'voucher_no'          => $payment_data['purchase_no'],
            'voucher_type'        => 'Purchase',
            'voucher_date'        => $payment_data['purchase_date'],
            'description'         => 'Supplier .' . $payment_data['supplier_name'],
            'debit'               => $payment_data['amount'],
            'credit'              => 0,
            'posted'              => 1,
            'approve'             => $payment_data['status'] == 1 ? 1 : 2,
        );
        if($payment_data['payment_method'] == 1){
            //Cah In Hand For Supplier
            $payment = array(
                'chart_of_account_id' => $payment_data['account_id'],
                'voucher_no'          => $payment_data['purchase_no'],
                'voucher_type'        => 'Purchase',
                'voucher_date'        => $payment_data['purchase_date'],
                'description'         => 'Cash in Hand For Supplier ' . $payment_data['supplier_name'],
                'debit'               => 0,
                'credit'              => $payment_data['amount'],
                'posted'              => 1,
                'approve'             => $payment_data['status'] == 1 ? 1 : 2,
                
            );
        }else{
            // Bank Ledger
            $payment = array(
                'warehouse_id' => 1,
                'chart_of_account_id' => $payment_data['account_id'],
                'voucher_no'          => $payment_data['purchase_no'],
                'voucher_type'        => 'Purchase',
                'voucher_date'        => $payment_data['purchase_date'],
                'description'         => 'Paid amount for Supplier  ' . $payment_data['supplier_name'],
                'debit'               => 0,
                'credit'              => $payment_data['amount'],
                'posted'              => 1,
                'approve'             => $payment_data['status'] == 1 ? 1 : 2,
            );
        }

        if($payment_data['payment_id']){
            $supplierdebit['modified_by'] = auth()->user()->name;
            $supplierdebit['updated_at']  = date('Y-m-d H:i:s');
            $payment['modified_by'] = auth()->user()->name;
            $payment['updated_at']  = date('Y-m-d H:i:s');
        }else{
            $supplierdebit['created_by'] = auth()->user()->name;
            $supplierdebit['created_at']  = date('Y-m-d H:i:s');
            $payment['created_by'] = auth()->user()->name;
            $payment['created_at']  = date('Y-m-d H:i:s');
        }

        $supplier_debit_transaction = Transaction::updateOrCreate(['id'=> $payment_data['supplier_debit_transaction_id']],$supplierdebit);
        $payment_transaction        = Transaction::updateOrCreate(['id'=> $payment_data['transaction_id']],$payment);

        if($supplier_debit_transaction && $payment_transaction){
            $data = [
                'purchase_id'                   => $payment_data['purchase_id'],
                'account_id'                    => $payment_data['account_id'],
                'transaction_id'                => $payment_transaction->id,
                'supplier_debit_transaction_id' => $supplier_debit_transaction->id,
                'amount'                        => $payment_data['amount'],
                'payment_method'                => $payment_data['payment_method'],
                'reference_no'                     => $payment_data['reference_no'],
            ];
            if($payment_data['payment_id']){
                $data['modified_by'] = auth()->user()->name;
            }else{
                $data['created_by'] = auth()->user()->name;
            }
           $result = PurchasePayment::updateOrCreate(['id'=>$payment_data['payment_id']],$data);
           return $result;
        }
        return false;
    }

    public function show(Request $request)
    {
        if($request->ajax())
        {
            if(permission('purchase-payment-view')){
                $payments = PurchasePayment::with('purchase','account')->where('purchase_id',$request->id)->get();
                return view('purchase::payment.view',compact('payments'))->render();
            }
        }
    }

    public function delete(Request $request)
    {
        if($request->ajax())
        {
            if(permission('purchase-payment-delete')){
                DB::beginTransaction();
                try {
                    $payment_data = PurchasePayment::find($request->id);
                    $purchase_data = Purchase::find($payment_data->purchase_id);
                    $purchase_data->paid_amount -= $payment_data->amount;
                    $balance = $purchase_data->grand_total - $purchase_data->paid_amount;
                    if($balance == 0)
                    {
                        $purchase_data->payment_status = 1;//paid
                    }else if($balance == $purchase_data->grand_total)
                    {
                        $purchase_data->payment_status = 3;//due
                    }else{
                        $purchase_data->payment_status = 2;//partial
                    }
                    if($purchase_data->update()){
                        $result = $payment_data->delete();
                        Transaction::whereIn('id',[$payment_data->supplier_debit_transaction_id,$payment_data->transaction_id])->delete();
                        
                        $output = $result ?  ['status'=>'success','message'=>'Data deleted successfully'] :  ['status'=>'error','message'=>'Faild to delete data'];
                    }else{
                        $output = ['status'=>'error','message'=>'Faild to delete data'];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
                return response()->json($output);
            }else{
                return response()->json( ['status'=>'error','message'=>'Unauthorized Access Blocked']);
            }
        }
    }
}
