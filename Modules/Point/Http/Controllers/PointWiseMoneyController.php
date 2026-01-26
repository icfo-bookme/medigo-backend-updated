<?php

namespace Modules\Point\Http\Controllers;

use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Point\Entities\PointWiseMoney;
use Modules\Point\Http\Requests\MoneyWisePointRequest;

class PointWiseMoneyController extends BaseController{
    public function __construct(PointWiseMoney $model){
        $this->model = $model;
    }
    public function index(){
        if(permission('point-wise-money-access')){
            $this->setPageData('Point Wise Money','Point Wise Money','far fa-handshake',[['name'=>'Point Wise Money']]);
            return view('point::point-wise-money.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('point-wise-money-access')){
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('point-wise-money-edit')){
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }
                    if(permission('point-wise-money-delete')){
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" >'.self::ACTION_BUTTON['Delete'].'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->point;
                    $row[]  = $value->money.'tk';
                    $row[]  = STATUS_LABEL[$value->status];;
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
            if(permission('point-wise-money-add') || permission('point-wise-money-edit')){
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
            if(permission('point-wise-money-edit')){
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
            if(permission('point-wise-money-delete')){
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
