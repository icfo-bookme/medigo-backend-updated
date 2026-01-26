<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\User;
use Exception;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Modules\Point\Entities\MoneyWisePoint;
use Modules\Point\Entities\PointWiseMoney;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\PosSalePayment;
use Modules\Sale\Entities\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\SaleNotification;
use Modules\Sale\Entities\SaleProduct;
use Illuminate\Support\Facades\Session;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\BaseController;
use Modules\Account\Entities\Transaction;
use Modules\Sale\Http\Controllers\Service\PosService;
use Modules\Setting\Entities\CustomerGroup;
use Modules\Product\Entities\ProductVariant;
use Modules\Product\Entities\WarehouseProduct;
use Modules\Sale\Http\Requests\POSFormRequest;
use Modules\Sale\Http\Requests\SaleFormRequest;
use Modules\Sale\Http\Requests\SaleDeliveryFormRequest;
use Modules\Setting\Entities\Warehouse;

class POSController extends BaseController
{
    public function __construct(Sale $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('sale-access')) {
            $this->setPageData('POS Sale', 'POS Sale', 'fab fa-opencart', [['name' => 'POS Sale']]);
            $data = [
                'taxes' => Tax::activeTaxes(),
                'brands' => Brand::allBrands(),
                'categories' => Category::allCategories(),
                'invoice_no' => 'INV-' . date('yis') . rand(1, 99),
                'products' => Product::where('status', 1)->paginate(12),
                'warehouses' => Warehouse::allWarehouses(),
                'customers' => Customer::where('status', 1)->get()
            ];
            return view('sale::pos', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function products(Request $request)
    {
        if ($request->ajax()) {
            $products = Product::where('status', 1)
                ->when($request->brand_id, fn($query, $brand_id) => $query->where('brand_id', $brand_id))
                ->when($request->category_id, fn($query, $category_id) => $query->where('category_id', $category_id))
                ->paginate(12);

            return view('sale::pos-product-list', ['products' => $products])->render();
        }
    }

//    new work ...............

    public function products_varient(Request $request)
    {
        if ($request->ajax()) {
            $product = Product::select('id', 'image')->find($request->product_id);

            $products_varient = ProductUnit::join('units as unt', 'unt.id', '=', 'product_units.product_unit_id')
                ->select('product_units.id', 'product_units.product_unit_id', 'product_units.qty', 'product_units.item_code',
                    'product_units.price', 'unt.id', 'unt.unit_name', 'product_units.discount')
                ->where('product_id', $request->product_id)
                ->where('product_units.qty', '>', 0)
                ->simplePaginate(8);

            return view('sale::pos-product-varient-list', [
                'products_varient' => $products_varient,
                'product' => $product
            ])->render();
        }
    }

    public function store(POSFormRequest $request)
    {
        if ($request->ajax() && permission('pos-add')) {
            DB::beginTransaction();
            try {
                $customer = Customer::where('id', $request->customer_id)->first();

                [$products, $total_item, $total_qty] = (new PosService())->pos_product_data_maker($request);

                $pos_data = (new PosService())->pos_entry_data_maker($request, $customer, $total_item, $total_qty);

                $pos = $this->model->create($pos_data);

                (new PosService())->activity_track_on_pos_create($pos, \auth()->user()->id);
                (new PosService())->notification_entry($pos);

                if (count($products) > 0) {
                    $pos->items()->sync($products);
                }

                $payments = (new PosService())->payment_maker($request);

                if (count($payments) > 0) {
                    $pos->payments()->sync($payments);
                }

                $output = $this->store_message($pos, $request->sale_id);
                $output['id'] = $pos->id;
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

    public function customerPoint($id)
    {
        $data = Customer::find($id);
        $point = $data->point ?? 0;
        return response()->json($point);
    }

    private function sale_balance_add($invoice_no, $grand_total, $total_tax, $sale_date, array $payment_data, $warehouse_id)
    {
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


        if (!empty($payment_data['paid_amount'])) {

            if ($payment_data['payment_method'] == 1) {
                //Cah In Hand debit
                $payment = array(
                    'chart_of_account_id' => $payment_data['account_id'],
                    'warehouse_id' => $warehouse_id,
                    'voucher_no' => $invoice_no,
                    'voucher_type' => 'INVOICE',
                    'voucher_date' => $sale_date,
                    'description' => 'Paid amount ' . $payment_data['paid_amount'] . 'Tk for Invoice No. - ' . $invoice_no,
                    'debit' => $payment_data['paid_amount'],
                    'credit' => 0,
                    'posted' => 1,
                    'approve' => 1,
                    'created_by' => optional(Auth::user())->name ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                );
            } else {
                // Bank Ledger
                $payment = array(
                    'chart_of_account_id' => $payment_data['account_id'],
                    'warehouse_id' => $warehouse_id,
                    'voucher_no' => $invoice_no,
                    'voucher_type' => 'INVOICE',
                    'voucher_date' => $sale_date,
                    'description' => 'Paid amount ' . $payment_data['paid_amount'] . 'Tk for Invoice No. - ' . $invoice_no,
                    'debit' => $payment_data['paid_amount'],
                    'credit' => 0,
                    'posted' => 1,
                    'approve' => 1,
                    'created_by' => optional(Auth::user())->name ?? '',
                    'created_at' => date('Y-m-d H:i:s')
                );
            }
            Transaction::create($payment);
        }
    }

    public function pos_invoice(int $id)
    {
        if (permission('sale-view')) {
            $this->setPageData('Sale Invoice', 'Sale Invoice', 'fas fa-file', [['name' => 'Sale Invoice']]);
            $sale = $this->model->with('products', 'customer', 'warehouse')->find($id);
            return view('sale::pos-invoice', compact('sale'));
        } else {
            return $this->access_blocked();
        }
    }
}
