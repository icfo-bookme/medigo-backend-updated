<?php

namespace Modules\Customer\Http\Controllers;

use Berkayk\OneSignal\OneSignalClient;
use Exception;
use App\Traits\UploadAble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Customer\Entities\Customer;
use Modules\Sale\Entities\Sale;
use Modules\Setting\Entities\Warehouse;
use App\Http\Controllers\BaseController;
use App\Models\User;
use Modules\Account\Entities\Transaction;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Customer\Http\Requests\CustomerFormRequest;

class CustomerController extends BaseController
{
    use UploadAble;

    public function __construct(Customer $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('customer-access')) {
            $this->setPageData('Customer', 'Customer', 'far fa-handshake', [['name' => 'Customer']]);
            return view('customer::index');
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax() && permission('customer-access')) {
            $fields = [
                'search_text' => 'setSearchText',
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
                if (permission('customer-edit') && $value->id != 1) {
                    $action .= ' <a class="dropdown-item edit_data" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                    $action .= ' <a class="dropdown-item set_point" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Set Point'] . '</a>';
                }
                if (permission('customer-delete') && $value->id != 1) {
                    $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }
                $orderList = '<a class="dropdown-item rounded view_order" style="background:#FA8C15; opacity: 0.5; width:fit-content; display:inline-flex;" data-customer_id="' . $value->id . '">' . self::ACTION_BUTTON['Order History'] . '</a>';

                $row = [];
                $row[] = row_checkbox($value->id);
                $row[] = $no;
                $row[] = $orderList . '<br><br>' . $value->name;
                $row[] = $value->phone;
                $row[] = ($value->information) . ($value->optional_information ? ' <br> <p class="bg-primary" style="width:33%;color: white">Optional: </p>' . ($value->optional_information) : '');
                $row[] = permission('customer-edit') ? change_status($value->id, $value->status, $value->name) : STATUS_LABEL[$value->status];
                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(), $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(CustomerFormRequest $request)
    {
        if (($request->ajax() && permission('customer-add')) || ($request->ajax() && permission('customer-edit'))) {
            DB::beginTransaction();
            try {
                $collection = $this->track_data(collect($request->all()), $request->update_id);
                $collection = $collection->merge(['warehouse_id' => 1]);
                $customer = $this->model->updateOrCreate(['id' => $request->update_id], $collection->all());

                if (empty($request->update_id)) {
                    $coa_max_code = ChartOfAccount::where('level', 4)->where('code', 'like', '1020201%')->max('code');
                    $code = $coa_max_code ? ($coa_max_code + 1) : $this->coa_head_code('customer_receivable');
                    $head_name = $customer->id . '-' . $customer->name;
                    $customer_coa_data = $this->customer_coa($code, $head_name, $customer->id);
                    $customer_coa = ChartOfAccount::create($customer_coa_data);
                    if (!empty($request->previous_balance)) {
                        if ($customer_coa) {
                            $this->previous_balance_add($request->previous_balance, $customer_coa->id, $customer->name, $request->warehouse_id);
                        }
                    }
                } else {
                    $new_head_name = $request->update_id . '-' . $request->name;
                    $customer_coa = ChartOfAccount::where(['customer_id' => $request->update_id])->first();
                    if ($customer_coa) {
                        $customer_coa->update(['name' => $new_head_name]);
                    }
                }
                // Update or create customer and user records
                $output = $this->store_message($customer, $request->update_id);
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function sendPushNotification(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'selected_ids' => 'required|string', // Expecting a comma-separated string
            'headings' => 'required|string|max:255',
            'url' => 'nullable|url|max:255',
            'message' => 'required|string|max:500',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Convert the selected_ids string into an array
        $selectedIds = explode(',', $validated['selected_ids']);

        $response = [];
        $successCount = 0;
        $failCount = 0;

        try {
            $client = new OneSignalClient(config('onesignal.app_id'), config('onesignal.rest_api_key'), null);

            // Loop through each selected user ID
            foreach ($selectedIds as $userId) {
                try {
                    $client->sendNotificationToExternalUser(
                        $validated['message'],                 // Message content
                        trim($userId),                         // External User ID (trim whitespace)
                        $validated['url'] ?? null,             // Optional URL
                        [
                            'headings' => ['en' => $validated['headings']],  // Notification heading
                            'big_picture' => $validated['image'] ?? $validated['old_image'] ?? null, // Big picture image
                        ],
                        null,                                  // Buttons (optional)
                        null,                                  // Schedule (optional)
                        $validated['headings'],                // Notification heading
                        null,                                  // Subtitle (optional)
                    );
                    $successCount++;
                } catch (Exception $e) {
                    $failCount++;
                    info("Failed to send notification to user {$userId}", ['error' => $e->getMessage()]);
                }
            }

            // Prepare response
            $response['status'] = 'success';
            $response['message'] = "{$successCount} notifications sent successfully, {$failCount} failed.";

        } catch (\Exception $e) {
            info("Failed to send notifications.", ['error' => $e->getMessage()]);
            $response['status'] = 'error';
            $response['message'] = "An error occurred while sending notifications: " . $e->getMessage();
        }

        return response()->json($response);
    }

    public function edit(Request $request)
    {
        if ($request->ajax() && permission('customer-edit')) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function delete(Request $request)
    {
        if ($request->ajax() && permission('customer-delete')) {
            DB::beginTransaction();
            try {
                $total_sale_data = DB::table('sales')->where('customer_id', $request->id)->count();
                if ($total_sale_data > 0) {
                    $output = ['status' => 'error', 'message' => 'This data cannot delete because it is related with sales data.'];
                } else {
                    $result = $this->model->find($request->id)->delete();
                    $output = $this->delete_message($result);
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $output = ['status' => 'error', 'message' => $e->getMessage()];
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function change_status(Request $request)
    {
        if ($request->ajax() && permission('customer-edit')) {
            $result = $this->model->find($request->id)->update(['status' => $request->status]);
            $output = $result ? ['status' => 'success', 'message' => 'Status Has Been Changed Successfully'] : ['status' => 'error', 'message' => 'Failed To Change Status'];
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function customer_list(Request $request)
    {
        if ($request->ajax()) {
            $warehouse_id = $request->warehouse_id;
            $customers = DB::table('customers')
                ->select('id', 'name', 'phone', 'status')
                ->where('status', 1)
                ->when($warehouse_id, function ($q) use ($warehouse_id) {
                    $q->where('warehouse_id', $warehouse_id);
                })->orderBy('id', 'desc')
                ->get();
            $output = '';
            if (!$customers->isEmpty()) {
                foreach ($customers as $value) {
                    $output .= '<option value="' . $value->id . '">' . $value->name . ' - ' . $value->phone . '</option>';
                }
            }
            return $output;
        }
    }

    public function customer_lists(Request $request)
    {
        if ($request->ajax()) {
            $warehouse_id = $request->warehouse_id;
            $customers = DB::table('customers')
                ->select('id', 'name', 'phone')
                ->when($warehouse_id, function ($q) use ($warehouse_id) {
                    $q->where('warehouse_id', $warehouse_id);
                })
                ->get();
            $output = '';
            if (!$customers->isEmpty()) {
                $output .= '<option value="1">Walking Customer</option>';
                foreach ($customers as $value) {
                    $output .= '<option value="' . $value->id . '">' . $value->name . '</option>';
                }
            }
            return $output;
        }
    }

    public function view_order(Request $request)
    {
        if ($request->ajax() && permission('customer-access')) {
            if ($request->customer_id) {
                $data = [
                    'data' => Sale::where('customer_id', $request->customer_id)->orderBy('id', 'desc')->get(),
                    'delivery_status' => ORDER_STATUS_VALUE
                ];
            }
            if ($request->customer_phone_no) {
                $customer = Customer::where('phone', $request->customer_phone_no)->first('id');
                $data = [
                    'data' => Sale::where(['phone' => $request->customer_phone_no])
                        ->orWhere(['customer_id' => optional($customer)->id])
                        ->latest()->get(),
                    'delivery_status' => ORDER_STATUS_VALUE
                ];
            }

            $output = $this->data_message($data);
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    private function customer_coa(string $code, string $head_name, int $customer_id)
    {
        return [
            'code' => $code,
            'name' => $head_name,
            'parent_name' => 'Customer Receivable',
            'level' => 4,
            'type' => 'A',
            'transaction' => 1,
            'general_ledger' => 2,
            'customer_id' => $customer_id,
            'supplier_id' => null,
            'budget' => 2,
            'depreciation' => 2,
            'depreciation_rate' => '0',
            'status' => 1,
            'created_by' => optional(Auth::user())->name ?? ''
        ];
    }

    private function previous_balance_add($balance, int $customer_coa_id, string $customer_name)
    {
        if (!empty($balance) && !empty($customer_coa_id) && !empty($customer_name)) {
            $transaction_id = generator(10);
            $cosdr = array(
                'chart_of_account_id' => $customer_coa_id,
                'voucher_no' => $transaction_id,
                'voucher_type' => 'PR Balance',
                'voucher_date' => date("Y-m-d"),
                'description' => 'Customer debit For ' . $customer_name,
                'debit' => $balance,
                'credit' => 0,
                'posted' => 1,
                'approve' => 1,
                'created_by' => optional(Auth::user())->name ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );
            $inventory = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'),
                'voucher_no' => $transaction_id,
                'voucher_type' => 'PR Balance',
                'voucher_date' => date("Y-m-d"),
                'description' => 'Inventory credit For Old sale For ' . $customer_name,
                'debit' => 0,
                'credit' => $balance,
                'posted' => 1,
                'approve' => 1,
                'created_by' => optional(Auth::user())->name ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );
            Transaction::insert([$cosdr, $inventory]);
        }
    }

    private function previous_balance_update($balance, int $customer_coa_id, string $customer_name)
    {
        if (!empty($balance) && !empty($customer_coa_id) && !empty($customer_name)) {
            $customer_pr_balance_data = Transaction::where(['chart_of_account_id' => $customer_coa_id, 'voucher_type' => 'PR Balance',])->first();
            $voucher_no = $customer_pr_balance_data->voucher_no;
            $updated = $customer_pr_balance_data->update([
                'description' => 'Customer debit For ' . $customer_name,
                'debit' => $balance,
                'modified_by' => optional(Auth::user())->name ?? '',
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            if ($updated) {
                Transaction::where([
                    'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('inventory'))->value('id'), 'voucher_no' => $voucher_no])
                    ->update([
                        'description' => 'Inventory credit For Old sale For ' . $customer_name,
                        'credit' => $balance,
                        'modified_by' => optional(Auth::user())->name ?? '',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                return true;
            } else {
                return false;
            }
        }
    }
}
