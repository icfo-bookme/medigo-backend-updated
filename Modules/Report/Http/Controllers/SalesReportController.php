<?php

namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\SalesReport;

class SalesReportController extends BaseController
{
    public function __construct(SalesReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('sales-report-access')) {
            $this->setPageData('Sales Report', 'Sales Report', 'fas fa-file', [['name' => 'Sales Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('report::sales-report.all_sales', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function facebookSalesReport()
    {
        if (permission('sales-report-access')) {
            $this->setPageData('Facebook Sales Report', 'Facebook Sales Report', 'fas fa-file', [['name' => 'Facebook Sales Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('report::sales-report.facebook_sales_report', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function whatsappSalesReport()
    {
        if (permission('sales-report-access')) {
            $this->setPageData('WhatsApp Sales Report', 'WhatsApp Sales Report', 'fas fa-file', [['name' => 'WhatsApp Sales Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('report::sales-report.whatsapp_sales_report', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function callSalesReport()
    {
        if (permission('sales-report-access')) {
            $this->setPageData('Call Sales Report', 'Call Sales Report', 'fas fa-file', [['name' => 'Call Sales Report']]);
            $data = [
                'warehouses' => Warehouse::allWarehouses()
            ];
            return view('report::sales-report.call_sales_report', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('sales-report-access')) {
                $fields = [
                    'invoice_no' => 'setInvoiceNo',
                    'start_date' => 'setStartDate',
                    'end_date' => 'setEndDate',
                    'warehouse_id' => 'setWarehouseID',
                    'sort_table' => 'setTableOrder',
                    'order_source' => 'setOrderSource',
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
                    $row[] = $value->invoice_no;
                    $row[] = $value->customer ? ($value->customer->name . ' - ' . $value->customer->mobile) : '';
                    $row[] = $value->item . ' (' . $value->total_qty . ')';
                    $row[] = number_format($value->order_tax, 2);
                    $row[] = number_format($value->order_discount, 2);
                    $row[] = number_format($value->shipping_cost, 2);
                    $row[] = number_format($value->net_total, 2);
                    $row[] = number_format($value->grand_total, 2);
                    $row[] = number_format($value->paid_amount, 2);
                    $row[] = date(config('settings.date_format'), strtotime($value->sale_date));
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
