<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\Allowances;
use Modules\HRM\Entities\Bonus;
use Modules\HRM\Entities\Deduction;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Salary;
use Modules\HRM\Entities\SalaryPayment;
use Modules\HRM\Http\Requests\SalaryPaymentFormRequest;

class SalaryPaymentController extends BaseController
{
    public function __construct(SalaryPayment $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('salary-payment-access')){
            $setTitle = __('file.Manage Salary Payment');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $employee   = Salary::select('id','employee_id')->get();
            $salary_pay = SalaryPayment::select('id','payment_month')->groupBy('payment_month')->get();
            return view('hrm::salary-payment.index',compact('deletable','employee','salary_pay'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('salary-payment-access')){

                if (!empty($request->employee_id)) {
                    $this->model->setName($request->employee_id);
                }
                if (!empty($request->payment_month)) {
                    $this->model->setMonth($request->payment_month);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('salary-payment-edit')){
                        $action .= ' <a class="dropdown-item" href="'.url("salaryPayment/edit/".$value->id).'">'.$this->actionButton('Edit').'</a>';
                    }
                    if (permission('salary-payment-view')) {
                        $action .= ' <a class="dropdown-item" href="'.url("salaryPayment/salary-payslip/".$value->id).'">'.$this->actionButton('paySlip').'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee ? $value->employee->name : '';
                    $row[] = $value->employee ? $value->employee->employee_id : '';
                    $row[] =  date('F-Y', strtotime($value->payment_month));
                    $row[] = '<span class="label label-success label-pill label-inline mt-1" style="padding: 10px 0px;height: 33px;min-width: 100% !important;font-size:12px">'.$value->payment_amount.'/-TK</span>';
                    $row[] = $value->net_salary == $value->payment_amount ? '<span class="label label-danger label-pill label-inline" style="padding: 10px 0px;height: 33px;width: 100%;font-size: 14px;">Paid<span>' : '<span class="label label-warning label-pill label-inline" style="padding: 10px 0px;height: 33px;width: 100%;font-size: 14px;">Due :   '.($value->net_salary - $value->payment_amount).'/-TK<span>';
                    $row[] = $value->note;
                    $row[] = date('Y-m-d', strtotime($value->created_at)) ;
                    $row[] = $value->user ? $value->user->name : '';
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function create(Request $request){
        if(permission('salary-payment-access')){
            $setTitle = __('file.Salary Payment');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $employee_id    = $request->employee_id;
            $payment_month  = $request->payment_month;
            $checker        = SalaryPayment::where('payment_month',$request->payment_month)->exists();
            if ($employee_id){
                if ($checker){
                    $salarys    = SalaryPayment::where('employee_id',$employee_id)->where('payment_month',$payment_month)->get();
                    return view('hrm::salary-payment.view',compact('salarys','payment_month'));
                }else{
                    $bonuses    = Bonus::select('id','bonus_percentage')->where('type',2)->where('bonus_month',$payment_month)->get();
                    $bonus      = $bonuses->SUM('bonus_percentage');
                    $salary     = Salary::where('employee_id',$employee_id)->where('joining_month','<=',$payment_month)->get();
                }
            }else{
                if ($checker){
                    $salarys    = SalaryPayment::where('payment_month',$payment_month)->get();
                    return view('hrm::salary-payment.view',compact('salarys','payment_month'));
                }else{
                    $salary     = Salary::where('joining_month','<=',$payment_month)->get();
                    $bonuses    = Bonus::select('id','bonus_percentage')->where('type',2)->where('bonus_month',$payment_month)->get();
                    $bonus      = $bonuses->SUM('bonus_percentage');
                }
            }
            return view('hrm::salary-payment.create',compact('salary','payment_month','bonus'));
        }else{
            return $this->access_blocked();
        }
    }

    public function allowanceView($id,$month){
        if(permission('salary-payment-access')){
            $setTitle = __('file.All Allowances');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $allowance          = Salary::where('employee_id',$id)->first();
            $other_allowance    = Allowances::where('employee_id',$id)->where('allowances_month',$month)->get();
            return view('hrm::salary-payment.allowance-view',compact('allowance','other_allowance','month'));
        }else{
            return $this->access_blocked();
        }
    }

    public function deductionView($id,$month){
        if(permission('salary-payment-access')){
            $setTitle = __('file.All Deduction');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deduction          = Salary::where('employee_id',$id)->first();
            $other_deduction    = Deduction::where('employee_id',$id)->where('deduction_month',$month)->get();
            return view('hrm::salary-payment.deduction-view',compact('deduction','other_deduction','month'));
        }else{
            return $this->access_blocked();
        }
    }

    public function payslipView($id){
        if(permission('salary-payment-view')){
            $setTitle = __('file.Pay Slip');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $payslip         = $this->model->find($id);
            $allowance       = Salary::where('employee_id',$payslip->employee_id)->first();
            $other_allowance = Allowances::where('employee_id',$payslip->employee_id)->where('type',1)->where('status',1)->where('allowances_month',$payslip->payment_month)->get();
            $deduction       = Salary::where('employee_id',$payslip->employee_id)->first();
            $other_deduction = Deduction::where('employee_id',$payslip->employee_id)->where('type',1)->where('status',1)->where('deduction_month',$payslip->payment_month)->get();
            return view('hrm::salary-payment.payslip',compact('payslip','allowance','other_allowance','deduction','other_deduction'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store_or_update_data(SalaryPaymentFormRequest $request) {
        if($request->ajax()){
            $user_id = Auth::user()->id;

            if(permission('salary-payment-add')){
                $collection     = collect($request->all());
                foreach ($collection['employee_id'] as $key => $val) {
                    $employees = new SalaryPayment();
                    $employees->payment_type    = $collection['payment_type'];
                    $employees->note            = $collection['note'];
                    $employees->created_by      = $user_id;
                    $employees->provident_fund  = $collection['provident_fund'][$key];
                    $employees->payment_amount  = $collection['payment_amount'][$key];
                    $employees->payment_month   = $collection['payment_month'][$key];
                    $employees->basic_salary    = $collection['basic_salary'][$key];
                    $employees->net_salary      = $collection['net_salary'][$key];
                    $employees->gross_salary    = $collection['gross_salary'][$key];
                    $employees->salary_advance  = $collection['salary_advance'][$key];
                    $employees->total_deduction = $collection['total_deduction'][$key];
                    $employees->employee_id     = $collection['employee_id'][$key];
                    $employees->total_allowance = $collection['total_allowance'][$key];
                    $employees->bonus           = $collection['bonus'][$key];
                    $employees->save();
                }
                $output         = $this->store_message($employees);
            }else{
                $output         = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit($id){
        if(permission('salary-payment-edit')){
            $setTitle = __('file.payment Edit');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $salary_pay  = SalaryPayment::find($id);
            return view('hrm::salary-payment.edit',compact('salary_pay'));
        }else{
            return $this->access_blocked();
        }
    }

    public function update_data(Request $request){
        if($request->ajax()){
            if(permission('salary-payment-edit')){
                $collection  = collect($request->all());
                $salary      = $this->model->find($request->update_id)->update($collection->all());
                $output      = $this->store_message($salary);
            }else{
                $output      = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
