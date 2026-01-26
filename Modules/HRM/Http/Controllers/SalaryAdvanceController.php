<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Advance;
use Modules\HRM\Entities\Salary;
use Modules\HRM\Http\Requests\SalaryAdvanceFormRequest;

class SalaryAdvanceController extends BaseController
{

    public function __construct(Advance $model)
    {
        $this->model = $model;
    }
    public function index(){
        if(permission('salary-advance-access')){
            $setTitle = __('file.Manage Salary Advance');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $employee       = Salary::with('employee')->where('status',1)->get();
            $advance_pay    = Advance::select('id','advance_month')->groupBy('advance_month')->get();
            return view('hrm::salary-advance.index',compact('employee','advance_pay'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('salary-advance-access')){

                if (!empty($request->employee_id)) {
                    $this->model->setName($request->employee_id);
                }
                if (!empty($request->advance_month)) {
                    $this->model->setMonth($request->advance_month);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('salary-advance-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->employee->name;
                    $row[]  = $value->employee->designation->name;
                    $row[]  =  date('F-Y', strtotime($value->advance_month));
                    $row[]  = '<span class="label label-info label-pill label-inline mt-1" style="padding: 10px 0px;min-width: 100% !important;font-size:12px">'.$value->advance_amount.'/-TK</span>';;
                    $row[]  = $value->advance_description;
                    $row[]  = permission('salary-advance-edit') ? change_status($value->id,$value->status, $value->employee->name) : STATUS_LABEL[$value->status];
                    $row[]  = $value->user ? $value->user->name : '';
                    $row[]  = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(SalaryAdvanceFormRequest $request) {
        if($request->ajax()){
            if(permission('salary-advance-add')){
                $user_id = Auth::user()->id;
                $collection   = collect($request->all())->merge(['created_by' => $user_id]);
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output       = $this->store_message($result);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request) {
        if($request->ajax()){
            if(permission('salary-advance-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

}
