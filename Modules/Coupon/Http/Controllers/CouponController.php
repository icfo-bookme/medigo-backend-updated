<?php

namespace Modules\Coupon\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coupon\Entities\Coupon;
use Modules\Coupon\Http\Requests\CouponRequest;

class CouponController extends BaseController
{
    public function __construct(Coupon $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('manage-coupon')) {
            $this->setPageData('Coupon', 'Coupon', 'fas fa-balance-scale', [['name' => 'Coupon']]);
            $data = [
                'coupons' => Coupon::pluck('name','id')
            ];
            return view('coupon::index', $data);
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
                $action = '';
                if (permission('coupon-edit')) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('coupon-delete')) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }
                $row = [];
                $row[] = $no;
                $row[] = '<div class="text-left"><b>Name: </b>' . $value->name . '<br><b>Type: </b>' . COUPON_TYPE[$value->coupon_type] . '</div>';
                $row[] = '<div class="text-left"><b>Type: </b>' . COUPON_DISCOUNT_TYPE[$value->type] . '<br><b>Value: </b>' . ($value->type == 1 ? '$' : '') . $value->value . ($value->type == 2 ? '%' : 'Tk') . '<br><b>Limit: </b>' . $value->coupon_value_limit . ' Tk</div>';
                $row[] = '<b>Start Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->start_date)) . '<br><b>End Date: </b>' . date('Y-m-d h:i:s A', strtotime($value->end_date));
                $row[] = permission('coupon-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(CouponRequest $request)
    {
        if ($request->ajax() && permission('coupon-add')) {
            $collection = collect($request->all());
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
        if ($request->ajax() and permission('coupon-edit')) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('unit-delete')) {
            $result = $this->model->find($request->id)->delete();
            $output = $this->delete_message($result);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() and permission('coupon-edit')) {
            $result = $this->model->find($request->id)->update(['status' => $request->status]);
            $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error', 'message' => 'Failed To Change Status'];
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
