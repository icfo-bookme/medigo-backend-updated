<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Salary;
use Modules\HRM\Entities\SalaryPayment;

class ProvidentFundController extends BaseController
{
    public function __construct(Salary $model) {
        $this->model = $model;
    }

    public function index(){
        if(permission('salary-access')){
            $setTitle = __('file.Manage Salary');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $salary_emp = Salary::get();
            $employee   = Employee::where('activation_status',1)->get();
            return view('hrm::provident-fund.index',compact('deletable','employee','salary_emp'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('salary-access')){
                if (!empty($request->employee_id)) {
                    $this->model->setName($request->employee_id);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $p_totals = SalaryPayment::where('employee_id',$value->employee_id)->get();
                    $p_total  = $p_totals->SUM('provident_fund');

                    $no++;
                    $action = '';
                    if(permission('salary-view')){
                        $action .= ' <a class="dropdown-item" href="'.url("providentFund/provident-fund/".$value->employee_id).'">'.$this->actionButton('View').'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee ? $value->employee->name : '';
                    $row[] = $value->employee ? $value->employee->employee_id : '';
                    $row[] = $value->employee ? $value->employee->designation->name : '';
                    $row[] = EMPLOYEE_TYPE_LABEL[$value->employee_type];
                    $row[] = $value->provident_fund_contribution;
                    $row[] = $value->provident_fund_deduction;
                    $row[] = $value->provident_fund_contribution + $value->provident_fund_deduction + $p_total;
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

    public function view($id){
        if(permission('salary-payment-view')){
            $setTitle = __('file.Provident Fund');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $employee         = $this->model->where('employee_id',$id)->first();
            $all_fund       = SalaryPayment::where('employee_id',$employee->employee_id)->get();
            return view('hrm::provident-fund.view',compact('employee','all_fund'));
        }else{
            return $this->access_blocked();
        }
    }

}
