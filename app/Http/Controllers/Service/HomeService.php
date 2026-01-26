<?php

namespace App\Http\Controllers\Service;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerFeedback;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Report\Entities\DailyClosing;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\VisitorStat;
use Modules\Setting\Entities\Warehouse;

class HomeService
{
    public function getShowroomsData($start_date, $end)
    {
        $showrooms = [];
        $warehouses = auth()->user()->warehouse_id
            ? Warehouse::where('id', auth()->user()->warehouse_id)->get()
            : Warehouse::allWarehouses();

        foreach ($warehouses as $key => $value) {
            $showrooms[$key]['showroom'] = $value->name;
            $showrooms[$key]['yearly_sale_amount'] = $this->getSaleData($start_date, $end, $value->id);
        }
        return $showrooms;
    }

    public function getDashboardData($start_date, $end_date)
    {
        if ($start_date && $end_date) {
            $userWarehouseId = auth()->user()->warehouse_id;

            $sale = $this->getSum('sales', 'sale_date', $start_date, $end_date, 'net_total', $userWarehouseId);
            $purchase = $this->getSum('purchases', 'purchase_date', $start_date, $end_date, 'total_cost', null);
            $avgDeliveryTime = $this->getAvgDeliveryTime($start_date, $end_date, $userWarehouseId);

//            $income = $this->getSum('sales', 'sale_date', $start_date, $end_date, 'paid_amount', $userWarehouseId);
            $expense = $this->getSum('expenses', 'date', $start_date, $end_date, 'amount', $userWarehouseId);
            $customer = $this->getCount('customers', 'created_at', Carbon::parse($start_date)->startOfDay(), Carbon::parse($end_date)->endOfDay(), 'id', $userWarehouseId);
            $totalCustomer = Customer::count();
            $totalProduct = Product::count();
            $stockValue = $this->getStockValue();
            $deliveryOrders = $this->getDeliveryOrdersCount($start_date, $end_date, $userWarehouseId);
            $deadlineMissPercentage = $this->getDeadlineMissPercentage($start_date, $end_date, $userWarehouseId);

            $total_visitor = $this->visited_user_count_provider($start_date, $end_date);
            $customer_feedback = CustomerFeedback::whereBetween('created_at', [Carbon::parse($start_date)->startOfDay(), Carbon::parse($end_date)->endOfDay()])->count();


            $cashBankId = ChartOfAccount::where(function ($query) {
                $query->whereNotNull('bank_id')
                    ->orWhereNotNull('mobile_bank_id');
            })
                ->orWhere('code', 1020101)
                ->pluck('id');
            $cash_data = DB::table('transactions as t')
                ->selectRaw('SUM(debit) AS cash_in_amount, SUM(credit) AS cash_out_amount')
                ->leftjoin('chart_of_accounts as coa', 't.chart_of_account_id', '=', 'coa.id')
                ->where([
                        't.warehouse_id' => auth()->user()->warehouse_id]
                )
                ->where('t.voucher_date', '<=', date('Y-m-d'))
                ->whereIn('t.chart_of_account_id', $cashBankId)
                ->first();


            $closingBalance = DailyClosing::latest()->value('closing_amount');
            $currentBalance = $cash_data->cash_in_amount - $cash_data->cash_out_amount;

            return [
                'stock_value' => $stockValue,
                'delivered_orders' => $deliveryOrders,
                'product_count' => $totalProduct,
                'avg_delivery_time' => $avgDeliveryTime,
                'deadline_miss_percentage' => $deadlineMissPercentage,
                'total_customer' => $totalCustomer,
                'total_visitor' => $total_visitor,
                'customer_feedback' => $customer_feedback,
                'customer' => $customer,
                'sale' => $sale,
                'purchase' => $purchase,
                'closingBalance' => $closingBalance,
                'currentBalance' => $currentBalance
            ];
        }
        return [];
    }

    public function getCurrentBalanceDetails()
    {
        $cashBankId = ChartOfAccount::where(function ($query) {
            $query->whereNotNull('bank_id')
                ->orWhereNotNull('mobile_bank_id');
        })
            ->orWhere('code', 1020101)
            ->pluck('id');
        $cashInInvoices = DB::table('transactions as t')
            ->selectRaw('debit as amount, voucher_no, voucher_type')
            ->leftJoin('chart_of_accounts as coa', 't.chart_of_account_id', '=', 'coa.id')
            ->where('t.warehouse_id', auth()->user()->warehouse_id)
            ->where('t.debit', '>', 0) // Corrected condition here
            ->where('t.voucher_date', '<=', date('Y-m-d'))
            ->whereIn('t.chart_of_account_id', $cashBankId)
            ->orderBy('t.voucher_type')
            ->get();


        $cashoutInvoices = DB::table('transactions as t')
            ->selectRaw('credit as amount, voucher_no, voucher_type')
            ->leftjoin('chart_of_accounts as coa', 't.chart_of_account_id', '=', 'coa.id')
            ->where([
                    't.warehouse_id' => auth()->user()->warehouse_id]
            )
            ->where('t.credit', '>', 0) // Corrected condition here
            ->where('t.voucher_date', '<=', date('Y-m-d'))
            ->whereIn('t.chart_of_account_id', $cashBankId)
            ->orderBy('t.voucher_type')
            ->get();

//        dd($cashInInvoices);

        return [
            'cashInInvoices'  => $cashInInvoices,
            'cashoutInvoices' => $cashoutInvoices
        ];
    }

