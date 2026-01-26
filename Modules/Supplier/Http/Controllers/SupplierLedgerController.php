<?php

namespace Modules\Supplier\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Supplier\Entities\Supplier;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\DB;
use Modules\Supplier\Entities\SupplierLedger;

class SupplierLedgerController extends BaseController
{
    public function __construct(SupplierLedger $model)
    {
        $this->model = $model;
    }


    public function index()
    {
        if (permission('supplier-ledger-access')) {
            $this->setPageData('Supplier Ledger', 'Supplier Ledger', 'fas fa-file-invoice-dollar', [['name' => 'Supplier', 'link' => route('supplier')], ['name' => 'Supplier Ledger']]);
            $suppliers = Supplier::with('coa')->where(['status' => 1])->orderBy('name', 'asc')->get();
            return view('supplier::ledger.index', compact('suppliers'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('supplier-ledger-access')) {

                if (!empty($request->supplier_id)) {
                    $this->model->setSupplierID($request->supplier_id);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }

                $this->set_datatable_default_properties($request); // Set datatable default properties
                $list = $this->model->getDatatableList(); // Get table data
                $data = [];
                $debit = $credit = $balance = 0;
                foreach ($list as $value) {
                    $row = [];
                    $row[] = $value->voucher_date;
                    $row[] = $value->description;
                    $row[] = $value->voucher_no;
                    $row[] = $value->debit ? number_format($value->debit, 2, '.', ',') : number_format(0, 2, '.', ',');
                    $row[] = $value->credit ? number_format($value->credit, 2, '.', ',') : number_format(0, 2, '.', ',');
                    $debit += $value->debit;
                    $credit += $value->credit;
                    $balance = $debit - $credit;
                    $row[] = number_format(($balance), 2, '.', ',');
                    $data[] = $row;
                }

                return $this->datatable_draw(
                    $request->input('draw'),
                    $this->model->count_all(),
                    $this->model->count_filtered(),
                    $data
                );
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }
    public function getLedgerPreviousData(Request $request)
    {
        // previous total data
        $query = DB::table('transactions')
            ->select(DB::raw('SUM(transactions.debit) AS total_debit'), DB::raw('SUM(transactions.credit) AS total_credit'))
            ->join('chart_of_accounts as coa', 'transactions.chart_of_account_id', '=', 'coa.id')
            ->join('suppliers as s', 'coa.supplier_id', 's.id')
            ->where(['coa.parent_name' => 'Account Payable', 'transactions.approve' => 1]);

        if (!empty($request->supplier_id)) {
            $query->where('s.id', $request->supplier_id);
        }
        if (!empty($request->from_date)) {
            $query->where('transactions.voucher_date', '<', $request->from_date);
        }

        $result = $query->first();

        $totalDebit = $result->total_debit;
        $totalCredit = $result->total_credit;
        $balance =  $totalDebit - $totalCredit;
        if(empty($request->from_date))
        {
            $balance = 0;
        }

        //previous page data
        $count = $request->page * $request->length;
        $p_pageData = DB::table('transactions')
            ->join('chart_of_accounts as coa', 'transactions.chart_of_account_id', '=', 'coa.id')
            ->join('suppliers as s', 'coa.supplier_id', 's.id')
            ->where(['coa.parent_name' => 'Account Payable', 'transactions.approve' => 1]);

        if (!empty($request->supplier_id)) {
            $p_pageData->where('s.id', $request->supplier_id);
        }
        if (!empty($request->from_date)) {
            $p_pageData->where('transactions.voucher_date', '>=', $request->from_date);
        }
        if (!empty($request->to_date)) {
            $p_pageData->where('transactions.voucher_date', '<=', $request->to_date);
        }
        $prev_PageResult = $p_pageData->take($count)->get(['transactions.debit','transactions.credit']);
        $newBalance = 0;
        foreach($prev_PageResult as $result1)
        {
            $newBalance +=$result1->debit-$result1->credit;
        }

        $newBalance += $balance;
        $data = ['prev_balance' => $balance, 'new_balance'=>$newBalance];
        return response()->json($data);
    }
}
