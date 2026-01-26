<?php

namespace Modules\Designation\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Designation\Entities\Designation;
use Modules\Designation\Http\Requests\DesignationFormRequest;

class DesignationController extends BaseController
{
    public function __construct(Designation $model) {
        $this->model = $model;
    }

    public function index(){
        if(permission('designation-access')){
            $setTitle = __('file.Designation');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable = self::DELETABLE;
            return view('designation::designation.index',compact('deletable'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('designation-access')){

                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list   = $this->model->getDatatableList();//get table data
                $data   = [];
                $no     = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('designation-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.$this->actionButton('Edit').'</a>';
                    }

                    if(permission('designation-delete')){
                        if($value->deletable == 2){
                            $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">'.$this->actionButton('Delete').'</a>';
                        }
                    }

                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->name;
                    $row[]  = permission('designation-edit') ? change_status($value->id,$value->status, $value->name) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(DesignationFormRequest $request) {
        if($request->ajax()){
            if(permission('designation-add')){
                $collection   = collect($request->validated());
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
            if(permission('designation-edit')){
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

    public function delete(Request $request) {
        if($request->ajax()){
            if(permission('designation-delete')){
                $result   = $this->model->find($request->id)->delete();
                $output   = $this->delete_message($result);
            }else{
                $output   = $this->unauthorized();

            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
