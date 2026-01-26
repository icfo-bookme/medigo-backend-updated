<?php

namespace Modules\Coupon\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Entities\CouponReport;

class CouponReportController extends BaseController
{
    public function __construct(CouponReport $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('manage-coupon')) {
            $this->setPageData('Coupon Report', 'Coupon Report', 'fas fa-balance-scale', [['name' => 'Coupon Report']]);
            $data = [
                'coupons' => Coupon::pluck('name','id'),
            ];
            return view('coupon::coupon-report.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('manage-coupon')) {
            $fields = [
                'coupon_id' => 'setCouponId',
                'coupon_type' => 'setCouponType',
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
                $row[] = '<div class="text-left"><b>Name: </b>' . $value->name . '<br><b>Type: </b>' . COUPON_TYPE[$value->coupon_type] . '</div>';

                // Extract and format the invoice numbers
                $invoiceNos = collect($value->sales)->pluck('invoice_nos')->first();
                $saleIds = collect($value->sales)->pluck('sale_ids')->first();
                $invoiceNosArray = explode(',', $invoiceNos);
                $saleIdsArray = explode(',', $saleIds);

                // Create the links
                $invoiceLinks = [];
                for ($i = 0; $i < count($invoiceNosArray); $i++) {
                    $invoiceLinks[] = '<a class="text-info cursor-pointer view_invoice" data-id="' . $saleIdsArray[$i] . '">' . $invoiceNosArray[$i] . '</a>';
                    // Add a line break after every 5th item
                    if (($i + 1) % 5 == 0 && $i != count($invoiceNosArray) - 1) {
                        $invoiceLinks[] = '<br>';
                    }
                }
                $invoiceLinks = implode(', ', $invoiceLinks);

                $row[] = $invoiceLinks;
                $row[] = $value->sales_count;
                $row[] = $value->sales_sum_coupon_discount_value;
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
