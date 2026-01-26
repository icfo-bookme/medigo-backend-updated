<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Labour;
use Modules\HRM\Entities\LabourLeaveApp;
use Modules\HRM\Entities\LeaveCategory;
use Modules\HRM\Http\Requests\LabourLeaveFormRequest;

class LabourLeaveAppController extends BaseController
{
    public function __construct(LabourLeaveApp $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('labour-leave-access')){
            $setTitle = __('file.Labour Leave Application');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $labour     = Labour::where('status',1)->get();
            $l_cat      = LeaveCategory::where('status',1)->get();

            return view('hrm::labour_leave.index',compact('deletable','labour','l_cat'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('labour-leave-access')){
                if (!empty($request->labour_id)) {
                    $this->model->setName($request->labour_id);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('labour-leave-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->labour->name;
                    $row[] = $value->leaveCategory->leave_category;
                    $row[] = $value->start_date;
                    $row[] = $value->end_date;
                    $row[] = $value->purpose;
                    $row[] = $value->date;
                    $row[] = permission('manage-leave-cat-edit') ? change_status($value->id,$value->status, $value->leave_category) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(LabourLeaveFormRequest $request) {
        if($request->ajax()){
            if(permission('labour-leave-add')){
                $collection   = collect($request->validated())->merge(['created_by' => Auth::user()->id,'date' => date('Y-m-d')]);
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
            if(permission('manage-leave-cat-edit')){
                $data   = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            }else{
                $output = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

}
