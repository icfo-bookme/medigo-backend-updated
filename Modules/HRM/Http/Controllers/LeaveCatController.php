<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\HRM\Entities\LeaveCategory;
use Modules\HRM\Http\Requests\LeaveCatFormRequest;

class LeaveCatController extends BaseController
{
    public function __construct(LeaveCategory $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('leave-access')) {
            $setTitle = __('file.Leave Category');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            return view('hrm::leave-category.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('leave-access')) {
                if (!empty($request->name)) {
                    $this->model->setName($request->name);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('leave-edit')) {
                        $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . $this->actionButton('Edit') . '</a>';
                    }
                    if (permission('leave-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->leave_category . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->leave_category;
                    $row[] = $value->leave_category_description;
                    $row[] = permission('leave-edit') ? change_status($value->id, $value->status, $value->leave_category) : STATUS_LABEL[$value->status];
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

    public function store_or_update_data(LeaveCatFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('leave-add')) {
                $collection = collect($request->validated())->merge(['created_by' => Auth::user()->id]);
                $collection = $this->track_data($collection, $request->update_id);
                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                $output = $this->store_message($result, $request->update_id);
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
            if (permission('leave-edit')) {
                $data = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax()) {
            if (permission('leave-delete')) {
                $result = $this->model->find($request->id);
                $result->update([
                    'status' => '2',
                    'deleted_by' => auth()->user()->username
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

}
