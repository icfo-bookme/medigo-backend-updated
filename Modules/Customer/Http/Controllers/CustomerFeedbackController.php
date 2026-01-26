<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Customer\Entities\CustomerFeedback;

class CustomerFeedbackController extends BaseController
{

    public function __construct(CustomerFeedback $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if (permission('customer-access')) {
            $this->setPageData('Customer Feedback', 'Customer Feedback', 'far fa-comment-dots', [['name' => 'Customer Feedback']]);
            return view('customer::customer-feedback.index');
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

                $row[] = $no;
                $row[] = $value->customer->name ?? $value->name;
                $row[] = $value->customer->phone ?? $value->phone;
                $row[] = $value->customer->email ?? $value->email;
                $row[] = CUSTOMER_FEEDBACK_TYPE_LABEL[$value->type];
                $row[] = $value->feedback;
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(), $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

}
