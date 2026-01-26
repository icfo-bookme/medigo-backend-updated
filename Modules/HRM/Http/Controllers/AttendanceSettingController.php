<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
use Modules\HRM\Entities\AttendanceSetting;
use Modules\HRM\Http\Requests\AttTimeFormRequest;

class AttendanceSettingController extends BaseController
{
    public function __construct(AttendanceSetting $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('attendance-access')){
            $setTitle = __('Attendance Time Add');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            return view('hrm::attendance_setting.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function store_or_update_data(AttTimeFormRequest $request) {
        if($request->ajax()){
            if(permission('attendance-add')){
                $collection   = collect($request->all());
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

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('attendance-access')){

//                if (!empty($request->name)) {
//                    $this->model->setName($request->name);
//                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $in     = Carbon::createFromFormat('H:i:s',$value->start_time);
                    $out    =  Carbon::createFromFormat('H:i:s',$value->end_time);
                    $r      = $in->diffAsCarbonInterval($out)->hours;
                    $no++;
                    $action = '';
                    if(permission('attendance-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->shift;
                    $row[] = $value->start_time;
                    $row[] = $value->end_time;
                    $row[] = $r;
                    $row[] = permission('attendance-edit') ? change_status($value->id,$value->status, $value->shift) : STATUS_LABEL[$value->status];
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

    public function edit(Request $request) {
        if($request->ajax()){
            if(permission('attendance-edit')){
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
