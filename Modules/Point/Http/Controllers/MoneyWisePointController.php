<?php

namespace Modules\Point\Http\Controllers;

use Exception;
use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Point\Entities\MoneyWisePoint;
use Modules\Point\Http\Requests\MoneyWisePointRequest;


class MoneyWisePointController extends BaseController
{
    public function __construct(MoneyWisePoint $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('money-wise-point-access')){
            $this->setPageData('Money Wise Point','Money Wise Point','far fa-handshake',[['name'=>'Money Wise Point']]);
            return view('point::money-wise-point.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('money-wise-point-access')){
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('money-wise-point-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('money-wise-point-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" >'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->money.'tk';
                    $row[]  = $value->point;
                    $row[]  = STATUS_LABEL[$value->status];
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

    public function store_or_update_data(MoneyWisePointRequest $request) {
        if($request->ajax()){
            if(permission('money-wise-point-add') || permission('money-wise-point-edit')){
                DB::beginTransaction();
                try {
                    $collection = collect($request->all());
                    $collection = $this->track_data($collection,$request->update_id);
                    $result     = $this->model->updateOrCreate(['id'=>$request->update_id],$collection->all());
                    $output     = $this->store_message($result, $request->update_id);
                    DB::commit();

                } catch (Exception $e) {
                    DB::rollback();
                    $output = ['status' => 'error','message' => $e->getMessage()];
                }
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
            if(permission('money-wise-point-edit')){
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

    public function delete(Request $request) {
        if($request->ajax()){
            if(permission('money-wise-point-delete')){
                $checklist  = $this->model->find($request->id);
                $result    = $checklist->delete();

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
