<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Account\Entities\Transaction;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\EmployeeSalaryPayment;
use Modules\HRM\Http\Requests\EmployeeSalaryPaymenEdittFormRequest;
use Modules\HRM\Http\Requests\EmployeeSalaryPaymentFormRequest;

class EmployeeSalaryPaymentController extends BaseController
{
    public function __construct(EmployeeSalaryPayment $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('employee-salary-payment-access')) {
            $this->setPageData('Manage Employee Payment', 'Manage Employee Payment', 'fab fa-opencart', [['name' => 'Manage Employee Payment']]);
            $employees = Employee::where('activation_status', '1')->get();
            return view('hrm::salary-payment.index', compact('employees'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-salary-payment-access')) {
                if (!empty($request->employee_id)) {
                    $this->model->setEmployee($request->employee_id);
                }
                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }
                $this->set_datatable_default_properties($request); //set datatable default properties
                $list = $this->model->getDatatableList(); //get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('employee-salary-payment-change-status') && $value->status == 1) {
                        $action .= ' <a class="dropdown-item" href="' . route("employee.salary.payment.approve", $value->id) . '">' . self::ACTION_BUTTON['Approve'] . '</a>';
                    }
                    if (permission('employee-salary-payment-delete') && $value->status == 1) {
                        $action .= ' <a class="dropdown-item delete-data" data-id="' . $value->id . '" data-name="' . $value->employee->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }
                    if (permission('employee-salary-payment-view')) {
                        $action .= ' <a class="dropdown-item view-data" data-id="' . $value->id . '" data-name="' . $value->employee->name . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    }
                    if (permission('employee-salary-payment-edit') && $value->status == 1) {
                        $action .= ' <a class="dropdown-item" href="' . route("employee.salary.payment.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee->name;
                    $row[] = $value->paid_amount;
                    $row[] = $value->coa->name;
                    $row[] = $value->created_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Created Yet</span>';
                    $row[] = $value->modified_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = LEAVE_STATUS_LABEL[$value->status];
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

    public function create()
    {
        if (permission('employee-salary-payment-add')) {
            $this->setPageData('Add Employee Payslip', 'Add Employee Payslip', 'fab fa-opencart', [['name' => 'Add Employee Payslip']]);
            $employees = Employee::where('activation_status', '1')->get();
            return view('hrm::salary-payment.create', compact('employees'));
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function getEmployees(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-salary-payment-add')) {
                $idArray = $request->id;
                $employees = DB::table('employees as e')->join('chart_of_accounts as coa', 'coa.employee_id', 'e.id')->join('transactions as t', 't.chart_of_account_id', 'coa.id');
                if (!in_array("0", $idArray)) {
                    $employees->whereIn('e.id', $idArray);
                }
                $employees = $employees->groupBy('t.chart_of_account_id')->select(DB::raw('sum(debit) as debit'), DB::raw('sum(credit) as credit'), 'e.id as employee_id', 'e.name as name')->get();
                return view('hrm::salary-payment.data', compact('employees'))->render();
            }
        }
    }

    public function edit($id)
    {
        if (permission('employee-salary-payment-edit')) {
            $this->setPageData('Edit Employee Payslip', 'Edit Employee Payslip', 'fab fa-opencart', [['name' => 'Edit Employee Payslip']]);
            $data = $this->model->with('coa', 'employee')->findOrFail($id);
            $coahead = $data->employee->coa->id;
            $due = Transaction::where('chart_of_account_id', $coahead)->select(DB::raw('sum(debit) as debit'), DB::raw('sum(credit) as credit'))->first();
            $due = $due->credit - $due->debit;
            return view('hrm::salary-payment.edit', compact('data', 'due'));
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function view(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-salary-payment-view')) {
                $data = $this->model->with('employee', 'coa')->findOrFail($request->id);
                return view('hrm::salary-payment.modal-data', compact('data'))->render();
            }
        }
    }

    public function storeOrUpdate(EmployeeSalaryPaymentFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('employee-salary-payment-add') || permission('employee-salary-payment-edit')) {
                $salary = [];
                foreach ($request->salary as $value) {
                    $salary[] = [
                        'employee_id' => $value['employee_id'],
                        'paid_amount' => $value['paid_amount'],
                        'payment_method' => $value['payment_method'],
                        'account_id' => $value['account_id'],
                        'date' => date("Y-m-d"),
                        'created_by' => auth()->user()->username,
                        'status' => 1
                    ];
                }
                $this->model->insert($salary);
                $output = ['status' => 'success', 'message' => 'Salary added successfully'];
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function update(EmployeeSalaryPaymenEdittFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('employee-salary-payment-add') || permission('employee-salary-payment-edit')) {
                $data = EmployeeSalaryPayment::where('id', $request->update_id)->first();
                $data->update([
                    'paid_amount' => $request->paid_amount,
                    'payment_method' => $request->payment_method,
                    'account_id' => $request->account_id,
                    'date' => date("Y-m-d"),
                    'modified_by' => auth()->user()->username,
                    'status' => 1
                ]);
                $output = ['status' => 'success', 'message' => 'Salary Updated successfully'];
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
            if (permission('employee-salary-payment-change-status')) {
                $result = $this->model->find($request->id)->delete();
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
        if (permission('employee-salary-payment-change-status')) {
            DB::beginTransaction();
            try {
                $result = $this->model->find($id);
                $data = $result;
                $result->update([
                    'status' => '2'
                ]);
                $employeeCoa = ChartOfAccount::where('employee_id', $data->employee_id)->first();
                $this->generateSalary($employeeCoa->id, $data->employee->name, $data->paid_amount, $data->date, $data->account_id);
                $output = ['status' => 'success', 'message' => 'Payslip Approve Successful'];
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
        } else {
            $output = $this->unauthorized();
        }
        return redirect()->back()->with($output);
    }

    public function generateSalary($employe_coa, $employee_name, $net_salary, $date, $account_id)
    {
        $voucher_no = 'SALARY-' . date('ymd') . rand(1, 999);
        $voucher_date = $date;
        $description = 'Monthly Salary Paid To '. $employee_name . '';
        $liability = [
            'chart_of_account_id' => $account_id,
            'warehouse_id' => 1,
            'voucher_no' => $voucher_no,
            'voucher_type' => 'SALARY_PAYMENT',
            'voucher_date' => $voucher_date,
            'description' => $description,
            'debit' => $net_salary,
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
            'voucher_type' => 'SALARY_PAYMENT',
            'voucher_date' => $voucher_date,
            'description' => $description,
            'debit' => $net_salary,
            'credit' => 0,
            'is_opening' => 2,
            'posted' => 1,
            'approve' => 1,
            'created_by' => auth()->user()->username
        ];
        Transaction::create($liability);
        Transaction::create($employee);
    }
}
