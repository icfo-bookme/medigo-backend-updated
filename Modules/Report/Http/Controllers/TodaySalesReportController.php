<?php
namespace Modules\Report\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController;
use Modules\Report\Entities\TodaySalesReport;
use Modules\Setting\Entities\Warehouse;

class TodaySalesReportController extends BaseController
{

    public function __construct(TodaySalesReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if(permission('todays-sales-report-access')){
            $this->setPageData('Today\'s Sales Report','Today\'s Sales Report','fas fa-file',[['name' => 'Today\'s Sales Report']]);
            $data = [
                'warehouses'  => Warehouse::allWarehouses()
            ];
            return view('report::today-sales-report',$data);
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if($request->ajax()){
            if (!empty($request->invoice_no)) {
                $this->model->setInvoiceNo($request->invoice_no);
            }

            if (!empty($request->warehouse_id)) {
                $this->model->setWarehouseID($request->warehouse_id);
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
                if(empty(auth()->user()->warehouse_id)){
                    $row[] = $value->warehouse->name;
                }
                $row[] = $value->item;
                $row[] = $value->total_qty;
                $row[] = number_format($value->total_price,2);
                $row[] = number_format($value->order_tax,2);
                $row[] = number_format($value->order_discount,2);
                $row[] = number_format($value->shipping_cost,2);
                $row[] = number_format($value->grand_total,2);
                $row[] = number_format($value->adjustment,2);
                $row[] = number_format($value->net_total,2);
                $row[] = number_format($value->paid_amount,2);
                $row[] = number_format($value->change_amount,2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
            $this->model->count_filtered(), $data);
            
        }else{
            return response()->json($this->unauthorized());
        }
    }
}
