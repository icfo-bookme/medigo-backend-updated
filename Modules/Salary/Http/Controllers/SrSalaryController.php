<?php

namespace Modules\Salary\Http\Controllers;

use Exception;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Salary\Entities\Salary;
use Modules\Salary\Entities\SrSalary;
use Modules\SalesMen\Entities\Salesmen;
use Modules\SalesMen\Entities\SalesmenMonthlyTarget;
use Modules\SalesMen\Entities\SalesmenProductTarget;

class SrSalaryController extends BaseController {
    public function __construct(SrSalary $model){
        $this->model = $model;
    }
    public function index(){
        if (permission('sr-salary-access')){
            $this->setPageData('SR Salary','SR Salary','fas fa-marker',[['name' => 'SR Salary']]);
            $salesmen = Salesmen::all();
            return view('salary::srSalary.index',compact('salesmen'));
        }else{
            return $this->access_blocked();
        }
    }
    public function getDataTableData(Request $request){
        if($request->ajax() && permission('sr-salary-access')){
            if (!empty($request->salarytable_id)) {
                $this->model->setSrID($request->salarytable_id);
            }
            $this->set_datatable_default_properties($request);
            $list = $this->model->getDatatableList();
            $data = [];
            $no   = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $row     = [];
                $row[]   = $no;
                $row[]   = $value->username;
                $row[]   = $value->name;
                $row[]   = $value->month;
                $row[]   = $value->date;
                $row[]   = $value->salary;
                $row[]   = $value->monthly_target_achieve;
                $row[]   = $value->product_target_achieve;
                $row[]   = $value->ta_td;
                $row[]   = $value->deduction;
                $row[]   = $value->claim;
                $row[]   = STATUS_LABEL[$value->status];
                $data[]  = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(), $this->model->count_filtered(), $data);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function create(){
        if (permission('sr-salary-add')){
            $this->setPageData('SR Salary Add','SR Salary Add','fas fa-marker',[['name' => 'SR Salary Add']]);
            return view('salary::srSalary.create');
        }else{
            return $this->access_blocked();
        }
    }
    public function store(Request $request){
        if ($request->ajax() && permission('sr-salary-add')){
            DB::beginTransaction();
            try{
                if($request->has('salary')){
                    foreach($request->salary as $value){
                        $sr = Salary::where(['month' => $request->month,'salarytable_id' => $value['id'],'type' => 'sr'])->first();
                        if(!empty($value['id']) && !empty($value['salary']) && !empty($value['claim']) && empty($sr)){
                            $srSalary = SrSalary::find($value['id']);
                            $srSalary->srSalary()->create([
                              'month'                  => $request->month,
                              'date'                   => $request->date,
                              'salary'                 => $value['salary'],
                              'monthly_target_achieve' => $value['monthly_target_achieve'],
                              'product_target_achieve' => $value['product_target_achieve'],
                              'ta_td'                  => $value['ta_td'],
                              'deduction'              => $value['deduction'],
                              'claim'                  => $value['claim'],
                              'status'                 => 1,
                              'type'                   => 'sr'
                            ]);
                        }
                    }
                }

                $output = ['status' => 'success' , 'message' => 'Salary Store Successfully'];
                DB::commit();
            }catch (Exception $e){
                DB::rollBack();
                $output = ['status' => 'error' , 'message' => $e->getMessage()];
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
    public function monthWiseSalaryGenerate($month){
        $table = '';
        $table .= '<table class="table table-bordered">
                       <thead class="bg-primary">
                           <tr class="text-center">
                               <th width="10%">UserName</th>
                               <th width="8%">Salary</th>
                               <th width="10%">Target</th>
                               <th width="10%">Achieve</th>
                               <th width="10%">Commission</th>
                               <th width="25%">Product</th>
                               <th width="8%">TA/TD</th>
                               <th width="8%">deduction</th>
                               <th width="8%">Claim</th>
                               <th width="3%"></th>
                           </tr>
                       </thead><tbody>';
                       foreach (Salesmen::all() as $key => $value){
                           $monthlyTarget     = SalesmenMonthlyTarget::where([['salesmen_id','=',$value->id],['start_date','like','%'.$month.'%'],['end_date','like','%'.$month.'%']])->orderBy('id','DESC')->first();
                           if($monthlyTarget != null){
                              $targetValue    = $monthlyTarget->target_value;
                              $commission     = $monthlyTarget->commission;
                              $commissionRate = $monthlyTarget->commission_rate;
                              $achieveValue   = $monthlyTarget->achieved_value != null ? $monthlyTarget->achieved_value : 0;
                           }else{
                              $targetValue    = 0;
                              $commission     = 0;
                              $commissionRate = 0;
                              $achieveValue   = 0;
                           }
                           $table .= '<tr class="text-center">
                                        <td><input type="hidden" name = "salary['. $key .'][id]" value = "'. $value->id.'"/>'.$value->username.'</td>
                                        <td><input type="text" class = "form-control bg-primary text-white" id = "salary_'. $key .'_salary" name = "salary['. $key .'][salary]" value = "'. $value->salary.'" readonly/></td>
                                        <td>'.number_format($targetValue,2) .'</td>
                                        <td>'.number_format($achieveValue,2).'</td>
                                        <td>'.$commission.'%'.'<br/>'.number_format($commissionRate,2).'<br/><input type = "text" class = "form-control monthlyTargetAchieve" id = "salary_'. $key .'_monthly_target_achieve" name = "salary['. $key .'][monthly_target_achieve]" data-salary = "salary_'. $key .'_salary" data-product_target_achieve = "salary_'. $key .'_product_target_achieve" data-claim = "salary_'. $key .'_claim"/></td>
                                        <td>
                                        <table class="table table-bordered">
                                        <thead class="bg-primary">
                                            <tr class="text-center">
                                                <th>Name</th>
                                                <th>Qty</th>
                                                <th>Sale</th>
                                                <th>Rate</th>
                                            </tr>
                                        </thead><tbdoy>';
                           foreach (SalesmenProductTarget::with('commission')->where([['salesmen_id','=',$value->id],['start_date','like','%'.$month.'%'],['end_date','like','%'.$month.'%']])->get() as $item){
                           $table .= '<tr class="text-center">
                                        <td>'.$item->commission->name.'</td>
                                        <td>'.$item->commission->quantity.'</td>
                                        <td>'.$item->pcs.'</td>
                                        <td>'.number_format($item->commission->commission_amount,2).'</td>
                                      </tr>';
                           }
                           $table .= '<tr class="text-center">
                                        <td colspan="4"><input type="text" class="form-control productTargetAchieve" id = "salary_'. $key .'_product_target_achieve" name="salary['. $key .'][product_target_achieve]" data-salary = "salary_'. $key .'_salary" data-monthly_target_achieve ="salary_'. $key .'_monthly_target_achieve" data-claim = "salary_'. $key .'_claim"/></td>
                                      </tr>';
                           $table .= '</tbody></table></td>';
                           $table .= '<td><input type="text" class="form-control ta_td" id = "salary_'. $key .'_ta_td" name = "salary['. $key .'][ta_td]" data-salary = "salary_'. $key .'_salary" data-monthly_target_achieve ="salary_'. $key .'_monthly_target_achieve" data-product_target_achieve = "salary_'. $key .'_product_target_achieve" data-deduction = "salary_'. $key .'_deduction" data-claim = "salary_'. $key .'_claim"/></td>';
                           $table .= '<td><input type="text" class="form-control deduction" id = "salary_'. $key .'_deduction" name = "salary['. $key .'][deduction]" data-salary = "salary_'. $key .'_salary" data-monthly_target_achieve ="salary_'. $key .'_monthly_target_achieve" data-product_target_achieve = "salary_'. $key .'_product_target_achieve" data-claim = "salary_'. $key .'_claim"/></td>';
                           $table .= '<td><input type="text" class="form-control bg-primary text-white" id = "salary_'. $key .'_claim" name = "salary['. $key .'][claim]" value = "'.$value->salary.'" readonly/></td>';
                           $table .= '<td><button type = "button" class = "btn btn-danger btn-sm deleteRaw"><i class = "fas fa-minus-circle"></i></button></td></td>';
                       }
        $table .= '</tr></tbody></table>';
        $table .= '<div class="form-group col-md-12 text-center pt-5"><button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="srSalaryStore()"><i class="fas fa-save"></i>Store</button></div>';
        return $table;
    }
}
