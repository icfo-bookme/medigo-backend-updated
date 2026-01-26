<?php

namespace Modules\StockReturn\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\Transaction;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleProduct;
use Modules\StockReturn\Entities\StockReturn;
use Modules\StockReturn\Entities\StockReturnProduct;
use Modules\StockReturn\Http\Requests\StockReturnRequest;

class StockReturnController extends BaseController
{
    public function __construct(StockReturn $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('stock-return-access')) {
            $this->setPageData('Stock Return', 'Stock Return', 'fas fa-file', [['name' => 'Stock Return']]);
            $customers = Customer::where('status', 1)->get();
            return view('stockreturn::sale.index', compact('customers'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('stock-return-access')) {
            $this->model->setType(1);

            $fields = [
                'return_no' => 'setReturnNo',
                'invoice_no' => 'setInvoiceNo',
                'start_date' => 'setStartDate',
                'end_date' => 'setEndDate',
                'customer_id' => 'setCustomerID',
                'sort_table' => 'setTableOrder',
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }

            $this->set_datatable_default_properties($request); //set datatable default properties
            $list = $this->model->getDatatableList(); //get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('stock-return-view')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . route("stock.return.show", $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';
                }
                if (permission('stock-return-access') && $value->is_paid == 1) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->return_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    if ($value->sale_payment_status != 3) {
                        $action .= ' <a class="dropdown-item approve-payment"  data-id="' . $value->id . '" data-name="' . $value->return_no . '">' . self::ACTION_BUTTON['Change Status'] . '</a>';
                    }
                }

                $row = [];
                if (permission('stock-return-access')) {
                    $row[] = row_checkbox($value->id);
                }
                $row[] = $no;
                $row[] = '<div class="text-left"><b>Sale Inv: </b>' . $value->invoice_no . '<br><b>Return Inv: </b>' . $value->return_no . '</div>';
                $row[] = $value->customer_name ?? '';
                $row[] = date(config('settings.date_format'), strtotime($value->return_date));
                $row[] = number_format($value->grand_total, 2);
                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw(
                $request->input('draw'),
                $this->model->count_all(),
                $this->model->count_filtered(),
                $data
            );
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id)
    {
        if (permission('stock-return-view')) {
            $this->setPageData('Sale Return Details', 'Sale Return Details', 'fas fa-file', [['name' => 'Sale Return Details']]);
            $sale = $this->model->with('return_products', 'customer')->find($id);
            return view('stockreturn::sale.view', compact('sale'));
        } else {
            return $this->access_blocked();
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('stock-return-access')) {
            DB::beginTransaction();
            try {
                $saleData = $this->model->with('return_products')->find($request->id);
                $invoice_no = $saleData->invoice_no;

                if (!$saleData->return_products->isEmpty()) {
                    foreach ($saleData->return_products as $return_product) {
                        $return_qty = $return_product->return_qty;
                        $sale_unit = Unit::find($return_product->unit_id);
                        if ($sale_unit->operator == '*') {
                            $return_qty = $return_qty * $sale_unit->operation_value;
                        } else {
                            $return_qty = $return_qty / $sale_unit->operation_value;
                        }

                        if ($return_product->product_variant_id) {
                            $product_variant = ProductUnit::find($return_product->product_variant_id);
                            if ($product_variant) {
                                $product_variant->decrement('qty', $return_qty);
                            }
                        }
                    }
                    $saleData->return_products()->delete();
                }
                Transaction::where(['voucher_no' => $invoice_no, 'voucher_type' => 'Return'])->delete();

                $result = $saleData->delete();
                if ($result) {
                    $output = ['status' => 'success', 'message' => 'Data has been deleted successfully'];
                } else {
                    $output = ['status' => 'error', 'message' => 'Failed to delete data'];
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function bulk_delete(Request $request)
    {
        if ($request->ajax() && permission('stock-return-access')) {
            foreach ($request->ids as $id) {
                DB::beginTransaction();
                try {
                    $saleData = $this->model->with('return_products')->find($id);
                    $invoice_no = $saleData->invoice_no;

                    if (!$saleData->return_products->isEmpty()) {
                        foreach ($saleData->return_products as $return_product) {
                            $return_qty = $return_product->return_qty;
                            $sale_unit = Unit::find($return_product->unit_id);
                            if ($sale_unit->operator == '*') {
                                $return_qty = $return_qty * $sale_unit->operation_value;
                            } else {
                                $return_qty = $return_qty / $sale_unit->operation_value;
                            }

                            if ($return_product->product_variant_id) {
                                $product_variant = ProductUnit::find($return_product->product_variant_id);
                                if ($product_variant) {
                                    $product_variant->decrement('qty', $return_qty);
                                }
                            }
                        }
                        $saleData->return_products()->delete();
                    }
                    Transaction::where(['voucher_no' => $invoice_no, 'voucher_type' => 'Return'])->delete();

                    $result = $saleData->delete();
                    if ($result) {
                        $output = ['status' => 'success', 'message' => 'Data has been deleted successfully'];
                    } else {
                        $output = ['status' => 'error', 'message' => 'failed to delete data'];
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
                return response()->json($output);
            }
        } else {
            return response()->json($this->access_blocked());
        }
    }

    public function stockReturn($id)
    {
        if (permission('sale-return')) {
            $setTitle = 'Sale';
            $setSubTitle = 'Return';
            $this->setPageData($setSubTitle, $setSubTitle, 'fas fa-edit', [['name' => $setTitle, 'link' => route('sale')], ['name' => $setSubTitle]]);
            $sale = Sale::with('customer', 'sale_products', 'sale_products.product', 'sale_products.unit')->find($id);
            return view('stockreturn::sale.return_form', compact('sale'));
        } else {
            return $this->access_blocked();
        }
    }

    public function stockReturnStore(StockReturnRequest $request)
    {
        if ($request->ajax() && permission('sale-return')) {
            try {
                DB::beginTransaction();
                date_default_timezone_set('Asia/Dhaka');
                $sale_data = Sale::where('invoice_no', $request->invoice_no)->select('id', 'invoice_no', 'warehouse_id', 'shipping_cost')->first();
                $sale_return_data = [
                    'warehouse_id' => Auth::user()->warehouse_id,
                    'return_no' => 'SRINV-' . date('YmdH') . rand(1, 999),
                    'invoice_no' => $request->invoice_no,
                    'type' => 1,
                    'customer_id' => $request->customer_id,
                    'customer_name' => $request->customer_name,
                    'total_price' => $request->total_price,
                    'total_deduction' => $request->total_deduction ? $request->total_deduction : null,
                    'tax_rate' => $request->tax_rate ? $request->tax_rate : null,
                    'total_tax' => $request->total_tax ? $request->total_tax : null,
                    'grand_total' => $request->grand_total_price,
                    'reason' => $request->reason,
                    'date' => $request->sale_date,
                    'return_date' => $request->return_date,
                    'created_by' => Auth::user()->name
                ];

                $sale_return = $this->model->create($sale_return_data);

                //Stock Return User Activity
                $sale_return->user_activity()->create([
                    'activity_type' => 'return_create',
                    'status_name' => 'Created',
                    'user_id' => Auth::id()
                ]);

                $products = [];
                if ($request->has('products')) {
                    foreach ($request->products as $value) {
                        if ($value['return'] == 1) {
                            $unit = Unit::where('unit_name', $value['unit'])->first();
                            if ($unit->operator == '*') {
                                $qty = $value['return_qty'] * $unit->operation_value;
                            } else {
                                $qty = $value['return_qty'] / $unit->operation_value;
                            }

                            $product_unit = ProductUnit::where([
                                'product_id' => $value['id'],
                                'item_code' => $value['code']
                            ])->first();

                            $products[] = [
                                'warehouse_id' => Auth::user()->warehouse_id,
                                'stock_return_id' => $sale_return->id,
                                'invoice_no' => $request->invoice_no,
                                'item_code' => $value['code'],
                                'product_id' => $value['id'],
                                'product_variant_id' => $product_unit->id ?? $value['variant_id'],
                                'return_qty' => $value['return_qty'],
                                'unit_id' => $unit ? $unit->id : null,
                                'product_rate' => $value['net_unit_price'],
                                'deduction_rate' => $value['deduction_rate'] ?? null,
                                'deduction_amount' => $value['deduction_amount'] ?? null,
                                'total' => $value['total']
                            ];

                            $sale_data->update(['delivery_status' => $request->delivery_status]);

                            if (!empty($request->balance)) {
                                SaleProduct::where([
                                    'sale_id' => $sale_data->id,
                                    'product_id' => $value['id'],
                                    'product_variant_id' => $value['variant_id']
                                ])->update(['return_status' => $request->delivery_status]);
                            }

                            if ($product_unit) {
                                $product_unit->increment('qty', $qty);
                            }
                        }
                    }
                    if (count($products) > 0) {
                        StockReturnProduct::insert($products);
                    }
                }

                if (!empty($request->customer_id)) {
                    $customer = Customer::with('coa')->find($request->customer_id);
                    $customer_credit = array(
                        'chart_of_account_id' => $customer->coa->id,
                        'warehouse_id' => Auth::user()->warehouse_id ?? '',
                        'voucher_no' => $request->invoice_no,
                        'voucher_type' => 'Return',
                        'voucher_date' => $request->return_date,
                        'description' => 'Customer ' . $customer->name . ' credit for Product Return Invoice NO- ' . $request->invoice_no,
                        'debit' => 0,
                        'credit' => $request->grand_total_price,
                        'posted' => 1,
                        'approve' => 1,
                        'created_by' => auth()->user()->name,
                        'created_at' => date('Y-m-d H:i:s')
                    );
                    Transaction::create($customer_credit);
                }

                if (!empty($request->balance)) {
                    $sale_data->update(['collection_amount' => $request->balance]);
                    $total_tax = 0;
                    $grand_total = $request->balance;
                    $this->sale_balance_add_courier($request->invoice_no, $grand_total, $total_tax, date('Y-m-d'), $sale_data->warehouse_id, $request->customer_id);
                }
                $output = $this->store_message($sale_return, null);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function stockReturnLog(Request $request)
    {
        if ($request->ajax() && permission('stock-return-access')) {
            $sale = $this->model->with('user_activity')->find($request->id);
            return view('sale::log-data', compact('sale'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }

    private function sale_balance_add_courier($invoice_no, $grand_total, $total_tax, $sale_date, $warehouse_id, $customer_id)
    {
        $product_sale_income = array(
            'chart_of_account_id' => 8,
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $sale_date,
            'description' => 'Sale Income ' . $grand_total . 'TK from Invoice No. - ' . $invoice_no,
            'debit' => 0,
            'credit' => $grand_total,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->name ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        );
        Transaction::create($product_sale_income);
        $customer_credit = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('customer_id', $customer_id)->value('id'),
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $sale_date,
            'description' => 'Courier debit ' . $grand_total . 'TK from Invoice No. - ' . $invoice_no,
            'debit' => $grand_total,
            'credit' => 0,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->name ?? '',
            'created_at' => date('Y-m-d H:i:s'),
        );
        Transaction::create($customer_credit);
    }

    public function approveRefund(Request $request)
    {
        if ($request->ajax() && permission('stock-return-access')) {
            DB::beginTransaction();
            try {
                $stockReturn = $this->model->find($request->id);
                if ($stockReturn->sale_payment_status) {
                    $this->companyLiability($stockReturn->warehouse_id, $stockReturn->return_no, $stockReturn->return_date, $stockReturn->grand_total, $stockReturn->account_id);
                }
                $stockReturn->update([
                    'is_paid' => 2
                ]);
                $output = ['status' => 'success', 'message' => 'Refund Approved'];
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function companyLiability($warehouse_id, $invoice_no, $return_date, $grand_total, $cashBankId)
    {
        $refund = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', '50205')->value('id'),
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $return_date,
            'description' => 'Sale Refund ' . $grand_total . 'TK from Invoice No. - ' . $invoice_no,
            'debit' => $grand_total,
            'credit' => 0,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->name,
            'created_at' => date('Y-m-d H:i:s')
        );
        Transaction::create($refund);
        $payment = array(
            'chart_of_account_id' => $cashBankId,
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $return_date,
            'description' => 'Cash Paid amount ' . $grand_total . 'Tk for Invoice No. - ' . $invoice_no,
            'debit' => 0,
            'credit' => $grand_total,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->name,
            'created_at' => date('Y-m-d H:i:s')
        );
        Transaction::create($payment);
    }
}
