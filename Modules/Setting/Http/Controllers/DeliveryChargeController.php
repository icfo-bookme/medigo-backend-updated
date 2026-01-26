<?php

namespace Modules\Setting\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Setting\Entities\DeliveryCharge;

class DeliveryChargeController extends BaseController
{
    public function __construct(DeliveryCharge $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('showroom-access')) {
            $this->setPageData('Delivery Charge', 'Delivery Charge', 'fas fa-truck', [['name' => 'Delivery Charge']]);
            return view('setting::delivery-charge.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('showroom-access')) {
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
                if (permission('showroom-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('showroom-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                $row = [];
                $row[] = $no;
                $row[] = $value->name;
                $row[] = $value->value;
                $row[] = permission('showroom-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(Request $request)
    {
        if ($request->ajax() && permission('showroom-add')) {
            $validator = $request->validate([
                'name' => 'required|string',
                'value' => 'nullable',
            ]);
            $collection = collect($validator);
            $collection = $this->track_data($collection, $request->update_id);
            $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
            $output = $this->store_message($result, $request->update_id);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit(Request $request)
    {
        if ($request->ajax() && permission('showroom-edit')) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('showroom-delete')) {
            $result = $this->model->find($request->id)->delete();
            $output = $this->delete_message($result);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() && permission('showroom-edit')) {
            $result = $this->model->find($request->id)->update(['status' => $request->status]);
            $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error', 'message' => 'Failed To Change Status'];
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
