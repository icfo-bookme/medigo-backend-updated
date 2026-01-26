<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Allowances;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Labour;
use Modules\HRM\Entities\LabourAllowances;
use Modules\HRM\Http\Requests\AllowanceFormRequest;

class LabourAllowancesController extends BaseController
{
    public function __construct(LabourAllowances $model)
    {
        $this->model = $model;
    }
    public function index(){
        if(permission('allowances-access')){
            $setTitle = __('file.Manage Labour Allowances');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $employee   = Labour::all();
            return view('hrm::labour.allowances.index',compact('deletable','employee'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('allowances-access')){

                if (!empty($request->allowances_name)) {
                    $this->model->setName($request->allowances_name);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('allowances-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->allowances_name;
                    $row[]  = $value->labour->name;
                    $row[]  =  date('F-Y', strtotime($value->allowances_month));
                    $row[]  = $value->allowances_amount;
                    $row[]  = $value->allowances_description;
                    $row[]  = permission('allowances-edit') ? change_status($value->id,$value->status, $value->allowances_name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(AllowanceFormRequest $request) {
        if($request->ajax()){
            if(permission('allowances-add')){
                $user = Auth::user()->id;
                $collection   = collect($request->all())->merge(['created_by' => $user, 'type' => 2]);
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
            if(permission('allowances-edit')){
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
