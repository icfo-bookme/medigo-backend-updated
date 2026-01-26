<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HRM\Entities\Salary;
use Modules\HRM\Entities\SalaryPayment;

class PaySlipController extends BaseController
{
    public function __construct(SalaryPayment $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('salary-payment-access')){
            $setTitle = __('file.GENERATE PAYSLIP');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $employee   = Salary::select('id','employee_id')->get();
            $salary_pay = SalaryPayment::select('id','payment_month')->groupBy('payment_month')->get();
            return view('hrm::report.payslip.index',compact('deletable','employee','salary_pay'));
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
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee ? $value->employee->name : '';
                    $row[] = $value->employee ? $value->employee->employee_id : '';
                    $row[] =  date('F-Y', strtotime($value->payment_month));
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-info label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->basic_salary.'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-primary label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->total_allowance.'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-success label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->gross_salary.'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-danger label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.($value->total_deduction - $value->provident_fund).'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-warning label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->provident_fund.'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-info label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->bonus.'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-primary label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->net_salary.'/-TK</span>'.'</a>';
                    $row[] = '<a class="dropdown-item employee-edit" href="'.url("salaryPayment/salary-payslip/".$value->id).'" title="Click And View Payslip Details" target="_blank">'.'<span class="label label-success label-pill label-inline mt-1" style="padding: 10px 0px;height: 30px;min-width: 100% !important;font-size:12px">'.$value->payment_amount.'/-TK</span>'.'</a>';
                    $row[] = $value->net_salary == $value->payment_amount ? '<span class="label label-danger label-pill label-inline" style="padding: 10px 0px;height: 30px;width: 100%;font-size: 14px;">Paid<span>' : '<span class="label label-warning label-pill label-inline" style="padding: 10px 0px;height: 30px;width: 100%;font-size: 10px;">Due :'.($value->net_salary - $value->payment_amount).'/-TK<span>'.'</a>';
                    $row[] = $value->note;
                    $row[] = date('Y-m-d', strtotime($value->created_at)) ;
                    $row[] = $value->user ? $value->user->name : '';
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }


}
