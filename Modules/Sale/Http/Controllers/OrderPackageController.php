<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Sale\Entities\OrderPackage;
use Modules\Sale\Http\Requests\OrderPackageRequestForm;
use Modules\Sale\Http\Requests\PrescriptionOrderdeleteRequestForm;
use Modules\Sale\Http\Requests\PrescriptionOrderRequestForm;


class OrderPackageController extends BaseController
{
    public function __construct(OrderPackage $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('manage-coupon')) {
            $this->setPageData('Prescription Order', 'Prescription Order', 'fas fa-balance-scale', [['name' => 'Coupon']]);
            return view('sale::prescription-order.index');
        } else {
            return $this->access_blocked();
        }
    }

    public function indexApi(Request $request)
    {

        try {
            $query = $this->model->with('productsList:id,order_package_id,sale_product_id,product_id,qty,sale_unit_id,net_unit_price,total',
                'productsList.product:id,name,image,slug,generic_id,brand_id',
                'productsList.product.company:id,name',
                'productsList.product.productUnits:id,product_id,product_unit_id,price,discount,qty',
                'productsList.product.productUnits.unit:id,unit_name',
                'productsList.product.generic:generic_name,id,slug',
                'productsList.product_variant:discount,id,product_id,price,qty,product_unit_id',
                'productsList.product_variant.unit:id,unit_name',
                'productsList.productUnits:id,product_id,product_unit_id,price,discount,qty',
                'productsList.productUnits.unit:id,unit_name'
            );

            if (!empty(Auth::guard('customer')->user()->id)) {
                $query->where('user_id', Auth::guard('customer')->user()->id);
            }

            if (!empty($request->created_at)) {
                $query->where('created_at', $request->created_at);
            }

            $result = $query
                ->select('id', 'name', 'user_id', 'start_date', 'delivery_date', 'auto_order_after_days', 'grand_total')->latest()->paginate(10);

            $output = $result;
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'message' => $th->getMessage()];
        }
        return response()->json($output);
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() and permission('manage-coupon')) {
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
                $row[] = '<a href="#" class="show-image" data-id="' . $value->id . '" data-image="' . $value->prescription_file . '"  data-image_path="' . asset("storage/" . PRESRCIPTION_ORDER_FILE_PATH) . '" data-toggle="modal" data-target="#largeModal"><div class="image-container">' . $this->table_image(PRESRCIPTION_ORDER_FILE_PATH, $value->prescription_file, $value->created_at) . ' <i class="fas fa-eye text-secondary"></i></div>';
                $row[] = $value->created_at ? date(config('settings.date_format'), strtotime($value->created_at)) : '';
                $row[] = permission('coupon-edit') ? change_status($value->id, $value->status, $value->created_at) : STATUS_LABEL[$value->status];
                $row[] = $value->created_by;
                $row[] = $value->modified_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Modified Yet</span>';
                $row[] = action_button($action);
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(PrescriptionOrderRequestForm $request)
    {
        if ($request->ajax() && permission('coupon-add')) {
            DB::beginTransaction();
            try {
                $image = '';
                if ($request->hasFile('prescription_file')) {
                    $image = $this->upload_file($request->file('prescription_file'), PRESRCIPTION_ORDER_FILE_PATH);
                    if (!empty($request->old_prescription_file)) {
                        $this->delete_file($request->old_prescription_file, PRESRCIPTION_ORDER_FILE_PATH);
                    }
                }
                $collection = collect($request->validated())->merge(['prescription_file' => $image, 'user_id' => Auth::guard('customer')->user()->id]);

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

    public function store_or_update_data_api(OrderPackageRequestForm $request)
    {
        DB::beginTransaction();
        try {
            $collection = collect($request->validated())->merge(['user_id' => auth()->user()->id]);

            $collection = $this->track_data($collection, $request->update_id);

            $products = [];

            $grand_total = 0;
            $total_qty = 0;
            $total_discount = 0;
            $total_discount_rate = 0;
            $total_net_total = 0;
            $idx = 0;

            if ($request->has('products')) {
                foreach ($request->products as $key => $item) {
                    $grand_total += $item['total'] ?? 0;
                    $total_qty += $item['qty'] ?? 0;
                    $total_discount += $item['discount'] ?? 0;
                    $total_net_total += $item['net_unit_price'] ?? 0;
                    $idx += 1;

                    $products[] = [
                        'sale_product_id' => $item['id'],
                        'product_id' => $item['product_id'],
                        'net_unit_price' => $item['net_unit_price'],
                        'qty' => $item['qty'],
                        'sale_unit_id' => $item['sale_unit_id'] ?? 0,
                        'discount' => $item['discount'] ?? 0,
                        'discount_rate' => $item['discount_rate'] ?? 0,
                        'total' => $item['total'] ?? 0
                    ];
                }
            }

            $new = [
                'grand_total' => $grand_total,
                'net_total' => $total_net_total,
                'total_discount' => $total_discount,
                'total_qty' => $total_qty,
                'item' => $idx
            ];
            $package = $this->model->updateOrCreate(
                ['id' => $request->update_id],
                $collection->merge($new)->all()
            );

            if (count($products) > 0) {
                $package->products()->sync($products);
            }

            DB::commit();
            $output = $this->store_message($package, $request->update_id);
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'message' => $th->getMessage()];
        }
        return response()->json($output);
    }


    public function edit(Request $request)
    {
        if ($request->ajax()) {
            if (permission('unit-edit')) {
                $data = $this->model->findOrFail($request->id);
                $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            } else {
                $output = $this->unauthorized();
            }
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
