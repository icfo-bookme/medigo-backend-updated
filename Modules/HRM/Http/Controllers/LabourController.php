<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Traits\UploadAble;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Branch;
use Modules\HRM\Entities\Labour;
use Modules\HRM\Http\Requests\LabourFormRequest;

class LabourController extends BaseController
{
    use UploadAble;
    public function __construct(Labour $model){
        $this->model = $model;
    }

    public function index(){
        if(permission('labour-access')){
            $setTitle = __('file.Labour');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            return view('hrm::labour.index',compact('deletable'));
        }else{
            return $this->access_blocked();
        }
    }

    public function add(){
        if(permission('labour-add')){
            $setTitle = __('file.labour add');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $branch         = Branch::all();
            return view('hrm::labour.create',compact('branch'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('labour-access')){
                if (!empty($request->employee_id)) {
                    $this->model->setName($request->employee_id);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('labour-edit')){
                        $action .= ' <a class="dropdown-item employee-edit" href="'.url("labour/edit/".$value->id).'">'.$this->actionButton('Edit').'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->labour_id;
                    $row[]  = $value->name;
                    $row[]  = $value->joining_date;
                    $row[]  = $value->contact_no_one;
                    $row[]  = '<span class="label label-success label-pill label-inline mt-1" style="padding: 10px 0px;height: 33px;min-width: 100% !important;font-size:12px">'.$value->basic_salary.'/-TK</span>';
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

    public function store_or_update_data(LabourFormRequest $request) {
        if($request->ajax()){
            $user_id = Auth::user()->id;
            if(permission('labour-add')){
                $joining_month = date('Y-m', strtotime($request->joining_date));

                $resume = !empty($request->resume) ? $request->resume : null;
                if ($request->hasFile('resume')) {
                    $resume  = $this->upload_file($request->file('resume'), EMPLOYEE_IMAGE_PATH);
                    if (!empty($request->resume)) {
                        $this->delete_file($request->resume, EMPLOYEE_IMAGE_PATH);
                    }
                }
                $collection  = collect($request->all())->merge(['created_by' => $user_id,'joining_month' => $joining_month,$resume]);
                $employees   = $this->model->create($collection->all());
                $output      = $this->store_message($employees);
            }else{
                $output      = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function edit($id){
        if(permission('labour-access')){
            $setTitle = __('file.Labour Edit');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $labour         = Labour::find($id);
            $branch         = Branch::all();
            return view('hrm::labour.edit',compact('labour','branch'));
        }else{
            return $this->access_blocked();
        }
    }

    public function update_data(LabourFormRequest $request){
        if($request->ajax()){
            if(permission('labour-edit')){
                $collection  = collect($request->all());
                $salary      = $this->model->find($request->update_id)->update($collection->all());
                $output      = $this->store_message($salary);
            }else{
                $output      = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }

}
