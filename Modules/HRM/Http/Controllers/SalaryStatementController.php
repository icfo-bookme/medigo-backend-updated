<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Salary;
use Modules\HRM\Entities\SalaryPayment;

class SalaryStatementController extends BaseController
{
    public function index(){
        if(permission('salary-statement-access')){
            $setTitle = __('file.Salary Statement');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $employee   = Salary::select('id','employee_id')->get();
            $salary_pay = SalaryPayment::select('id','payment_month')->groupBy('payment_month')->get();
            return view('hrm::salary-statement.index',compact('employee','salary_pay'));
        }else{
            return $this->access_blocked();
        }
    }

    public function salaryStatement(Request $request){
        if(permission('salary-statement-report')){
            $setTitle = __('file.Salary Statement');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $salary_pay     = SalaryPayment::select('id','payment_month')->groupBy('payment_month')->get();
            $payment_month  = $request->payment_month;

            if ($payment_month){
                $salarys    = SalaryPayment::where('payment_month',$payment_month)->get();
            }
            return view('hrm::salary-statement.index',compact('salary_pay','salarys','payment_month'));
        }else{
            return $this->access_blocked();
        }
    }

}
