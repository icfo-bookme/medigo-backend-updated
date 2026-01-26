<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Allowances;
use Modules\HRM\Entities\Bonus;
use Modules\HRM\Entities\Deduction;
use Modules\HRM\Entities\Labour;
use Modules\HRM\Entities\LabourSalaryPayment;
use Modules\HRM\Http\Requests\LabourSalaryPaymentFormRequest;

class LabourSalaryPaymentController extends BaseController
{
    public function __construct(LabourSalaryPayment $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('labour-salary-payment-access')){
            $setTitle = __('file.Manage Labour Salary Payment');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $labour     = Labour::where('status',1)->get();
            $salary_pay = LabourSalaryPayment::select('id','payment_month')->groupBy('payment_month')->get();
//            return view('hrm::labour.salary-payment.index',compact('employee','salary_pay'));
            return view('hrm::labour.salary-payment.index',compact('labour','salary_pay'));
        }else{
            return $this->access_blocked();
        }
    }

    public function create(Request $request){
        if(permission('labour-salary-payment-add')){
            $setTitle = __('file.Salary Payment');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $labour_id      = $request->labour_id;
            $payment_month  = $request->payment_month;
            $checker        = LabourSalaryPayment::where('payment_month',$request->payment_month)->exists();
            if ($labour_id){
                if ($checker){
                    $salarys    = LabourSalaryPayment::where('labour_id',$labour_id)->where('payment_month',$payment_month)->get();
                    $total_days  = date('t', strtotime($payment_month));
                    return view('hrm::labour.salary-payment.view',compact('salarys','payment_month','total_days'));
                }else{

                    $salary     = Labour::where('status',1)->where('joining_month','<=',$payment_month)->get();
                }
            }else{
                if ($checker){
                    $salarys        = LabourSalaryPayment::where('payment_month',$payment_month)->get();
                    $total_days     = date('t', strtotime($payment_month));
                    return view('hrm::labour.salary-payment.view',compact('salarys','payment_month','total_days'));
                }else{
                   $total_days  = date('t', strtotime($payment_month));
                    $salary     = Labour::where('status',1)->where('joining_month','<=',$payment_month)->get();
                    $bonuses    = Bonus::select('id','bonus_percentage')->where('type',2)->where('bonus_month',$payment_month)->get();
                    $bonus      = $bonuses->SUM('bonus_percentage');

                }
            }
            return view('hrm::labour.salary-payment.create',compact('payment_month','labour_id','salary','total_days','bonus'));
        }else{
            return $this->access_blocked();
        }
    }

    public function store_or_update_data(LabourSalaryPaymentFormRequest $request) {
        if($request->ajax()){
            $user_id = Auth::user()->id;
            if(permission('salary-payment-add')){
                $collection     = collect($request->all());
                foreach ($collection['labour_id'] as $key => $val) {
                    $employees = new LabourSalaryPayment();
                    $employees->payment_type        = $collection['payment_type'];
                    $employees->note                = $collection['note'];
                    $employees->created_by          = $user_id;
                    $employees->basic_salary        = $collection['basic_salary'][$key];
                    $employees->total_working_days  = $collection['total_working_days'][$key] ?? 0;
                    $employees->avg_overtime_hour   = $collection['avg_overtime_hour'][$key] ?? 0;
                    $employees->overtime_amount     = $collection['overtime_amount'][$key] ?? 0;
                    $employees->total_gross_salary  = $collection['total_gross_salary'][$key];
                    $employees->avg_absence_hour    = $collection['avg_absence_hour'][$key] ?? 0;
                    $employees->absence_amount      = $collection['absence_amount'][$key] ?? 0;
                    $employees->salary_advance      = $collection['salary_advance'][$key] ?? 0;
                    $employees->other_deduction     = $collection['other_deduction'][$key] ?? 0;
                    $employees->total_deduction     = $collection['total_deduction'][$key] ?? 0;
                    $employees->payable_salary      = $collection['payable_salary'][$key];
                    $employees->net_payable         = $collection['net_payable'][$key];
                    $employees->bonus               = $collection['bonus'][$key] ?? 0;
                    $employees->payment_month       = $collection['payment_month'][$key];
                    $employees->labour_id           = $val;
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

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('salary-payment-access')){

                if (!empty($request->labour_id)) {
                    $this->model->setName($request->labour_id);
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
                        $action .= ' <a class="dropdown-item" href="'.url("labourSalaryPayment/edit/".$value->id).'">'.$this->actionButton('Edit').'</a>';
                    }
                    if (permission('salary-payment-view')) {
                        $action .= ' <a class="dropdown-item" href="'.url("labourSalaryPayment/salary-payslip/".$value->id).'">'.$this->actionButton('paySlip').'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->labour->name;
                    $row[] = $value->labour->labour_id;
                    $row[] =  date('F-Y', strtotime($value->payment_month));
                    $row[] = '<span class="label label-success label-pill label-inline mt-1" style="padding: 10px 0px;height: 33px;min-width: 100% !important;font-size:12px">'.$value->payable_salary.'/-TK</span>';
                    $row[] = $value->net_payable  + $value->bonus == $value->payable_salary ? '<span class="label label-danger label-pill label-inline" style="padding: 10px 0px;height: 33px;width: 100%;font-size: 14px;">Paid<span>' : '<span class="label label-warning label-pill label-inline" style="padding: 10px 0px;height: 33px;width: 100%;font-size: 14px;">Due :   '.(($value->net_payable + $value->bonus ) - $value->payable_salary).'/-TK<span>';
                    $row[] = $value->note;
                    $row[] = date('Y-m-d', strtotime($value->created_at));
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

    public function payslipView($id){
        if(permission('salary-payment-view')){
            $setTitle = __('file.Pay Slip');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $payslip         = $this->model->find($id);
            $other_allowance = Allowances::where('employee_id',$payslip->labour_id)->where('type',2)->where('status',1)->where('allowances_month',$payslip->payment_month)->get();
            $other_deduction = Deduction::where('employee_id',$payslip->labour_id)->where('type',2)->where('status',1)->where('deduction_month',$payslip->payment_month)->get();
            return view('hrm::labour.salary-payment.payslip',compact('payslip','other_allowance','other_deduction'));
        }else{
            return $this->access_blocked();
        }
    }

    public function edit($id){
        if(permission('salary-payment-edit')){
            $setTitle = __('file.Labour payment Edit');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $salary_pay  = LabourSalaryPayment::find($id);
            return view('hrm::labour.salary-payment.edit',compact('salary_pay'));
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
