<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Modules\HRM\Entities\Holiday;
use Illuminate\Http\Request;
use Modules\HRM\Http\Requests\HolidayFormRequest;

class HolidayController extends BaseController
{
    public function __construct(Holiday $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('holiday-access')) {
            $this->setPageData('Manage Holiday', 'Manage Holiday', 'fab fa-opencart', [['name' => 'Manage Holiday']]);

            return view('hrm::holiday.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('holiday-access')) {
                if (!empty($request->title)) {
                    $this->model->setTitle($request->title);
                }
                if (!empty($request->start_date)) {
                    $this->model->setFromDate($request->start_date);
                }
                if (!empty($request->end_date)) {
                    $this->model->setToDate($request->end_date);
                }
                if (!empty($request->type)) {
                    $this->model->setType($request->type);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('holiday-view')) {
                        $action .= ' <a class="dropdown-item view-data" data-id="' . $value->id . '" data-name="' . $value->title . '">' . self::ACTION_BUTTON['View'] . '</a>';
                    }
                    if (permission('holiday-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->title . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }
                    $start_date = new \DateTime($value->start_date);
                    $end_date = new \DateTime($value->end_date);
                    $interval = $start_date->diff($end_date);
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->title;
                    $row[] = TYPE_STATUS_LABEL[$value->type];
                    $row[] = $value->description;
                    $row[] = 'From ' . date("d-m-Y", strtotime($value->start_date)) . ' To ' . date("d-m-Y", strtotime($value->end_date));
                    $row[] = $interval->days + 1;
                    $row[] = $value->created_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = $value->created_at ? date(config('settings.date_format'), strtotime($value->created_at)) : '';
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

    public function view(Request $request)
    {
        if ($request->ajax()) {
            if (permission('holiday-view')) {
                $data = $this->model->findOrFail($request->id);
                return view('hrm::holiday.view-modal-data', compact('data'))->render();
            }
        }
    }

    public function store(HolidayFormRequest $request)
    {
        if ($request->ajax()) {
            if (permission('holiday-add')) {
                $collection = collect($request->validated());
                $collection = $collection->merge(with(['created_by' => auth()->user()->username]));
                $result = $this->model->create($collection->all());
                $output = $this->store_message($result, $request->update_id);
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
            if (permission('holiday-delete')) {
                $result = $this->model->find($request->id)->delete();
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
