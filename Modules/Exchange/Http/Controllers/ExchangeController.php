<?php

namespace Modules\Exchange\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Exchange\Entities\Exchange;
use Modules\Exchange\Entities\ExchangeProduct;
use Modules\Exchange\Entities\ExchangeProductReceive;
use Modules\Exchange\Http\Requests\ExchangeFormRequest;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleProduct;
use Modules\Sale\Http\Controllers\Service\PosService;
use Modules\Setting\Entities\Warehouse;

class ExchangeController extends BaseController
{
    public function __construct(Exchange $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('exchange-list-access')) {
            $this->setPageData('Exchange List', 'Exchange List', 'fas fa-retweet', [['name' => 'Exchange List']]);
            return view('exchange::sale.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('exchange-list-access')) {
            $fields = [
                'return_no' => 'setReturnNo',
                'invoice_no' => 'setInvoiceNo',
                'start_date' => 'setFromDate',
                'end_date' => 'setToDate',
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

                if (permission('exchange-list-access')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . route("stock.exchange.show", $value->id) . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';

                    if ($value->status == 1) {
                        $action .= ' <a class="dropdown-item delivery-status" data-id="' . $value->id . '" data-status="' . $value->status . '">' . self::ACTION_BUTTON['Change Status'] . '</a>';
                    }
                    if ($value->exchange_qty > $value->total_received_qty) {
                        $action .= ' <a class="dropdown-item" href="' . route("stock.exchange.receive", $value->id) . '">' . $this->actionButton('Receive') . '</a>';
                    }
                }

                $row = [];
                $row[] = $no;
                $row[] = '<div class="text-left"><b>Sale Inv: </b>' . $value->invoice_no . '<br><b>Exchange Inv: </b>' . $value->return_no . '</div>';
                $row[] = $value->customer_name ? $value->customer_name : '';
                $row[] = date(config('settings.date_format'), strtotime($value->exchange_date));
                $row[] = $value->grand_total;
                $row[] = '<div class="text-left"><b>Exchange: </b>' . $value->exchange_qty . '<br><b>Received: </b>' . $value->total_received_qty . '</div>';
                $row[] = EXCHANGE_STATUS_LABEL[$value->status];;
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

    public function store(ExchangeFormRequest $request)
    {
        if ($request->ajax() && permission('exchange-list-access')) {
            DB::beginTransaction();
            try {
                $exchange_products = [];
                $return_no = 'INV-EXC-' . date('yis') . rand(1, 999);

                $exchange_collection = collect($request->all())->except('')->merge([
                    'warehouse_id' => $request->warehouse_id,
                    'return_no' => $return_no,
                    'customer_id' => $request->customer_id,
                    'customer_name' => $request->customer_name,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'exchange_qty' => $request->exchange_qty,
                    'exchange_date' => $request->return_date,
                    'status' => 1,
                    'created_by' => auth()->user()->name,
                ]);
                $exchange = Exchange::create($exchange_collection->all());
                // Log exchange user activity
                $exchange->user_activity()->create([
                    'activity_type' => 'exchange_create',
                    'status_name' => 'Created',
                    'user_id' => Auth::id()
                ]);

                if ($request->has('old_sale')) {
                    foreach ($request->old_sale as $value) {
                        $exchange_products[] = [
                            'exchange_id' => $exchange->id,
                            'invoice_no' => $return_no,
                            'old_product_id' => $value['old_product_id'],
                            'old_product_code' => $value['old_product_code'],
                            'old_stock_qty' => $value['old_stock_qty'],
                            'old_exchange_qty' => $value['old_exchange_qty'],
                            'old_price' => $value['old_price'],
                        ];

                        if ($request->status == 2) {
                            $product_unit = ProductUnit::where('id', $value['old_product_id'])
                                ->where('item_code', $value['old_product_code'])
                                ->first();

                            if ($product_unit) {
                                $product_unit->increment('qty', $value['old_exchange_qty']);
                            }
                        }
                    }

                    if ($request->status == 2) {
                        Exchange::where('id', $exchange->id)->update(['total_received_qty' => $request->exchange_qty]);
                    }

                    if (!empty($exchange_products)) {
                        ExchangeProduct::insert($exchange_products);
                    }
                }

                $old_sale = Sale::where('invoice_no', $request->invoice_no)->first();

                $sale_data = [
                    'invoice_no' => $return_no,
                    'warehouse_id' => $request->warehouse_id,
                    'customer_id' => $request->customer_id,
                    'ecom_customer_id' => $request->customer_id,
                    'item' => $request->item,
                    'total_qty' => $request->total_qty,
                    'total_discount' => $request->total_discount ?? 0,
                    'total_tax' => $request->total_tax ?? 0,
                    'total_price' => $request->net_total,
                    'net_total' => $request->net_total,
                    'order_tax_rate' => $request->order_tax_rate,
                    'order_tax' => $request->order_tax,
                    'order_discount_per' => $request->order_discount_per,
                    'order_discount' => $request->order_discount ?? 0,
                    'order_discount_rate' => $request->order_discount_rate ?? 0,
                    'adjustment_per' => $request->adjustment_per,
                    'adjustment' => $request->adjustment ?? 0,
                    'shipping_cost' => $request->shipping_cost ?? 0,
                    'grand_total' => $request->grand_total,
                    'paid_amount' => $request->paid_amount ?? 0,
                    'due_amount' => $request->due_amount ?? 0,
                    'name' => $request->name ?? '',
                    'phone' => $request->phone ?? '',
                    'information' => $request->information ?? '',
                    'optional_information' => $old_sale->optional_information ?? '',
                    'sale_type' => 1,
                    'sale_date' => date('Y-m-d'),
                    'delivery_status' => $request->delivery_status ?? 1,
                    'payment_status' => $request->payment_status,
                    'order_type' => 2,
                    'order_source_id' => $old_sale->order_source_id ?? '',
                    'created_by' => auth()->user()->name,
                ];
                $new_sale = Sale::create($sale_data);
                // Log New sale user activity
                $new_sale->user_activity()->create([
                    'activity_type' => 'sale_create',
                    'status_name' => 'Created',
                    'user_id' => Auth::id()
                ]);

                $old_sale->update(['delivery_status' => 7]);
                // Log old sale user activity
                $old_sale->user_activity()->create([
                    'activity_type' => 'sale_status_change',
                    'status_name' => ORDER_STATUS_VALUE[7],
                    'user_id' => Auth::id()
                ]);

                $products = [];
                if ($request->has('new_sale')) {
                    foreach ($request->new_sale as $value) {
                        $variant = ProductUnit::with('product', 'unit')
                            ->where('product_id', $value['product_id'])
                            ->where('item_code', $value['product_code'])
                            ->first();

                        if ($variant) {
                            $products[] = [
                                'sale_id' => $new_sale->id,
                                'product_id' => $value['product_id'],
                                'product_variant_id' => $variant->id,
                                'qty' => $value['exchange_qty'],
                                'sale_unit_id' => $variant->unit ? $variant->unit->id : null,
                                'net_unit_price' => $value['price'],
                                'discount' => 0,
                                'discount_rate' => 0,
                                'total' => $value['price'] * $value['exchange_qty'],
                                'order_type' => 2,
                                'created_at' => now(),
                            ];
                        }
                    }

                    if (!empty($products)) {
                        SaleProduct::insert($products);
                    }
                }
                $payments = [];
                if ($request->has('payment') && $request->payment_status != 3) {
                    foreach ($request->payment as $payment_list) {
                        $payments[] = [
                            'payment_method' => $payment_list['payment_method'],
                            'account_id' => $payment_list['account_id'] ?? 0,
                            'reference_no' => $payment_list['reference_no'] ?? 0,
                            'paid_amount' => $payment_list['payment_amount']
                        ];
                    }
                }

                if (count($payments) > 0) {
                    $new_sale->payments()->sync($payments);
                }

                $output = ['status' => 'success', 'message' => 'Data Saved Successfully'];
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function show(int $id){
        if(permission('exchange-list-access')){
            $this->setPageData('Sale Exchange Details','Sale Exchange Details','fas fa-file',[['name' => 'Sale Exchange Details']]);
            $sale = $this->model->with('exchange_products','customer')->find($id);
            return view('exchange::sale.view',compact('sale'));
        }else{
            return $this->access_blocked();
        }
    }

    public function changeStatus(Request $request)
    {
        if ($request->ajax() && permission('exchange-list-access')) {
            DB::beginTransaction();
            try {
                $receive_products = [];
                $id = $request->id;
                $exchange = $this->model->find($id);

                if ($request->status == 1) {
                    foreach ($exchange->exchange_products as $value) {
                        $receive_products[] = [
                            'exchange_id' => $id,
                            'invoice_no' => $exchange->return_no,
                            'product_id' => $value['old_product_id'],
                            'product_code' => $value['old_product_code'],
                            'price' => $value['old_price'],
                            'receive_qty' => $value['old_exchange_qty'],
                            'sub_total' => $value['old_price'] * $value['old_exchange_qty'],
                            'receive_date' => date('Y-m-d'),
                        ];

                        $value->update(['received_qty' => $value['old_exchange_qty']]);

                        // Find the product unit and increment quantity
                        $product_unit = ProductUnit::where('product_id', $value['old_product_id'])
                            ->where('item_code', $value['old_product_code'])
                            ->first();

                        if ($product_unit) {
                            $product_unit->increment('qty', $value['old_exchange_qty']);
                        }
                    }

                    $exchange->update(['total_received_qty' => $exchange->exchange_qty, 'status' => 2]);

                    ExchangeProductReceive::insert($receive_products);
                } else {
                    $exchange->update(['status' => $request->status]);
                }

                // Log exchange user activity
                $exchange->user_activity()->create([
                    'activity_type' => 'exchange_status_change',
                    'status_name' => 'Stock Updated',
                    'user_id' => Auth::id()
                ]);

                DB::commit();
                return response()->json(['status' => 'success', 'message' => 'Status Changed Successfully']);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function exchangeLog(Request $request)
    {
        if ($request->ajax() && permission('exchange-list-access')) {
            $sale = $this->model->with('user_activity')->find($request->id);
            return view('sale::log-data', compact('sale'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function stockExchange($id)
    {
        if (permission('exchange-list-access')) {
            $sale = Sale::with('sale_products', 'customer')->where('id', $id)->first();

            if ($sale) {
                $this->setPageData('Exchange', 'Exchange Product', 'fas fa-retweet', [['name' => 'Exchange Product']]);

                $warehouse_product = ProductUnit::with('product', 'unit')
                    ->where('product_units.qty', '>', 0)
                    ->get();

                $data = [
                    'sale' => $sale,
                    'warehouse_product' => $warehouse_product,
                ];
                return view('exchange::sale.exchange_form', $data);
            } else {
                return redirect('sale')->with(['status' => 'error', 'message' => 'No Invoice Data Found']);
            }
        } else {
            return $this->access_blocked();
        }
    }

    public function exchangeReceive($id)
    {
        if (permission('exchange-list-access')) {
            $setTitle = 'Exchange Received product';
            $setSubTitle = 'Exchange Received product';
            $this->setPageData($setSubTitle, $setSubTitle, 'fas fa-edit', [['name' => $setTitle, 'link' => route('sale')], ['name' => $setSubTitle]]);
            $exchange = $this->model->find($id);
            $data = [
                'exchange' => $exchange,
                'invoiceNo' => round(microtime(true) * 1000),
                'warehouses' => Warehouse::where('status', 1)->get(),
                'customers' => Customer::all(),
            ];
            return view('exchange::sale.receive_form', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function exchangeReceiveStore(Request $request)
    {
        if ($request->ajax() && permission('exchange-list-access')) {
            DB::beginTransaction();
            try {
                $receive_products = [];
                $exchange = $this->model->with('exchange_products')->find($request->exchange_id);

                if ($request->has('exchange')) {
                    foreach ($request->exchange as $value) {
                        $receive_products[] = [
                            'exchange_id' => $request->exchange_id,
                            'invoice_no' => $request->invoice_no,
                            'product_id' => $value['product_id'],
                            'product_code' => $value['product_code'],
                            'price' => $value['price'],
                            'receive_qty' => $value['receive_qty'],
                            'sub_total' => $value['sub_total'],
                            'receive_date' => $request->receive_date,
                        ];

                        $product_unit = ProductUnit::where('id', $value['product_id'])
                            ->where('item_code', $value['product_code'])
                            ->first();

                        if ($product_unit) {
                            $product_unit->increment('qty', $value['receive_qty']);
                        }
                        if (!empty($exchange->exchange_products)) {
                            $exchange->exchange_products()->increment('received_qty', $value['receive_qty']);
                        }
                    }
                }
                ExchangeProductReceive::create($receive_products);

                if ($exchange) {
                    $exchange->increment('total_received_qty', $request->total_received_qty);
                    // Log exchange user activity
                    $exchange->user_activity()->create([
                        'activity_type' => 'exchange_receive',
                        'status_name' => 'Received',
                        'user_id' => Auth::id()
                    ]);
                }
                $output = ['status' => 'success', 'message' => 'Receive Store Successfully'];
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function saleProductDetails(Request $request)
    {
        $product = SaleProduct::with('product', 'product_variant')->where(['id' => $request->product_id])->first();
        $data = [
            'product_id' => $product->product_id,
            'product_code' => $product->product_variant->item_code,
            'stockQty' => $product->qty,
            'price' => $product->total / $product->qty,
        ];
        return response()->json($data);
    }

    public function getProductDetails(Request $request)
    {
        $product = ProductUnit::with('product', 'unit')->where('product_id', $request->product_id)->first();
        $data = [
            'product_id' => $product->product_id,
            'product_code' => $product->item_code,
            'stockQty' => $product->qty,
            'price' => $product->price,
        ];
        return response()->json($data);
    }
}
