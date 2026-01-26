<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Deduction;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Labour;
use Modules\HRM\Entities\LabourDeduction;
use Modules\HRM\Http\Requests\DeductionFormRequest;

class LabourDeductionController extends BaseController
{
    public function __construct(LabourDeduction $model)
    {
        $this->model = $model;
    }
    public function index(){
        if(permission('deduction-access')){
            $setTitle = __('file.Manage Labour Deduction');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $employee   = Labour::all();
            return view('hrm::labour.deduction.index',compact('deletable','employee'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('deduction-access')){

                if (!empty($request->deduction_name)) {
                    $this->model->setName($request->deduction_name);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('deduction-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->deduction_name;
                    $row[]  = $value->labour ? $value->labour->name : '';
                    $row[]  =  date('F-Y', strtotime($value->deduction_month));
                    $row[]  = $value->deduction_amount;
                    $row[]  = $value->deduction_description;
                    $row[]  = permission('deduction-edit') ? change_status($value->id,$value->status, $value->deduction_name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(DeductionFormRequest $request) {
        if($request->ajax()){
            if(permission('deduction-add')){
                $collection   = collect($request->all())->merge(['created_by' => Auth::user()->id,'type' => 2]);
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                $output       = $this->store_message($result, $request->update_id);
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
            if(permission('deduction-edit')){
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
