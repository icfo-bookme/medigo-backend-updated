<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Increment;

class IncrementController extends BaseController
{
    public function __construct(Increment $model)
    {
        $this->model = $model;
    }

    public function index(){
        if(permission('increment-access')){
            $setTitle = __('file.Manage Increment');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable  = self::DELETABLE;
            $employee   = Employee::all();
            return view('hrm::increment.index',compact('deletable','employee'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('increment-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
//                return response()->json($list);
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('increment-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }
//                    if(permission('increment-delete')){
//                        if($value->deletion_status == 2){
//                            $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->leave_category . '">'.$this->actionButton('Delete').'</a>';
//                        }
//                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee ? $value->employee->name : '';
                    $row[] = $value->amount;
                    $row[] = $value->date;
                    $row[] = $value->incr_purpose;
                    $row[] = $value->created_by;
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
            if(permission('increment-access')){
                $collection   = collect($request->all())->merge(['created_by' => Auth::user()->id]);
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
}
