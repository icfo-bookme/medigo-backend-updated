<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\OrderWithCall;
use Modules\Sale\Http\Requests\OrderWithCallFormRequest;
use Modules\Sale\Http\Requests\PrescriptionOrderdeleteRequestForm;


class OrderWithCallController extends BaseController
{

    public function __construct(OrderWithCall $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('manage-coupon')) {
            $this->setPageData('Order With Call', 'Order With Call', 'fas fa-balance-scale', [['name' => 'Coupon']]);
            return view('sale::order-with-call.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function indexApi(Request $request)
    {

        try {
            $query = $this->model;

            $result = $query->select('facebook', 'whatsapp', 'mobile')->where('status', 'active')->first();
            $output = $result;
            $output = ['status' => 'true', $output];

        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'message' => $th->getMessage()];
        }
        return response()->json($output);
    }


    public function get_datatable_data(Request $request)
    {


        if ($request->ajax()) {
            if (permission('manage-coupon')) {
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
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->facebook;
                    $row[] = $value->whatsapp;
                    $row[] = $value->mobile;
                    $row[] = $value->status;
                    $row[] = $value->created_by;
                    $row[] = $value->modified_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                    $row[] = action_button($action);
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(OrderWithCallFormRequest $request)
    {

        if ($request->ajax()) {
            DB::beginTransaction();
            try {
                $collection = collect($request->validated());
                $collection = $this->track_data($collection, $request->update_id);
                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                $output = $this->store_message($result, $request->update_id);

                DB::commit();
            } catch (\Throwable $th) {
                DB::rollback();
                $output = ['status' => 'error', 'message' => $th->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }


    public function edit(Request $request)
    {
        if ($request->ajax() && permission('unit-edit')) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(PrescriptionOrderdeleteRequestForm $request)
    {
        try {
            $result = $this->model->findorFail($request->id)->delete();
            $output = $this->delete_message($result);
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'message' => $th->getMessage()];
        }
        return response()->json($output);

    }

    public function deleteApi($id)
    {
        try {
            $result = $this->model->findorFail($id)->delete();
            $output = $this->delete_message($result);
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'message' => $th->getMessage()];
        }
        return response()->json($output);

    }

    public function change_status(PrescriptionOrderdeleteRequestForm $request)
    {
        try {
            $result = $this->model->find($request->id)->update(['status' => $request->status]);
            $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully']
                : ['status' => 'error', 'message' => 'Failed To Change Status'];
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'message' => $th->getMessage()];
        }
        return response()->json($output);
    }
}
