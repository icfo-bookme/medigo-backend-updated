<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\UserActivity;
use Exception;
use App\Models\Tax;
use App\Models\Unit;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Illuminate\Support\Facades\Session;
use Modules\Product\Entities\ProductUnit;
use Modules\Purchase\Entities\ProductExpiryDate;
use Modules\Purchase\Entities\Purchase;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Product\Entities\ProductVariant;
use Modules\Purchase\Entities\PurchasePayment;
use Modules\Purchase\Entities\PurchaseProduct;
use Modules\Purchase\Http\Requests\PurchaseFormRequest;
use Intervention\Image\Facades\Image;

class PurchaseController extends BaseController
{

    use UploadAble;

    private const INVOICE_NO = 1001;

    public function __construct(Purchase $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('purchase-access')) {
            $this->setPageData('Purchase Manage', 'Purchase Manage', 'fas fa-shopping-cart', [['name' => 'Purchase Manage']]);
            $suppliers = Supplier::where('status', 1)->get(['id','name','company_name']);
            return view('purchase::index', compact('suppliers'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('purchase-access')) {
                $fields = [
                    'invoice_no' => 'setInvoiceNo',
                    'start_date' => 'setFromDate',
                    'end_date' => 'setToDate',
                    'supplier_id' => 'setSupplierID',
                    'purchase_status' => 'setPurchaseStatus',
                    'payment_status' => 'setPaymentStatus',
                    'sort_table' => 'setTableOrder',
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
                    if (permission('purchase-edit')) {
                        $action .= ' <a class="dropdown-item" href="' . route("purchase.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    }
                    if (permission('purchase-view')) {
                        $action .= ' <a class="dropdown-item" href="' . route("purchase.view", $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                        $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';
                    }
                    if (permission('purchase-change-status') && $value->purchase_status != 1 && $value->purchase_status != 2) {
                        $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-name="' . $value->memo_no . '" data-status="' . $value->purchase_status . '"><i class="fas fa-check-circle text-success mr-2"></i> Change Status</a>';
                    }

                    if (permission('purchase-delete') && $value->purchase_status != 1) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->invoice_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }

                    $row = [];
                    if (permission('purchase-bulk-delete')) {
                        $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox
                    }
                    $row[] = $no;
                    $row[] = '<a class="text-info cursor-pointer view_invoice" data-id="' . $value->id . '">' . $value->invoice_no . '</a>';
                    $row[] = $value->supplier->company_name . ' ( ' . $value->supplier->name . ')';
                    $row[] = $value->item;
                    $row[] = number_format($value->grand_total, 2);
                    $row[] = number_format($value->paid_amount, 2);
                    $row[] = number_format(($value->due_amount), 2);
                    $row[] = date(config('settings.date_format'), strtotime($value->purchase_date));
                    $row[] = PURCHASE_STATUS_LABEL[$value->purchase_status];
                    $row[] = PAYMENT_STATUS_LABEL[$value->payment_status];
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function create()
    {
        if (permission('purchase-add')) {
            $this->setPageData('Add Purchase', 'Add Purchase', 'fas fa-shopping-cart', [['name' => 'Add Purchase']]);
            $purchase = $this->model->select('invoice_no')->orderBy('invoice_no', 'desc')->first();
            $data = [
                'suppliers' => Supplier::where('status', 1)->get(),
                'taxes' => Tax::activeTaxes(),
                'purchase_data' => Session::get('purchase_data'),
                'invoice_no' => 'PINV-' . ($purchase ? explode('PINV-', $purchase->invoice_no)[1] + 1 : self::INVOICE_NO)
            ];
            return view('purchase::create', $data);
        } else {
            return $this->access_blocked();
        }

    }

    public function store(PurchaseFormRequest $request)
    {
        if ($request->ajax() && permission('purchase-add')) {
            DB::beginTransaction();
            try {
                $purchase_data = [
                    'invoice_no' => $request->invoice_no,
                    'supplier_id' => $request->supplier_id,
                    'item' => $request->item,
                    'total_qty' => $request->total_qty + $request->total_free_qty,
                    'total_discount' => $request->total_discount,
                    'total_tax' => $request->total_tax,
                    'total_cost' => $request->total_cost,
                    'order_tax_rate' => $request->order_tax_rate,
                    'order_tax' => $request->order_tax,
                    'order_discount' => $request->order_discount ? $request->order_discount : null,
                    'shipping_cost' => $request->shipping_cost ? $request->shipping_cost : null,
                    'grand_total' => $request->grand_total,
                    'paid_amount' => $request->paid_amount ? $request->paid_amount : 0,
                    'due_amount' => $request->grand_total - ($request->paid_amount ? $request->paid_amount : 0),
                    'purchase_status' => 3,
                    'payment_status' => 3,
                    'note' => $request->note,
                    'purchase_date' => $request->purchase_date,
                    'created_by' => auth()->user()->name
                ];

                $payment_data = [
                    'payment_method' => $request->payment_method,
                    'account_id' => $request->account_id,
                    'paid_amount' => $request->paid_amount ? $request->paid_amount : 0,
                    'reference_no' => $request->reference_no ? $request->reference_no : '',
                ];

                if ($request->hasFile('document')) {
                    $purchase_data['document'] = $this->uploadCompressImage($request->file('document'), PURCHASE_DOCUMENT_PATH);
                }

                $purchase = $this->model->create($purchase_data);

                //purchase products
                $products = [];
                if ($request->has('products')) {
                    foreach ($request->products as $value) {
                        $unit = Unit::where('unit_name', $value['unit'])->first();
                        if ($unit->operator == '*') {
                            $qty = ($value['received'] ? $value['received'] : 0) * $unit->operation_value;
                        } else {
                            $qty = ($value['received'] ? $value['received'] : 0) / $unit->operation_value;
                        }

                        $variant_id = null;

                        $product = ProductUnit::where('item_code', $value['code'])->first();
                        if ($purchase->purchase_status == 1) {
                            ($value == '+') ? $product->qty += $value['qty'] : $product->qty += $value['qty'];
                        }
                        $product->save();

                        $products[] = [
                            'purchase_id' => $purchase->id,
                            'product_id' => $value['id'],
                            'product_variant_id' => $value['variant_id'],
                            'serial_no' => $value['serial_no'],
                            'qty' => $value['qty'],
                            'free_qty' => $value['free_qty'],
                            'received' => $value['received'],
                            'purchase_unit_id' => $unit ? $unit->id : null,
                            'net_unit_cost' => $value['net_unit_cost'],
                            'discount' => $value['discount'],
                            'tax_rate' => $value['tax_rate'],
                            'tax' => $value['tax'],
                            'total' => $value['subtotal'],
                            'expiry_date' => $value['expiry_date'],
                            'created_at' => date('Y-m-d g:i:s')
                        ];
                    }
                    if (count($products) > 0) {
                        PurchaseProduct::insert($products);
                    }
                }

                if (empty($purchase)) {
                    if ($request->hasFile('document')) {
                        $this->delete_file($purchase_data['document'], PURCHASE_DOCUMENT_PATH);
                    }
                }

                // Purchase User Activity
                $purchase->user_activity()->create([
                    'activity_type' => 'purchase_create',
                    'status_name' => 'Created',
                    'user_id' => auth()->id(),
                ]);

                $supplier = Supplier::with('coa')->find($request->supplier_id);
                if ($request->purchase_status == 1) {
                    $this->purchase_balance_add($purchase->invoice_no, $purchase->id, $request->grand_total, $supplier->coa->id, $supplier->name, $request->purchase_date, $payment_data);
                }

                $output = $this->store_message($purchase, $request->update_id);
                if ($purchase) {
                    Session::forget('purchase_data');
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

    public function changeStatus(Request $request)
    {
        if (permission('purchase-change-status')) {

            DB::beginTransaction();
            try {
                $purchase = $this->model->with('purchase_products', 'user_activity')->find($request->id);
                $supplier = Supplier::with('coa')->find($purchase->supplier_id);
                if ($request->status == 1) {
                    foreach ($purchase->purchase_products as $value) {
                        $product = ProductUnit::where('id', $value['product_id'])->first();
                        if (!empty($product)) {
                            // Adding qty and free_qty to the product quantity
                            $product->qty += $value['qty'] + $value['free_qty'];
                            $product->save();
                        } else {
                            return response()->json(['status' => 'error', 'message' => 'Product not found']);
                        }
                    }
                    $this->purchase_balance_add($purchase->invoice_no, $purchase->id, $purchase->grand_total, $supplier->coa->id, $supplier->name, $purchase->purchase_date);
                }
                $purchase->update(['purchase_status' => $request->status]);
                $purchase->user_activity()->create([
                    'activity_type' => 'purchase_status_change',
                    'status_name' => PURCHASE_STATUS[$request->status],
                    'user_id' => auth()->id(),
                ]);
                $output = ['status' => 'success', 'message' => 'Status Change Successfully'];
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    private function purchase_balance_add(string $purchase_no, string $purchase_id, $balance, int $supplier_coa_id, string $supplier_name, $purchase_date)
    {
        if (!empty($purchase_no) && !empty($purchase_id) && !empty($balance) && !empty($supplier_coa_id) && !empty($supplier_name) && !empty($purchase_date)) {
            // supplier Credit
            $purchase_coa_transaction = array(
                'chart_of_account_id' => $supplier_coa_id,
                'voucher_no' => $purchase_no,
                'voucher_type' => 'Purchase',
                'voucher_date' => $purchase_date,
                'description' => 'Supplier ' . $supplier_name,
                'debit' => 0,
                'credit' => $balance,
                'posted' => 1,
                'approve' => 1,
                'created_by' => auth()->user()->name,
                'created_at' => date('Y-m-d H:i:s')
            );

            //Inventory Debit
            $cosde = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
                'voucher_no' => $purchase_no,
                'voucher_type' => 'Purchase',
                'voucher_date' => $purchase_date,
                'description' => 'Inventory Debit For Supplier ' . $supplier_name,
                'debit' => $balance,
                'credit' => 0,
                'posted' => 1,
                'approve' => 1,
                'created_by' => auth()->user()->name,
                'created_at' => date('Y-m-d H:i:s')
            );

            // Expense for company
            $expense = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('material_purchase'))->value('id'),
                'voucher_no' => $purchase_no,
                'voucher_type' => 'Purchase',
                'voucher_date' => $purchase_date,
                'description' => 'Company Credit For Supplier ' . $supplier_name,
                'debit' => $balance,
                'credit' => 0,
                'posted' => 1,
                'approve' => 1,
                'created_by' => auth()->user()->name,
                'created_at' => date('Y-m-d H:i:s')
            );

            Transaction::insert([
                $purchase_coa_transaction, $cosde, $expense
            ]);

            //insaf er moton hoy nai

        }
    }

    public function show(int $id)
    {
        if (permission('purchase-view')) {
            $this->setPageData('Purchase Details', 'Purchase Details', 'fas fa-file', [['name' => 'Purchase', 'link' => route('purchase')], ['name' => 'Purchase Details']]);
            $purchase = $this->model->with('purchase_products')->find($id);
            return view('purchase::details', compact('purchase'));
        } else {
            return $this->access_blocked();
        }
    }

    public function show_invoice(Request $request)
    {
        if ($request->ajax()) {
            $purchase = $this->model->with('purchase_products')->find($request->id);
            return view('purchase::invoice-data', compact('purchase'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(int $id)
    {
        if (permission('purchase-edit')) {
            $this->setPageData('Edit Purchase', 'Edit Purchase', 'fas fa-edit', [['name' => 'Purchase', 'link' => route('purchase')], ['name' => 'Edit Purchase']]);
            $data = [
                'purchase' => $this->model->with('purchase_products')->find($id),
                'suppliers' => Supplier::where('status', 1)->get(),
                'taxes' => Tax::activeTaxes(),
            ];
            return view('purchase::edit', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function update(PurchaseFormRequest $request)
    {
        if ($request->ajax() && permission('purchase-edit')) {
            DB::beginTransaction();
            try {
                $purchaseData = $this->model->with('purchase_products')->find($request->purchase_id);
                $purchase_data = [
                    'supplier_id' => $request->supplier_id,
                    'warehouse_id' => $request->warehouse_id,
                    'item' => $request->item,
                    'total_qty' => $request->total_qty + $request->total_free_qty,
                    'total_discount' => $request->total_discount,
                    'total_tax' => $request->total_tax,
                    'total_labor_cost' => $request->total_labor_cost ? $request->total_labor_cost : null,
                    'total_cost' => $request->total_cost,
                    'order_tax_rate' => $request->order_tax_rate,
                    'order_tax' => $request->order_tax,
                    'order_discount' => $request->order_discount ? $request->order_discount : null,
                    'shipping_cost' => $request->shipping_cost ? $request->shipping_cost : null,
                    'grand_total' => $request->grand_total,
                    'due_amount' => ($request->grand_total - $purchaseData->paid_amount),
                    'purchase_status' => 3,
                    'payment_status' => 3,
                    'note' => $request->note,
                    'purchase_date' => $request->purchase_date,
                    'created_by' => auth()->user()->name
                ];

                if ($request->hasFile('document')) {
                    $purchase_data['document'] = $this->upload_file($request->file('document'), PURCHASE_DOCUMENT_PATH);
                }
                $old_document = $purchaseData ? $purchaseData->document : '';

                $products = [];
                if ($request->has('products')) {
                    foreach ($request->products as $key => $value) {

                        $unit = Unit::where('unit_name', $value['unit'])->first();
                        if ($unit->operator == '*') {
                            $qty = ($value['received'] ? $value['received'] : 0) * $unit->operation_value;
                        } else {
                            $qty = ($value['received'] ? $value['received'] : 0) / $unit->operation_value;
                        }

                        $products[] = [
                            'purchase_id' => $purchaseData->id,
                            'product_id' => $value['id'],
                            'product_variant_id' => $value['variant_id'],
                            'serial_no' => $value['serial_no'],
                            'qty' => $value['qty'],
                            'free_qty' => $value['free_qty'],
                            'received' => $value['received'],
                            'purchase_unit_id' => $unit ? $unit->id : null,
                            'net_unit_cost' => $value['net_unit_cost'],
                            'discount' => $value['discount'],
                            'tax_rate' => $value['tax_rate'],
                            'tax' => $value['tax'],
                            'total' => $value['subtotal'],
                            'expiry_date' => $value['expiry_date'],
                            'updated_at' => now()
                        ];
                    }
                    if (count($products) > 0) {
                        PurchaseProduct::where('purchase_id', $purchaseData->id)->delete();
                        PurchaseProduct::insert($products);
                    }
                }

                $purchase = $purchaseData->update($purchase_data);
                // Purchase User Activity
                $purchaseData->user_activity()->create([
                    'activity_type' => 'purchase_update',
                    'status_name' => 'Updated',
                    'user_id' => auth()->id(),
                ]);

                if (empty($purchase)) {
                    if ($request->hasFile('document')) {
                        $this->delete_file($purchase_data['document'], PURCHASE_DOCUMENT_PATH);
                        if ($purchase && $old_document != '') {
                            $this->delete_file($old_document, PURCHASE_DOCUMENT_PATH);
                        }
                    }
                }
                $output = $this->store_message($purchase, $request->purchase_id);
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

    private function purchase_balance_update(string $purchase_no, string $purchase_id, $balance, int $supplier_coa_id, string $supplier_name, $purchase_date, $old_supplier_coa_id)
    {
        if (!empty($purchase_id) && !empty($balance) && !empty($supplier_coa_id) && !empty($supplier_name) && !empty($purchase_date) && !empty($old_supplier_coa_id)) {

            if ($supplier_coa_id != $old_supplier_coa_id) {
                PurchasePayment::where('purchase_id', $purchase_id)->delete();
                $remove_purchase_transaction = Transaction::where('voucher_no', (string)$purchase_no)->where('voucher_type', (string)"Purchase")->delete();
                if ($remove_purchase_transaction) {
                    $this->purchase_balance_add($purchase_no, $purchase_id, $balance, $supplier_coa_id, $supplier_name, $purchase_date, $payment_data = []);
                }
            } else {
                $purchase_coa_transaction = Transaction::where(['chart_of_account_id' => $supplier_coa_id, 'voucher_no' => $purchase_id, 'voucher_type' => 'Purchase'])->first();
                if ($purchase_coa_transaction) {
                    $purchase_coa_transaction->update([
                        'voucher_date' => $purchase_date,
                        'credit' => $balance,
                        'modified_by' => auth()->user()->name,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

                $purchase_coscr = Transaction::where([
                    'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
                    'voucher_no' => $purchase_id, 'voucher_type' => 'Purchase'])->first();
                if ($purchase_coscr) {
                    $purchase_coscr->update([
                        'voucher_date' => $purchase_date,
                        'debit' => $balance,
                        'modified_by' => auth()->user()->name,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                $company_expense = Transaction::where([
                    'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('product_purchase'))->value('id'),
                    'voucher_no' => $purchase_id, 'voucher_type' => 'Purchase'])->first();
                if ($company_expense) {
                    $company_expense->update([
                        'voucher_date' => $purchase_date,
                        'debit' => $balance,
                        'modified_by' => auth()->user()->name,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }

            }

        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('purchase-delete')) {
            DB::beginTransaction();
            try {
                $purchaseData = $this->model->with('purchase_products')->find($request->id);
                $invoice_no = $purchaseData->invoice_no;
                if (!$purchaseData->purchase_products->isEmpty()) {
                    foreach ($purchaseData->purchase_products as $purchase_product) {
                        $old_received_qty = $purchase_product->received ? $purchase_product->received : 0;
                        $purchase_unit = Unit::find($purchase_product->purchase_unit_id);
                        if ($purchase_unit->operator == '*') {
                            $old_received_qty = $old_received_qty * $purchase_unit->operation_value;
                        } else {
                            $old_received_qty = $old_received_qty / $purchase_unit->operation_value;
                        }

                        if (!empty($purchase_product->product_variant_id)) {
                            $product_variant = ProductUnit::find($purchase_product->product_unit_id);
                            if ($product_variant) {
                                $product_variant->qty -= $old_received_qty;
                                $product_variant->update();
                            }
                        }
                    }
                    $purchaseData->purchase_products()->delete();
                }

                // Purchase User Activity
                $purchaseData->user_activity()->create([
                    'activity_type' => 'purchase_deleted',
                    'status_name' => 'Deleted',
                    'user_id' => auth()->id(),
                ]);
                $purchase = $purchaseData->delete();
                $output = $purchase ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
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

    public function bulk_delete(Request $request)
    {
        if ($request->ajax() && permission('purchase-bulk-delete')) {
            foreach ($request->ids as $id) {
                DB::beginTransaction();
                try {
                    $purchaseData = Purchase::with('purchase_products', 'labor_bill_rates')->find($id);
                    $invoice_no = $purchaseData->invoice_no;
                    if (!$purchaseData->purchase_products->isEmpty()) {
                        foreach ($purchaseData->purchase_products as $purchase_product) {

                            $old_received_qty = $purchase_product->received ? $purchase_product->received : 0;
                            $purchase_unit = Unit::find($purchase_product->purchase_unit_id);
                            if ($purchase_unit->operator == '*') {
                                $old_received_qty = $old_received_qty * $purchase_unit->operation_value;
                            } else {
                                $old_received_qty = $old_received_qty / $purchase_unit->operation_value;
                            }
                            $product_data = Product::find($purchase_product->product_id);
                            if ($product_data) {
                                $product_data->qty -= $old_received_qty;
                                $product_data->update();
                            }

                            if (!empty($purchase_product->product_variant_id)) {
                                $product_variant = ProductVariant::find($purchase_product->product_variant_id);
                                if ($product_variant) {
                                    $product_variant->item_qty -= $old_received_qty;
                                    $product_variant->update();
                                }
                            }
                        }

                        $purchaseData->purchase_products()->delete();
                    }
                    if (!$purchaseData->labor_bill_rates->isEmpty()) {
                        $purchaseData->labor_bill_rates()->detach();
                    }
                    PurchasePayment::where('purchase_id', $id)->delete();
                    Transaction::where('voucher_no', (string)$invoice_no)->where('voucher_type', (string)"Purchase")->delete();
                    $purchase = $purchaseData->delete();
                    $output = $purchase ? ['status' => 'success', 'message' => 'Data has been deleted successfully'] : ['status' => 'error', 'message' => 'failed to delete data'];
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
                return response()->json($output);
            }
        } else {
            return response()->json($this->access_blocked());
        }
    }

    //Purchase Form Product Auto Complete Search Data
    public function autocomplete_search_product(Request $request)
    {
        if (!empty($request->search)) {
            $output = array();
            $search_text = $request->search;
            $temp_array = [];

            $products = ProductUnit::join('products as p', 'product_units.product_id', '=', 'p.id')
                ->leftjoin('brands as b', 'p.brand_id', '=', 'b.id')
                ->leftjoin('units as unt', 'product_units.product_unit_id', '=', 'unt.id')
                ->selectRaw('product_units.*,p.name,p.brand_id,b.name as brand_name,unt.id,unt.unit_name')
                ->where(function ($q) use ($search_text) {
                    $q->where('product_units.item_code', 'like', '%' . $search_text . '%')
                        ->orWhere('p.name', 'like', '%' . $search_text . '%');
                })->get();


            if (!$products->isEmpty()) {
                foreach ($products as $value) {
                    $latest_cost = PurchaseProduct::where('product_id', $value->product_id)
                        ->orderBy('created_at', 'desc')
                        ->value('net_unit_cost');
                    $price = $latest_cost ? $latest_cost : $value->price;
                    $discounted_price = $price * (1 - $value->discount / 100);

                    $temp_array['code'] = $value->item_code;
                    $temp_array['value'] = $value->item_code . ' - ' . $value->name . ' (' . $value->unit_name . ')' . ' - ' . $price . ($value->brand_id ? ' - [' . $value->brand_name . ']' : '');
                    $temp_array['label'] = $value->item_code . ' - ' . $value->name . ' (' . $value->unit_name . ')' . ' - price(' . ($discounted_price) . 'Tk)' . ($value->brand_id ? ' - [' . $value->brand_name . ']' : '');
                    $output[] = $temp_array;
                }
            }

            if (empty($output) && count($output) == 0) {
                $output['value'] = '';
                $output['label'] = 'No Record Found';
            }

            return $output;
        }
    }

    //Purchase Form On Select Product Fetch All Data of It
    public function search_product(Request $request)
    {
        $product_data = Product::with('brand')->where('code', $request['data'])->first();

        if (!$product_data) {
            $product_data = Product::join('product_units', 'products.id', '=', 'product_units.product_id')
                ->leftjoin('brands as b', 'products.brand_id', '=', 'b.id')
                ->select('products.*', 'product_units.id as variant_id',
                    'product_units.item_code', 'product_units.price as item_price', 'product_units.qty as item_qty', 'product_units.id as p_u_id', 'b.name as brand_name', 'product_units.product_unit_id', 'product_units.discount')
                ->where('product_units.item_code', $request['data'])
                ->first();
        }

        if ($product_data) {
            $latest_cost = PurchaseProduct::where('product_id', $product_data->id)
                ->orderBy('created_at', 'desc')
                ->value('net_unit_cost');
            $cost = $latest_cost ? $latest_cost : $product_data->item_price;

            $product['id'] = $product_data->id;
            $product['name'] = $product_data->name . ' - ' . ($product_data->brand_id ? ' - [' . $product_data->brand_name . ']' : '');
            $product['code'] = $product_data->item_code;
            $product['variant_id'] = $product_data->variant_id;
            $product['cost'] = $cost;
            $product['qty'] = $product_data->item_qty;
            $product['discount'] = $product_data->discount;


            $product['tax_rate'] = $product_data->tax->rate ? $product_data->tax->rate : 0;
            $product['tax_name'] = $product_data->tax->name;
            $product['tax_method'] = $product_data->tax_method;

            $units = Unit::where('id', $product_data->product_unit_id)->get();
            $unit_name = [];
            $unit_operator = [];
            $unit_operation_value = [];
            if ($units) {
                foreach ($units as $unit) {
                    $unit_name           [] = $unit->unit_name;
                    $unit_operator       [] = $unit->operator;
                    $unit_operation_value[] = $unit->operation_value;
                }
            }
            $product['unit_name'] = implode(',', $unit_name) . ',';
            $product['unit_operator'] = implode(',', $unit_operator) . ',';
            $product['unit_operation_value'] = implode(',', $unit_operation_value) . ',';
            return $product;
        }
    }

    public function purchaseLog(Request $request)
    {
        if ($request->ajax() && permission('purchase-view')) {
            $purchase = $this->model->with('user_activity')->find($request->id);
            return view('purchase::log-data', compact('purchase'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
