<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\SaleNotification;
use Modules\Sale\Http\Requests\PrescriptionOrderdeleteRequestForm;


class SaleNotificationController extends BaseController
{
    public function __construct(SaleNotification $model)
    {
        $this->model = $model;
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            $this->set_datatable_default_properties($request);
            $list = $this->model->getDatatableList();

            $data = [];
            $no = $request->input('start');
            foreach ($list as $value) {
                $no++;
                $date = Carbon::parse($value->created_at)->diffForHumans();

                $row = [];
                $row[] = '<a class="text-muted" href="' . url('order') . '?invoice_no=' . urlencode($value->invoice) . '">' . $value->invoice . ' New Order Just Placed ' .
                    $date . ' ' . ORDER_SOURCE_LABEL[$value->order_source] .
                    '</a>';
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
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


    public function sale_notification_list()
    {
        $new_sale_count = DB::table('sales')
            ->where('delivery_status', 1)
            ->count('id');

        $notifications_count = DB::table('sale_notifications')
            ->where('is_seen', 0)
            ->count('id');

        return ['notifications_count' => $notifications_count, 'new_sale_count' => $new_sale_count];
    }

    public function sale_notification_update()
    {
        $this->model->where('is_seen', 0)->update(['is_seen' => 1]);
        return response()->json(['success' => 'Notification is seen']);
    }
}
