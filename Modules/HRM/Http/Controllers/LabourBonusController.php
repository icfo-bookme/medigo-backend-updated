<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Bonus;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\LabourBonus;

class LabourBonusController extends BaseController
{
    public function __construct(LabourBonus $model)
    {
        $this->model = $model;
    }
    public function index(){
        if(permission('bonus-access')){
            $setTitle = __('file.Manage Labour Bonuses');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $employee   = Employee::all();
            return view('hrm::labour.bonus.index',compact('deletable','employee'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('bonus-access')){

                if (!empty($request->bonus_name)) {
                    $this->model->setName($request->bonus_name);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('bonus-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->bonus_name;
                    $row[] =  date('F-Y', strtotime($value->bonus_month));
                    $row[] = '<span>'.$value->bonus_percentage.'%</span>';
                    $row[] = $value->bonus_description;
                    $row[]  = permission('bonus-edit') ? change_status($value->id,$value->status, $value->bonus_name) : STATUS_LABEL[$value->status];
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
    public function store_or_update_data(Request $request) {
        if($request->ajax()){
            if(permission('bonus-access')){
                $user = Auth::user()->id;
                $collection   = collect($request->all())->merge(['created_by' => $user,'type'=>2]);
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
            if(permission('bonus-edit')){
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
