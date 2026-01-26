<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\AssignDeliveryMan;
use Modules\Sale\Entities\Order;
use Modules\Sale\Entities\Sale;

class DeliveryManAssignController extends BaseController
{
    public function __construct(AssignDeliveryMan $model)
    {
        $this->model = $model;
    }

    public function assignDeliveryMan(Request $request)
    {
        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $assign_data = [
                    'sale_id' => $request->update_id,
                    'delivery_man_id' => $request->delivery_man_id,
                    'invoice_no' => $request->invoice_no,
                    'date' => date('Y-m-d'),
                ];
                $this->model->create($assign_data);

                if ($request->track_modal == 'Sale') {
                    $sale = Sale::find($request->update_id);
                    $sale->update(['delivery_status' => 4]);
                } elseif ($request->track_modal == 'Order') {
                    $sale = Order::find($request->update_id);
                    $sale->update(['delivery_status' => 4]);
                }
                // Log sale user activity
                $sale->user_activity()->create([
                    'activity_type' => 'sale_status_change',
                    'status_name' => ORDER_STATUS_VALUE[4],
                    'user_id' => auth()->id(),
                ]);
                DB::commit();
                $output = ['status' => 'success', 'message' => 'Delivery Man Assigned Successfully.'];
            } catch (\Exception $e) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('sale-access')) {
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
                $no++;
                $row = [];
                $row[] = $no;
                $row[] = 1;
                $row[] = '<a class="text-info cursor-pointer view_invoice" data-id="' . $value->id . '">' . $value->invoice_no . '</a>';
                $row[] = $value->customer ? $value->customer->name . ($value->customer->phone ? ' - ' . $value->customer->phone : '') : '';
                $row[] = $value->item;
                $row[] = $value->total_qty;
                $row[] = number_format($value->grand_total, 2);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function assignDeliveryManProduct()
    {
        if (permission('sale-access')) {
            $this->setPageData('Assign Delivery Man Product', 'Sale Manage', 'fab fa-opencart', [['name' => 'Assign Delivery Man Product']]);
            $data = [
                'delivery_man' => User::where('role_id', 4)->get(),
            ];
            return view('sale::deliveryMan.index', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function get_delivery_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('delivery-man-product-access')) {
            if (!empty($request->invoice_no)) {
                $this->model->setInvoiceNo($request->invoice_no);
            }
            if (!empty($request->delivery_man_id)) {
                $this->model->setDelivery_man_id($request->delivery_man_id);
            }

            $this->set_datatable_default_properties($request);//set datatable default properties
            $list = $this->model->getDatatableList();//get table data

            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $action = '';
                if (permission('delivery-man-product-view')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . url("sale/pos-invoice", $value->sale_id) . '" target="_blank">' . self::ACTION_BUTTON['View'] . '</a>';
                }
                $row = [];
                $row[] = $no;
                $row[] = $value->invoice_no
                    ? '<a class="text-info cursor-pointer view_invoice" data-id="' . $value->id . '">' . $value->invoice_no . '</a>'
                    : $value->invoice_no;

                $row[] = $value->sale ? ORDER_STATUS_LABEL[$value->sale->delivery_status] : '';
                $row[] = $value->user ? $value->user->name : '';
                $row[] = $value->sale ? $value->sale->name . '<br>' . $value->sale->phone : '';
                $row[] = (optional($value->sale)->information) . (optional($value->sale)->optional_information ? '<br>( <span style="color: #2ea8e5">Instructions : </span>'
                        . optional($value->sale)->optional_information . ') ' : '');
                $row[] = $value->sale ? $value->sale->item . '(' . $value->sale->total_qty . ')' : '';
                $row[] = $value->sale ? number_format($value->sale->grand_total, 2) : '';
                $row[] = isset($value->sale->payment_status) ? PAYMENT_STATUS_LABEL[$value->sale->payment_status] : PAYMENT_STATUS_LABEL[''];
                $row[] = $value->sale ? $value->sale->sale_date : '';
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }
}
