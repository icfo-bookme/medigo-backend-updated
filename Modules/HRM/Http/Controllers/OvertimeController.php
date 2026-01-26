<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\Transaction;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Overtime;
use Modules\HRM\Entities\Salary;
use Modules\HRM\Http\Requests\OvertimeFormRequest;

class OvertimeController extends BaseController
{
    public function __construct(Overtime $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('overtime-access')) {
            $this->setPageData('Manage Employee Overtime', 'Manage Employee Overtime', 'fab fa-opencart', [['name' => 'Manage Employee Overtime']]);
            $employees = Employee::where('activation_status', '1')->get();
            return view('hrm::overtime.index', compact('employees'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('overtime-access')) {
                if (!empty($request->employee_id)) {
                    $this->model->setEmployee($request->employee_id);
                }
                if (!empty($request->start_date)) {
                    $this->model->setFromDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setToDate($request->end_date);
                }
                if (!empty($request->type)) {
                    $this->model->setType($request->type);
                }
                $this->set_datatable_default_properties($request); //set datatable default properties
                $list = $this->model->getDatatableList(); //get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('overtime-edit') && $value->approval_status == 1) {
                        $action .= ' <a class="dropdown-item edit-data" data-id="' . $value->id . '" data-name="' . $value->employee->name . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    }
                    if (permission('overtime-approve') && $value->approval_status == 1) {
                        $action .= ' <a class="dropdown-item" href="' . route("overtime.approve", $value->id) . '">' . self::ACTION_BUTTON['Approve'] . '</a>';
                    }
                    if (permission('overtime-view')) {
                        $action .= ' <a class="dropdown-item view-data" data-id="' . $value->id . '" data-name="' . $value->employee->name . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    }
                    if (permission('overtime-delete') && $value->approval_status == 1) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->employee->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee->name;
                    $row[] = 'From ' . date("d-m-Y H:i:s a", strtotime($value->start_date . ' ' . $value->start_time)) . ' To ' . date("d-m-Y H:i:s a", strtotime($value->end_date . ' ' . $value->end_time));
                    $row[] = $value->working_hour;
                    $row[] = $value->approve_remarks;
                    $row[] = $value->created_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = $value->modified_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = $value->approved_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Approved Yet</span>';
                    $row[] = LEAVE_STATUS_LABEL[$value->approval_status];
                    $row[] = action_button($action); //custom helper function for action button
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

    public function edit(Request $request)
    {
        if ($request->ajax()) {
            if (permission('overtime-edit')) {
                $data = $this->model->findOrFail($request->id);
                return $data;
            }
        }
    }

    public function view(Request $request)
    {
        if ($request->ajax()) {
            if (permission('overtime-view')) {
                $data = $this->model->with('employee')->findOrFail($request->id);
                return view('hrm::overtime.modal-data', compact('data'))->render();
            }
        }
    }

    public function storeOrUpdate(OvertimeFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('overtime-add') || permission('overtime-edit')) {
                $collection = collect($request->validated())->merge(['created_by' => Auth::user()->username, 'dept_type' => '1']);
                $collection = $this->track_data($collection, $request->update_id);
                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                $output = $this->store_message($result, $request->update_id);
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('overtime-delete')) {
                $result = $this->model->find($request->id);
                $result->update([
                    'approval_status' => '3',
                    'deleted_by' => auth()->user()->username,
                ]);
                $output = $this->delete_message($result);
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function approve($id)
    {
        if (permission('overtime-approve')) {
            DB::beginTransaction();
            try {
                $result = $this->model->find($id);
                $data = $result;
                $result->update([
                    'approval_status' => '2',
                    'approved_by' => auth()->user()->username,
                ]);
                $overtime = Salary::where('employee_id', $data->employee_id)->value('overtime_rate');
                $overtime = $overtime * $data->working_hour;
                $employeeCoa = ChartOfAccount::where('employee_id', $data->employee_id)->first();
                $this->generateOvertime($employeeCoa->id, $data->employee->name, $overtime);
                $output = ['status' => 'success', 'message' => 'Overtime Approve Successful'];
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $output = ['status' => 'success', 'message' => $e->getMessage()];
            }
        } else {
            $output = $this->unauthorized();
        }
        return redirect()->back()->with($output);
    }
    public function generateOvertime($employe_coa, $employee_name, $overtime)
    {
        $voucher_no = 'EOVERTIME-' . date('ymd') . rand(1, 999);
        $voucher_date = date('Y-m-d');
        $description = 'Overtime Generated For ' . $employee_name . '';
        $Overtimeliability = [
            'chart_of_account_id' => 703,
            'warehouse_id' => 1,
            'voucher_no' => $voucher_no,
            'voucher_type' => 'Overtime',
            'voucher_date' => $voucher_date,
            'description' => $description,
            'debit' => $overtime,
            'credit' => 0,
            'is_opening' => 2,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->username
        ];

        $employee = [
            'chart_of_account_id' => $employe_coa,
            'warehouse_id' => 1,
            'voucher_no' => $voucher_no,
            'voucher_type' => 'Overtime',
            'voucher_date' => $voucher_date,
            'description' => $description,
            'debit' => 0,
            'credit' => $overtime,
            'is_opening' => 2,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->username
        ];
        Transaction::create($Overtimeliability);
        Transaction::create($employee);
    }
}
