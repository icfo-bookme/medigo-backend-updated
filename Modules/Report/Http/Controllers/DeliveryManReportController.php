<?php

namespace Modules\Report\Http\Controllers;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Modules\Report\Entities\DeliveryManReport;

class DeliveryManReportController extends BaseController
{
    public function __construct(DeliveryManReport $model)
    {
        $this->model = $model;
    }
    public function index()
    {
        if(permission('sales-report-access')){
            $this->setPageData('DeliveryMan Report','DeliveryMan Report','fas fa-file',[['name' => 'DeliveryMan Report']]);
            return view('report::delivery-man-report.index');
        }else{
            return $this->access_blocked();
        }

    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('sales-report-access')) {
            $fields = [
                'invoice_no' => 'setInvoiceNo',
                'delivery_man_id' => 'setDelivery_man_id',
                'sort_table' => 'setTableOrder',
            ];

            foreach ($fields as $field => $method) {
                if (isset($request->$field)) {
                    $this->model->$method($request->$field);
                }
            }
            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data
            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $date = $value->date ? Carbon::parse($value->date) : null;

                $no++;
                $row = [];
                $row[] = $no;
                $row[] = $date ? $date->format('Y-m-d') : 'N/A';
                $row[] = $value->name ?? 'N/A';
                $row[] = $value->delivered_count ?? 0;
                $row[] = $value->cancel_count ?? 0;
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
