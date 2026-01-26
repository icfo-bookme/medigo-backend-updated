<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Models\Tax;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Customer\Entities\Customer;
use Modules\Sale\Entities\SaleNotification;
use Modules\Sale\Entities\SaleProduct;
use Modules\Sale\Entities\SaleProductReturn;
use Modules\Sale\Http\Controllers\Service\PosService;
use Modules\Setting\Entities\CustomerGroup;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Sale\Http\Requests\SaleFormRequest;
use Modules\Account\Entities\ChartOfAccount;

class SaleController extends BaseController
{
    public function __construct(Sale $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('sale-access')) {
            $this->setPageData('Sale Manage', 'Sale Manage', 'fab fa-opencart', [['name' => 'Sale Manage']]);
            $data = [
                'customers' => Customer::where('status', 1)->get(),
                'employee' => User::where('role_id', 3)->get(),
                'delivery_man' => User::where('role_id', 4)->get(),
                'warehouses' => Warehouse::allWarehouses(),
                'sale_data' => Session::get('sale_data'),
                'users' => User::whereIn('role_id', [1, 2])->get(),
            ];
            return view('sale::index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() and permission('sale-access')) {
            $fields = [
                'invoice_no' => 'setInvoiceNo',
                'order_source_id' => 'setOrderSource',
                'start_date' => 'setStartDate',
                'end_date' => 'setEndDate',
                'customer_id' => 'setCustomerID',
                'created_by' => 'setCreatedBy',
                'sort_table' => 'setTableOrder',
                'order_type' => 'setOrderType'
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }

            $this->set_datatable_default_properties($request);//set datatable default properties

            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if ($value->delivery_status < 5 and permission('sale-status')) {
                    $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '"  data-visa_status="' . $value->delivery_status . '" ><i class="fas fa-check-circle text-success mr-2"></i> Change Status</a>';
                }
                if ($value->delivery_status == 3 and permission('sale-assign')) {
                    $action .= ' <a class="dropdown-item assign_rider" data-id="' . $value->id . '" data-invoice_no="' . $value->invoice_no . '">' . $this->actionButton('Assign Delivery Man') . '</a>';
                }
                if ($value->delivery_status == 1 and permission('sale-edit')) {
                    $action .= ' <a class="dropdown-item" href="' . route("sale.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('sale-view')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . url("sale/pos-invoice", $value->id) . '" target="_blank">' . self::ACTION_BUTTON['View'] . '</a>';
                    $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';
                }
                if ($value->delivery_status == 1 and permission('sale-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->invoice_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }
                if (permission('sale-return') && $value->payment_status != 3 && !in_array($value->delivery_status, [7, 9])) {
                    if ($value->total_return_qty < $value->total_qty) {
                        $action .= '<a class="dropdown-item" href="' . route("stock.return", $value->id) . '">' . $this->actionButton('Return') . '</a>';
                    }
                    $action .= '<a class="dropdown-item" href="' . route("stock.exchange", $value->id) . '">' . $this->actionButton('Exchange') . '</a>';
                }

                $row = [];
                $row[] = $no;
                $row[] = ($value->order_source_id ? ORDER_SOURCE_LABEL[$value->order_source_id] : '') .
                    '<br><br><a class="text-info cursor-pointer view_invoice" data-id="' . $value->id . '">' . $value->invoice_no . '</a>' .
                    '<br><br><b>Date: </b>' . date(config('settings.date_format'), strtotime($value->sale_date));

                $row[] = '<div class="text-left"><b>Name: </b>' . $value->customer->name . '<br><b>Phone: </b>' . $value->customer->phone . '<br><b>Address: </b>' . $value->information . '</div>';

                $row[] = '<div class="text-left"><b>Item: ' . $value->item . '</b><br> <b>Qty: ' . $value->total_qty . '</b><br> <b>Price: ' . number_format($value->total_price, 2) . '</b><br> <b>Tax: ' . number_format($value->order_tax, 2) . '</b><br> <b>Discount: ' . number_format($value->order_discount, 2) . '</b><br> <b>Shipping: ' . number_format($value->shipping_cost, 2) . '</b><br> <b>Adjustment: ' . number_format($value->adjustment, 2) . '</b><br> <b>Grand Total: ' . number_format($value->grand_total, 2) . '</b></div>';

                $row[] = '<div class="text-left"><b>Sale: </b>' . ORDER_STATUS_LABEL[$value->delivery_status] . '<br><br><b>Payment: </b>' . PAYMENT_STATUS_LABEL[$value->payment_status] . '</div>';

                $row[] = action_button($action); //custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit($id)
    {
        if (permission('sale-edit')) {
            $this->setPageData('Edit Sale', 'Edit Sale', 'fas fa-edit', [['name' => 'Sale', 'link' => route('purchase')], ['name' => 'Edit Sale']]);
            $data = [
                'brands' => Brand::allBrands(),
                'categories' => Category::allCategories(),
                'products' => Product::where('status', 1)->paginate(12),
                'sale' => $this->model->with('saleProductList', 'pos_payments')->find($id),
                'customers' => Customer::all(),
                'taxes' => Tax::activeTaxes(),
            ];

            return view('sale::edit', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function update(SaleFormRequest $request)
    {
        if ($request->ajax() and permission('sale-edit')) {
            DB::beginTransaction();
            try {
                $customer = Customer::find($request->customer_id);
                $saleData = $this->model->with('sale_products')->find($request->sale_id);

                $sale_data = (new PosService())->pos_entry_data_maker($request, $customer, $request->item, $request->total_qty);

                $old_document = $saleData ? $saleData->document : '';

                $sale = $saleData->update($sale_data);
                if (!$sale) {
                    throw new Exception('Sale update failed.');
                }

                // Sale User Activity
                $saleData->user_activity()->create([
                    'activity_type' => 'sale_update',
                    'status_name' => 'Updated',
                    'user_id' => auth()->id(),
                ]);

                $products = [];
                $direct_cost = [];
                if ($request->has('products')) {
                    foreach ($request->products as $value) {
                        $p_id = $saleData->id;
                        $unit = Unit::where('unit_name', $value['unit'])->first();

                        $products[] = [
                            'sale_id' => $p_id,
                            'product_id' => $value['id'],
                            'product_variant_id' => $value['sale_product_id'],
                            'serial_no' => $value['serial_no'],
                            'qty' => $value['qty'],
                            'sale_unit_id' => $unit ? $unit->id : null,
                            'net_unit_price' => $value['net_unit_price'],
                            'discount' => $value['discount'],
                            'discount_rate' => $value['discount_rate'],
                            'total' => $value['subtotal'],
                            'order_type' => 2,
                            'created_at' => date('Y-m-d g:i:s')
                        ];

                        $p_id = $value['variant_id'];
                        $p_qty = $value['qty'];
                        $old_data = SaleProduct::where('product_variant_id', $value['variant_id'])->where('sale_id', $request->sale_id)->first();
                        $product = ProductUnit::where('id', $value['variant_id'])->first();
                        if (!empty($old_data)) {
                            if ($p_qty > $old_data->qty) {
                                $c_data = $p_qty - $old_data->qty;
                                $data = [
                                    'qty' => $product->qty - $c_data,
                                ];
                            } elseif ($p_qty < $old_data->qty) {
                                $c_data = $old_data->qty - $p_qty;
                                $data = [
                                    'qty' => $product->qty + $c_data,
                                ];
                            } elseif ($p_qty == $old_data->qty) {
                                $data = [
                                    'qty' => $product->qty,
                                ];
                            } else {
                                return 'problem';
                            }
                        } else {
                            $data = [
                                'qty' => $product->qty - $p_qty,
                            ];
                        }

                        $result = ProductUnit::where('id', $p_id)->update($data);

                    }
                    if (count($products) > 0) {
                        SaleProduct::where('sale_id', $saleData->id)->delete();
                        SaleProduct::insert($products);
                    }
                }

                $payments = (new PosService())->payment_maker($request);

                if (count($payments) > 0) {
                    $saleData->payments()->sync($payments);
                }


                $sum_direct_cost = array_sum($direct_cost);
                $total_tax = ($request->total_tax ? $request->total_tax : 0) + ($request->order_tax ? $request->order_tax : 0);

                if ($sale && $old_document != '') {
                    $this->delete_file($old_document, SALE_DOCUMENT_PATH);
                }

//                    Transaction::where(['voucher_no'=>$request->invoice_no,'voucher_type'=>'INVOICE'])->delete();
//                    $customer = Customer::with('coa')->find($request->customer_id);
//                    $this->sale_balance_add($request->sale_id,$request->invoice_no,$request->grand_total,$total_tax,$sum_direct_cost,$customer->coa->id,$customer->name,$request->sale_date,$payment_data);
                $output = $this->store_message($sale, $request->sale_id);
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() and permission('sale-delete')) {
            DB::beginTransaction();
            try {
                $saleData = $this->model->find($request->id);
                $saleUnitData = SaleProduct::where('sale_id', $request->id);
                $results = $saleUnitData->delete();
                // Sale User Activity
                $saleData->user_activity()->create([
                    'activity_type' => 'sale_delete',
                    'status_name' => 'Deleted',
                    'user_id' => auth()->id(),
                ]);
                $result = $saleData->delete();

                if ($result) {
                    $output = ['status' => 'success', 'message' => 'Data has been deleted successfully'];
                } else {
                    $output = ['status' => 'error', 'message' => 'failed to delete data'];
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function show_invoice(Request $request)
    {
        if ($request->ajax() and permission('sale-view')) {
            $sale = $this->model->with('products', 'customer', 'coupon', 'warehouse')->find($request->id);
            return view('sale::invoice-data', compact('sale'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete_sale(Request $request)
    {
        if ($request->ajax() and permission('sale-delete')) {
            $id = $request->id;
            $sale_p = SaleProduct::where('id', $id)->first();
            $p = Sale::where('id', $sale_p->sale_id)->first();
            $product_unit = ProductUnit::where('id', $sale_p->product_variant_id)->first();
            DB::beginTransaction();
            try {
                $data = [
                    'item' => $p->item - 1,
                    'total_qty' => $p->total_qty - $sale_p->qty,
                    'total_price' => $p->total_price - $sale_p->total,
                    'total_discount' => $p->total_discount - $sale_p->discount,
                    'total_tax' => $p->total_tax - $sale_p->tax,
                    'grand_total' => $p->grand_total - $sale_p->total,
                    'net_total' => $p->net_total - $sale_p->total,
                ];
                Sale::where('id', $sale_p->sale_id)->update($data);
                $data1 = [
                    'qty' => $sale_p->qty + $product_unit->qty,
                ];
                ProductUnit::where('id', $sale_p->product_variant_id)->update($data1);

                $saleUnitData = SaleProduct::where('id', $request->id);
                $results = $saleUnitData->delete();

                if ($results) {
                    $output = ['status' => 'success', 'message' => 'Data has been deleted successfully'];
                } else {
                    $output = ['status' => 'error', 'message' => 'failed to delete data'];
                }
                DB::commit();
            } catch (Exception $e) {
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
        if ($request->ajax() and permission('sale-bulk-delete')) {
            DB::beginTransaction();
            try {
                foreach ($request->ids as $id) {
                    $saleData = $this->model->with('products')->find($id);
                    if (!$saleData->products->isEmpty()) {

                        foreach ($saleData->products as $sale_product) {
                            $old_sold_qty = $sale_product->pivot->qty;
                            $sale_unit = Unit::find($sale_product->pivot->sale_unit_id);
                            if ($sale_unit->operator == '*') {
                                $old_sold_qty = $old_sold_qty * $sale_unit->operation_value;
                            } else {
                                $old_sold_qty = $old_sold_qty / $sale_unit->operation_value;
                            }
                            $product_data = Product::find($sale_product->id);
                            if ($product_data) {
                                $product_data->qty += $old_sold_qty;
                                $product_data->update();
                            }

                            $warehouse_product = WarehouseProduct::where([
                                'warehouse_id' => $saleData->warehouse_id,
                                'product_id' => $sale_product->id,
                            ])->first();

                            if ($warehouse_product) {
                                $warehouse_product->qty += $old_sold_qty;
                                $warehouse_product->update();
                            }
                        }
                        $saleData->products()->detach();
                    }

                    Transaction::where(['voucher_no' => $saleData->invoice_no, 'voucher_type' => 'INVOICE'])->delete();
                }
                $result = $this->model->destroy($request->ids);
                if ($result) {
                    $output = ['status' => 'success', 'message' => 'Data has been deleted successfully'];
                } else {
                    $output = ['status' => 'error', 'message' => 'failed to delete data'];
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->access_blocked());
        }
    }

    public function returnStore(Request $request)
    {
        if ($request->ajax() && permission('sale-return')) {
            DB::beginTransaction();
            try {
                $saleProductReturn = [];
                $sale = $this->model->find($request->sale_id);
                $customer = Customer::with('coa')->find($sale->customer_id);

                if ($request->has('sale')) {
                    foreach ($request->sale as $value) {
                        if (!empty($value['warehouse_id']) && !empty($value['product_id']) && !empty($value['price']) && !empty($value['return_qty'] && !empty($value['sub_total']))) {
                            $saleProductReturn[] = [
                                'sale_id' => $request->sale_id,
                                'invoice_no' => $request->invoice_no,
                                'return_date' => date("Y-m-d"),
                                'warehouse_id' => $value['warehouse_id'],
                                'product_id' => $value['product_id'],
                                'price' => $value['price'],
                                'return_qty' => $value['return_qty'],
                                'sub_total' => $value['sub_total'],
                            ];
                            // Update sale product return qty
                            SaleProduct::where(['sale_id' => $request->sale_id, 'product_id' => $value['product_id']])
                                ->increment('return_qty', $value['return_qty']);

                            // Update warehouse product qty
                            WarehouseProduct::where(['warehouse_id' => $value['warehouse_id'], 'product_id' => $value['product_id']])
                                ->increment('qty', $value['return_qty']);
                        }
                    }
                }
                // Update total return qty for the sale
                $sale->update([
                    'total_return_qty' => $sale->total_return_qty + $request->total_return_qty,
                ]);

                // Log sale user activity
                $sale->user_activity()->create([
                    'activity_type' => 'sale_return',
                    'status_name' => 'Return',
                    'user_id' => auth()->id(),
                ]);
                // Create customer credit transaction
                $customerCredit = [
                    'chart_of_account_id' => $customer->coa->id,
                    'voucher_no' => $sale->invoice_no,
                    'voucher_type' => 'Return',
                    'voucher_date' => $request->return_date,
                    'description' => 'Customer ' . $customer->name . ' credit for Product Return Invoice NO- ' . $request->invoice_no,
                    'debit' => 0,
                    'credit' => $request->total_return_sub_total,
                    'posted' => 1,
                    'approve' => 1,
                    'created_by' => optional(Auth::user())->name ?? '',
                    'created_at' => now(),
                ];

                Transaction::create($customerCredit);

                // Insert sale product returns
                SaleProductReturn::insert($saleProductReturn);

                DB::commit();
                $output = ['status' => 'success', 'message' => 'Return Store Successfully'];
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function Saleassign(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function saleOrderStatus(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request.']);
        }

        $statusId = $request->status_id;
        $deliveryStatus = $request->delivery_status;

        $saleOrder = $this->model->find($statusId);
        $data = $saleOrder;
        if (!$saleOrder) {
            return response()->json(['error' => 'Sale order not found.']);
        }

        DB::beginTransaction();
        try {
            if ($deliveryStatus == 6) {
                $saleProducts = SaleProduct::where('sale_id', $statusId)->get();
                foreach ($saleProducts as $saleProduct) {
                    $productUnit = ProductUnit::where('product_unit_id', $saleProduct->sale_unit_id)
                        ->where('product_id', $saleProduct->product_id)
                        ->first();
                    if ($productUnit) {
                        $productUnit->update(['qty' => $productUnit->qty + $saleProduct->qty]);
                    }
                }
                // Update delivery status
                $saleOrder->update(['delivery_status' => $deliveryStatus]);
                $activityType = 'sale_status_change';
                $statusName = 'Cancel';
            } else {
                // Update delivery status
                $saleOrder->update(['delivery_status' => $deliveryStatus]);
                $activityType = 'sale_status_change';
                $statusName = ORDER_STATUS_VALUE[$deliveryStatus];
            }

            if ($deliveryStatus == 5) {
                $payment_data = [];
                if (isset($data->pos_payments)) {
                    foreach ($data->pos_payments as $value) {
                        $payment_data[] = [
                            'payment_method' => $value->payment_method,
                            'account_id' => $value->account_id,
                            'paid_amount' => $value->paid_amount
                        ];
                    }
                }
                $customerAccountId = ChartOfAccount::where(['customer_id' => $data->customer_id])->first();
                $this->sale_balance_add($data->invoice_no, $data->grand_total, 0, $data->sale_date, $payment_data, $data->warehouse_id, $customerAccountId);
            }

            // Log sale user activity
            $saleOrder->user_activity()->create([
                'activity_type' => $activityType,
                'status_name' => $statusName,
                'user_id' => auth()->id(),
            ]);

            DB::commit();
            $output = $this->store_message(true, $statusId);
            return response()->json($output);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update sale order status.']);
        }
    }

    private function sale_balance_add($invoice_no, $grand_total, $total_tax, $sale_date, array $payment_data, $warehouse_id, $customerAccountId)
    {
        $saleChartOfAccountTransaction = array(
            'chart_of_account_id' => $customerAccountId->id,
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $sale_date,
            'description' => 'Customer debit For Invoice No -  ' . $invoice_no ,
            'debit' => $grand_total,
            'credit' => 0,
            'posted' => 1,
            'approve' => 1,
            'created_by' => optional(Auth::user())->name ?? '',
            'created_at' => date('Y-m-d H:i:s')
        );
        Transaction::create($saleChartOfAccountTransaction);

        $product_sale_income = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('product_sale'))->value('id'),
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $sale_date,
            'description' => 'Sale Income ' . $grand_total . 'TK from Invoice No. - ' . $invoice_no,
            'debit' => 0,
            'credit' => $grand_total,
            'posted' => 1,
            'approve' => 1,
            'created_by' => optional(Auth::user())->name ?? '',
            'created_at' => date('Y-m-d H:i:s')
        );

        Transaction::create($product_sale_income);

        if ($total_tax) {
            $tax_info = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('tax'))->value('id'),
                'warehouse_id' => $warehouse_id,
                'voucher_no' => $invoice_no,
                'voucher_type' => 'INVOICE',
                'voucher_date' => $sale_date,
                'description' => 'Sale Total Tax For Invoice No. - ' . $invoice_no,
                'debit' => 0,
                'credit' => $total_tax,
                'posted' => 1,
                'approve' => 1,
                'created_by' => optional(Auth::user())->name ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );
            Transaction::create($tax_info);
        }

        $customerPaidAmount = 0;
        if (!empty($payment_data[0]['paid_amount'])) {
            foreach ($payment_data as $value) {
                if ($value['payment_method'] == 1) {
                    //Cah In Hand debit
                    $payment = array(
                        'chart_of_account_id' => $value['account_id'],
                        'warehouse_id' => $warehouse_id,
                        'voucher_no' => $invoice_no,
                        'voucher_type' => 'INVOICE',
                        'voucher_date' => $sale_date,
                        'description' => 'Cash Paid amount ' . $value['paid_amount'] . 'Tk for Invoice No. - ' . $invoice_no,
                        'debit' => $value['paid_amount'],
                        'credit' => 0,
                        'posted' => 1,
                        'approve' => 1,
                        'created_by' => optional(Auth::user())->name ?? '',
                        'created_at' => date('Y-m-d H:i:s')

                    );
                } else {
                    // Bank Ledger
                    $payment = array(
                        'chart_of_account_id' => $value['account_id'],
                        'warehouse_id' => $warehouse_id,
                        'voucher_no' => $invoice_no,
                        'voucher_type' => 'INVOICE',
                        'voucher_date' => $sale_date,
                        'description' => 'Paid amount ' . $value['paid_amount'] . 'Tk for Invoice No. - ' . $invoice_no,
                        'debit' => $value['paid_amount'],
                        'credit' => 0,
                        'posted' => 1,
                        'approve' => 1,
                        'created_by' => optional(Auth::user())->name ?? '',
                        'created_at' => date('Y-m-d H:i:s')
                    );
                }
                Transaction::create($payment);
                $customerPaidAmount += $value['paid_amount'];
            }

            $customerCredit = array(
                'chart_of_account_id' => $customerAccountId->id,
                'warehouse_id' => $warehouse_id,
                'voucher_no' => $invoice_no,
                'voucher_type' => 'INVOICE',
                'voucher_date' => $sale_date,
                'description' => 'Customer credit for Paid Amount For Customer Invoice NO- ' . $invoice_no ,
                'debit' => 0,
                'credit' => $customerPaidAmount,
                'posted' => 1,
                'approve' => 1,
                'Created_by' => optional(Auth::user())->name ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );
            Transaction::create($customerCredit);
        }
    }

    public function saleLog(Request $request)
    {
        if ($request->ajax() && permission('sale-view')) {
            $sale = $this->model->with('user_activity')->find($request->id);
            return view('sale::log-data', compact('sale'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
