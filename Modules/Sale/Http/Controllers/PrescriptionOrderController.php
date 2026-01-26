<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Tax;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\Product;
use Modules\Sale\Entities\PrescriptionOrder;
use Modules\Sale\Http\Requests\PrescriptionOrderdeleteRequestForm;
use Modules\Sale\Http\Requests\PrescriptionOrderRequestForm;
use Illuminate\Support\Facades\Validator;
use Modules\Setting\Entities\Warehouse;


class PrescriptionOrderController extends BaseController
{
    use UploadAble;

    public function __construct(PrescriptionOrder $model)
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

    public function pos(Request $request)
    {
        if (permission('sale-access')) {
            $this->setPageData('POS Sale', 'POS Sale', 'fab fa-opencart', [['name' => 'POS Sale']]);
            $data = [
                'taxes' => Tax::activeTaxes(),
                'brands' => Brand::allBrands(),
                'categories' => Category::allCategories(),
                'invoice_no' => 'INV-' . date('yis') . rand(1, 99),
                'products' => Product::where('status', 1)->paginate(12),
                'items' => PrescriptionOrder::latest()->get(),
                'p_order' => PrescriptionOrder::orderBy('id', 'desc')->first(),
                'warehouses' => Warehouse::allWarehouses(),
                'customers' => Customer::where('status', 1)->get(),
                'prescription_order_id' => optional($request)->prescription_order_id
            ];

            return view('sale::prescription-order.pos', $data);
        } else {
            return $this->access_blocked();
        }
    }

    public function products(Request $request)
    {
        if ($request->ajax()) {
            $page = $request->page;
            $products = PrescriptionOrder::whereIn('status', [1, 2]);
            $prescription_order_id = $request->prescription_order_id;

            if (!empty($prescription_order_id)) {
                $products->where('id', $prescription_order_id);
            }

            $products = $products->first();

            $data = [
                'p_order' => $products,
            ];

            return view('sale::prescription-order.pos-product-list', $data)->render();
        }
    }

    public function indexApi(Request $request)
    {
        try {
            $query = $this->model->where('user_id', optional(Auth::user())->id);
            if (!empty($request->created_at)) {
                $query->where('created_at', $request->created_at);
            }

            $result = $query->select('id', 'user_id', 'slug',
                'prescription_file')->latest()->paginate(10);

            $output = $result;
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
                $fields = [
                    'mobile_no' => 'setMobileNo',
                    'sort_table' => 'setTableOrder'
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
                        $action .= ' <a class="dropdown-item" href="' . route('prescription-order.pos', ['prescription_order_id' => $value->id]) . '" >'
                            . self::actionButton('POS') . '</a>';
                    }
                    if (permission('coupon-delete')) {
                        $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="">' . self::ACTION_BUTTON['Delete'] . '</a>';
                    }
                    if (permission('coupon-edit')) {
                        $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '" data-status="' . $value->status . '" ><i class="fas fa-check-circle text-success mr-2"></i> Change Status</a>';
                    }

                    $row = [];
                    $row[] = $no;
                    $row[] = '<a href="#" class="show-image" data-id="' . $value->id . '" data-image="' . $value->prescription_file . '"  data-image_path="' . asset("storage/" . PRESRCIPTION_ORDER_FILE_PATH) . '" data-toggle="modal" data-target="#largeModal"><div class="image-container">' . $this->table_image(PRESRCIPTION_ORDER_FILE_PATH, $value->prescription_file, $value->id) . ' <i class="fas fa-eye text-secondary"></i></div>';
                    $row[] = isset($value->user_id) ? ($value->user->name ?? 'Not Available') : ($value->name ?? 'Not Available');
                    $row[] = isset($value->user_id) ? ($value->user->phone ?? 'Not Available') : ($value->phone ?? 'Not Available');
                    $row[] = isset($value->user_id) ? $value->user->address ?? 'Not Available' : $value->address ?? 'Not Available';
                    $row[] = isset($value->status) ? PRESCRIPTION_POS_STATUS_LABEL[$value->status] : '';
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

                $collection = collect($request->validated())->merge(['prescription_file' => $image, 'user_id' => optional(Auth::user())->id, 'phone' => optional(Auth::user())->phone]);

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

    public function store_or_update_data_api(PrescriptionOrderRequestForm $request)
    {
        DB::beginTransaction();
        try {
            if (!empty($request->prescription_file)) {
                $file = $this->upload_base64_image($request->prescription_file, PRESRCIPTION_ORDER_FILE_PATH);
                $collection = collect($request->validated())->merge(['prescription_file' => $file, 'user_id' => optional(Auth::user())->id, 'phone' => optional(Auth::user())->phone]);
                $collection = $this->track_data($collection, $request->update_id);
                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());
                $output = $this->store_message($result, $request->update_id);
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => 'error', 'success' => false, 'message' => $th->getMessage()];
        }

        return response()->json($output);
    }

    public function store_or_update_data_guest(Request $request)
    {
        $data = $request->only('name', 'phone', 'prescription_file', 'address');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'phone' => 'required|max:20',
            'address' => 'required|max:500',
            'prescription_file' => 'required',
        ]);

        if ($validator->fails()) {
            $msg = $validator->messages()->first();
            return response()->json([
                'success' => false,
                'message' => $msg,
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            if (!empty($request->prescription_file)) {
                $file = $this->upload_base64_image($request->prescription_file, PRESRCIPTION_ORDER_FILE_PATH);

                $otp = rand(1000, 9999);
                // for sms ------------------------
                $url = "https://sms.brainwavebd.com/api/sms/send";
                $data = [
                    "apiKey" => "A000050ebd75369-fec2-4d67-86ce-dc3f3d7e101f",
                    "contactNumbers" => $request->phone,
                    "senderId" => "8809612441286",
                    "textBody" => "Your OTP: $otp",
                ];
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                $response = curl_exec($ch);

                $collection = collect($request->only('name', 'phone', 'address'))->merge(['prescription_file' => $file, 'status' => "2", 'otp' => $otp]);
                $result = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());

                $output = ['status' => true,
                    'success' => true,
                    'message' => 'OTP Send For Verification!',
                    'otp' => $otp,
                ];
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            $output = ['status' => false, 'success' => false, 'message' => $th->getMessage()];
        }

        return response()->json($output);
    }

    public function verifyGuestOtp(Request $request)
    {

        $data = $request->only('otp');
        $validator = Validator::make($data, [
            'otp' => 'required|max:50',
        ]);

        if ($validator->fails()) {
            $msg = $validator->messages()->first();

            return response()->json([
                'success' => false,
                'message' => $msg,
            ], Response::HTTP_BAD_REQUEST);
        }

        $checker = PrescriptionOrder::where('otp', $request->otp)->exists();

        if ($checker) {
            $data = [
                'otp' => '',
                'status' => "1",
            ];

            PrescriptionOrder::where('otp', $request->otp)->update($data);

            return response()->json([
                'status' => true,
                'success' => true,
                'message' => 'OTP Verification successfully Done',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'success' => false,
                'message' => 'Wrong OTP!',
            ], 400);
        }
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
