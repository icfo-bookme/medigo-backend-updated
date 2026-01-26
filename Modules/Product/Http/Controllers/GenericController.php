<?php

namespace Modules\Product\Http\Controllers;

use App\Http\Controllers\BaseController;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Product\Entities\Generic;
use Modules\Product\Entities\GenericDetails;
use Modules\Product\Http\Requests\GenericFormRequest;

class GenericController extends BaseController
{
    public function __construct(Generic $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('generic-access')){
            $this->setPageData('Generic','Generic','fas fa-th-list',[['name' => 'Generic']]);
            return view('product::generic.index');
        }else{
            return $this->access_blocked();
        }
    }
    public function genericCreate()
    {
        if(permission('generic-access')){
            $this->setPageData('Generic Add','Generic Add','fas fa-th-list',[['name' => 'Generic Add']]);
            return view('product::generic.create');
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if(permission('generic-access')){
                if (!empty($request->generic_name)) {
                    $this->model->setName($request->generic_name);
                }
                if (!empty($request->sort_table)) {
                    $this->model->setTableOrder($request->sort_table);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list   = $this->model->getDatatableList();//get table data
                $data   = [];
                $no     = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if(permission('generic-edit')){
                        $action .= ' <a class="dropdown-item" href="'.route("generic.edit",$value->id).'">'.self::ACTION_BUTTON['Edit'].'</a>';
                    }

                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->generic_name;
                    $row[]  = $value->slug;
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


    public function store_or_update_data(GenericFormRequest $request)
    {
        if($request->ajax()){
            if(permission('generic-add')){
                $slugs        = Str::slug($request->generic_name.Str::random(10), '-');
                $collection   = collect($request->all());
                $collection   = $this->track_data($collection,$request->update_id);
                $result       = $this->model->updateOrCreate(['id'=>$request->update_id, 'slug' => $slugs],$collection->all());

                foreach ($collection['title'] as $key => $val) {
                    $p_unit              = new GenericDetails();
                    $p_unit->generic_id  = $result->id;
                    $p_unit->title       = $collection['title'][$key];
                    $p_unit->description = $collection['description'][$key];
                    $p_unit->save();
                }
                $output       = $this->store_message($result, $request->update_id);
            }else{
                $output       = $this->unauthorized();
            }
            return response()->json($output);
        }else{
            return response()->json($this->unauthorized());
        }
    }


    public function edit(int $id){
        if(permission('generic-edit')){
            $this->setPageData('Edit Generic','Edit Generic','fas fa-edit',[['name'=>'Generic','link'=> route('product')],['name' => 'Edit Generic']]);
            $data = [
                'generic'            => Generic::find($id),
                'generic_description'=> GenericDetails::where('generic_id',$id)->get()
            ];
            return view('product::generic.edit',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function update_data(Request $request){
        if($request->ajax()){
            if(permission('generic-add')){
                $slugs        = Str::slug($request->generic_name.Str::random(10), '-');
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

    public function details_update_data(Request $request){
        if(permission('generic-edit')){
            DB::beginTransaction();
            try{
                if ($request->id){
                $collection  = collect($request->all())->except('_token');
                $checklists  = GenericDetails::where('id',$request->id);
                $checklists->update($collection->all());
                $outputs     = $this->store_message($checklists);
                }else{
                    $collection   = collect($request->all());
                    $collection   = $this->track_data($collection,$request->id);
                    $result       = GenericDetails::updateOrCreate(['id'=>$request->id],$collection->all());
                    $output       = $this->store_message($result, $request->id);
                }
                DB::commit();
            }catch(Exception $e){
                DB::rollBack();
                $outputs = ['status' => 'error','message' => $e->getMessage()];
            }
        }else{
            $outputs = $this->unauthorized();
        }
        return redirect()->back();
    }

    public function delete(Request $request)
    {
        if($request->ajax()){
            if(permission('brand-delete')){
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
