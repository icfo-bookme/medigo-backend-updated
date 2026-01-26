<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Customer\Entities\WelcomeCall;

class WelcomCallController extends BaseController
{
    public function __construct(WelcomeCall $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('customer-access')) {
            $this->setPageData('Welcome Calls', 'Welcome Calls', 'fas fa-phone-volume', [['name' => 'Welcome Calls']]);
            return view('customer::welcome-call.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('customer-access')) {
            $fields = [
                'search_text' => 'setSearchText',
                'sort_table' => 'setTableOrder',
                'call_status' => 'setCallStatus',
            ];

            foreach ($fields as $field => $method) {
                if (!empty($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;

                $row = [];
                $row[] = row_checkbox($value->id);//custom helper function to show the table each row checkbox

                $row[] = $no;
                $row[] = $value->customer->name ?? $value->name;
                $row[] = $value->customer->phone ?? $value->phone;
                $row[] = $value->customer->email ?? $value->email;
                $row[] = WELCOME_CALL_STATUS_LABEL[$value->call_status];
                //Approve button
                $row[] = '<button type="button" class="btn btn-sm btn-success" id="approve_call" data-id="' . $value->id . '" data-name="' . $value->name . '" ><i class="far fa-check-circle"></i> Approve</button>';
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(), $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() && permission('customer-edit')) {
            $result = $this->model->find($request->id)->update(['call_status' => '2']);
            $output = $result ? ['status' => 'success', 'message' => 'Call Has Been Approved'] : ['status' => 'error', 'message' => 'Error Approving Call'];
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