    public function visited_user_count_provider($start_date, $end_date)
    {
        return VisitorStat::
//         whereRaw('visited_at >= ? AND visited_at <= ?', [$start_date, $end_date])
        distinct('ip_address')
            ->count('ip_address');

    }

    private function getSum($table, $dateColumn, $startDate, $endDate, $sumColumn, $warehouseId = null)
    {
        $query = DB::table($table)->whereBetween($dateColumn, [$startDate, $endDate]);
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return $query->sum($sumColumn);
    }

    private function getCount($table, $dateColumn, $startDate, $endDate, $countColumn, $warehouseId = null)
    {
        $query = DB::table($table)->whereBetween($dateColumn, [$startDate, $endDate]);
        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return $query->count($countColumn);
    }

    private function getDeliveryOrdersCount($startDate, $endDate, $warehouseId = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('delivery_status', 2);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        return $query->count();
    }

    private function getStockValue()
    {
        return ProductUnit::query()->sum(DB::raw('qty * price'));
    }

    private function getAvgDeliveryTime($startDate, $endDate, $warehouseId = null)
    {
        $query = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('delivery_status', 2);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }
        $avgDeliveryTime = $query->select(DB::raw('AVG(DATEDIFF(delivery_date, sale_date)) as avg_delivery_time'))
            ->value('avg_delivery_time');

        return $avgDeliveryTime ?? 0; // Return 0 if avgDeliveryTime is null
    }

    private function getDeadlineMissPercentage($startDate, $endDate, $warehouseId = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('delivery_status', 2);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $query->selectRaw('COUNT(*) AS total_orders, SUM(CASE WHEN delivery_date IS NOT NULL AND est_delivery_date IS NOT NULL AND delivery_date > est_delivery_date THEN 1 ELSE 0 END) AS missed_deadline_orders');

        $result = $query->first();

        $totalOrdersCount = $result->total_orders;
        $missedDeadlineOrdersCount = $result->missed_deadline_orders;

        if ($totalOrdersCount > 0) {
            return ($missedDeadlineOrdersCount / $totalOrdersCount) * 100;
        } else {
            return 0;
        }
    }

    public function getProductStockAlert()
    {
        $output = '';
        $count = 0;
        $warehouses = auth()->user()->warehouse_id
            ? Warehouse::where('id', auth()->user()->warehouse_id)->get()
            : Warehouse::allWarehouses();

        foreach ($warehouses as $value) {
            $products = DB::table('warehouse_products as wp')
                ->leftJoin('products as p', 'wp.product_id', '=', 'p.id')
                ->where('warehouse_id', $value->id)
                ->whereColumn('p.alert_quantity', '>', 'wp.qty')
                ->count();
            if ($products > 0) {
                $text = $products == 1 ? 'product' : 'products';
                $output .= '<div class="pt-3">
                                <a href="' . url("product-stock-alert-report") . '" class="text-danger">
                                    <div class="text-center font-weight-bolder" style="height: 50px; width: 100%;align-items: center;display: flex;justify-content: space-evenly; margin: 0 auto;color: #f64e60;font-size: 12px;">
                                        <img src="' . asset("images/alert.svg") . '" style="width: 30px;"> ' . $products . ' ' . $text . ' are going to be out of stock from ' . $value->name . '
                                    </div>
                                </a>
                            </div>';
                $count += 1;
            }
        }

        return [
            'count' => $count,
            'output' => $output,
        ];
    }

    public function getSaleData($start_date, $end_date, $id)
    {
        $yearly_sale_amount = [];
        $current_date = strtotime($start_date);
        $end_timestamp = strtotime($end_date);

        while ($current_date < $end_timestamp) {
            $current_month_start = date('Y-m-01', $current_date);
            $current_month_end = date('Y-m-t', $current_date);

            $sale_amount = Sale::where('warehouse_id', $id)
                ->whereBetween('sale_date', [$current_month_start, $current_month_end])
                ->sum('net_total');

            $yearly_sale_amount[] = number_format($sale_amount, 2, '.', '');
            $current_date = strtotime('+1 month', $current_date);
        }

        return $yearly_sale_amount;
    }
}
