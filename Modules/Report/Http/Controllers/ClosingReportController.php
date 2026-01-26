<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\ClosingHead;
use Modules\Report\Entities\DailyClosingHead;
use Modules\Report\Http\Requests\ClosingHeadFormRequest;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\DailyClosing;
use Modules\Report\Http\Requests\ClosingFormRequest;

class ClosingReportController extends BaseController
{
    public function __construct(DailyClosing $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('closing-access')) {
            $this->setPageData('Closing Account', 'Closing Account', 'fas fa-file', [['name' => 'Report', 'link' => 'javascript::void();'], ['name' => 'Closing Account']]);

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

            $data = [
                'warehouses' => Warehouse::allWarehouses(),
                'heads' => ClosingHead::all(),
                'cash_data' => $cash_data
            ];
            return view('report::closing-report.form', $data);
        } else {
            return $this->access_blocked();
        }

    }

    // Closing Head
    public function storeHead(ClosingHeadFormRequest $request)
    {
        if (permission('closing-add') && $request->ajax()) {
            DB::beginTransaction();
            $collection = collect($request->validated());
            $collection = $this->track_data($collection, $request->update_id);
            $result = ClosingHead::updateOrCreate(['id' => $request->update_id], $collection->all());
            $output = $this->store_message($result, $request->update_id);
            DB::commit();
        } else {
            DB::rollBack();
            $output = $this->unauthorized();
        }
        return response()->json($output);
    }

    public function deleteHead(Request $request)
    {
        if (permission('closing-delete') && $request->ajax()) {
            DB::beginTransaction();
            $data = DailyClosingHead::where('closing_head_id', $request->id)->where('amount', '>', 0)->first();
            if (!$data) {
                $result = ClosingHead::where(['id' => $request->id])->delete();
                $output = $this->delete_message($result);
            } else {
                $output = ['status' => 'error', 'message' => 'Can not delete this label'];
            }
            DB::commit();
        } else {
            DB::rollBack();
            $output = $this->unauthorized();
        }
        return response()->json($output);
    }

    public function closing_data(Request $request)
    {
        if ($request->ajax()) {
            $warehouse_id = $request->warehouse_id;
            $last_closing_amount_data = DailyClosing::select('closing_amount')->where('warehouse_id', $warehouse_id)->latest('date')->first();
            $cash_data = DB::table('transactions as t')
                ->selectRaw('SUM(debit) AS cash_in_amount, SUM(credit) AS cash_out_amount')
                ->leftjoin('chart_of_accounts as coa', 't.chart_of_account_id', '=', 'coa.id')
                ->where([
                        't.voucher_date' => date('Y-m-d'),
                        'coa.code' => $this->coa_head_code('cash_in_hand'),
                        't.warehouse_id' => $warehouse_id]
                )
                ->first();
            $last_closing_amount = $last_closing_amount_data ? $last_closing_amount_data->amount : 0;
            $cash_in = $cash_data ? ($cash_data->cash_in_amount ?? 0) : 0;
            $cash_out = $cash_data ? ($cash_data->cash_out_amount ?? 0) : 0;
            if ($last_closing_amount) {
                $cash_in_hand = ($last_closing_amount + $cash_in) - $cash_out;
            } else {
                $cash_in_hand = $cash_in - $cash_out;
            }

            $data = [
                "last_day_closing" => $last_closing_amount,
                "cash_in" => $cash_in,
                "cash_out" => $cash_out,
                "cash_in_hand" => $cash_in_hand,
            ];
            return response()->json($data);

        }
    }

    public function store(ClosingFormRequest $request)
    {
        if ($request->ajax() && permission('closing-add')) {
            $closing_data = DB::table('daily_closings')->where(['date' => date('Y-m-d'), 'warehouse_id' => auth()->user()->warehouse_id])->get()->count();
            if ($closing_data > 0) {
                $output = ['status' => 'error', 'message' => 'Already Closed Today'];
            } else {
                DB::beginTransaction();
                try {
                    $collection = collect($request->validated())->except(['closing', 'grndtotal'])->merge(['created_by' => auth()->user()->username, 'warehouse_id' => auth()->user()->warehouse_id]);

                    $closingValue = [];
                    if ($request->has('closing')) {
                        foreach ($request->closing as $key => $value) {
                            $closingValue[] = [
                                'amount' => $value['amount'],
                                'closing_head_id' => $value['closing_head_id']
                            ];
                        }
                    }
                    $result = $this->model->create($collection->all());
                    $result->allHeads()->attach($closingValue);
                    $output = $this->store_message($result, null);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
            }
        } else {
            $output = $this->unauthorized();
        }
        return response()->json($output);
    }

    public function report()
    {
        if (permission('closing-report-access')) {
            $this->setPageData('Closing Report', 'Closing Report', 'fas fa-file-signature', [['name' => 'Report'], ['name' => 'Closing Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses(),
            ];
            return view('report::closing-report.report', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (!empty($request->start_date)) {
                $this->model->setStartDate($request->start_date);
            }
            if (!empty($request->end_date)) {
                $this->model->setEndDate($request->end_date);
            }
            if (!empty($request->warehouse_id)) {
                $this->model->setWarehouseID($request->warehouse_id);
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $value->warehouse->name;
                $row[] = date(config('settings.date_format'), strtotime($value->date));
                $row[] = $value->title;
                $row[] = number_format(($value->closing_amount), 2, '.', '');
                $row[] = $value->created_by;
                $row[] = '<a class="view_data" href="' . route("closing.view", $value->id) . '" target="_blank">' . self::ACTION_BUTTON['View'] . '</a>';;
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);

        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function view($id)
    {
        if (permission('closing-access')) {
            $this->setPageData('Closing Account', 'Closing Account Details', 'fas fa-file', [['name' => 'Report', 'link' => 'javascript::void();'], ['name' => 'Closing Account']]);
            $details = $this->model->with('warehouse', 'dailyClosingHeads')->findOrFail($id);
            $data = [
                'details' => $details
            ];
            return view('report::closing-report.view', $data);
        } else {
            return $this->access_blocked();
        }

    }
}
