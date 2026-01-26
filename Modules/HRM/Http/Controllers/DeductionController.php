<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\HRM\Entities\Deduction;
use Modules\HRM\Http\Requests\DeductionFormRequest;

class DeductionController extends BaseController
{
    public function __construct(Deduction $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('deduction-access')) {
            $this->setPageData('Manage Deduction', 'Manage Deduction', 'fab fa-opencart', [['name' => 'Manage Deduction']]);

            return view('hrm::deduction.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('deduction-access')) {
                if (!empty($request->head)) {
                    $this->model->setHead($request->head);
                }
                if (!empty($request->department)) {
                    $this->model->setDepartment($request->department);
                }

                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('deduction-edit')) {
                        $action .= ' <a class="dropdown-item edit-data" data-id="' . $value->id . '" data-name="' . $value->head . '" data-department="' . $value->department . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    }
                    if (permission('deduction-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->head . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }

                    $row = [];
                    $row[] = $no;
                    $row[] = $value->head;
                    $row[] = ALLOWANCE_DEPARTMENT_LABEL[$value->department];
                    $row[] = $value->created_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = $value->modified_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function storeOrUpdate(DeductionFormRequest $request)
    {
        if($request->ajax()){
            if(permission('deduction-add') || permission('deduction-edit')){
                $collection   = collect($request->validated());
                $collection   = $collection->merge(with(['type' => '2', 'status' => '1']));
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

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('deduction-delete')) {
                $result = $this->model->find($request->id);
                $result->update([
                    'status' => '2'
                ]);
                $output = $this->delete_message($result);
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax()) {
            if (permission('deduction-edit')) {
                $output = $this->model->find($request->id);
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
