<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Berkayk\OneSignal\OneSignalClient;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Customer\Entities\Customer;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Entities\SaleProduct;
use Modules\Account\Entities\Transaction;
use Exception;

class OrderController extends BaseController
{
    public function __construct(Sale $model)
    {
        $this->model = $model;
    }

    public function orderIndex()
    {
        if (permission('sale-access')) {
            $this->setPageData('Order Manage', 'Order Manage', 'fab fa-opencart', [['name' => 'Order Manage']]);
            $delivery_man = User::where('role_id', 4)->get();
            return view('sale::order.index', compact('delivery_man'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_order_datatable_data(Request $request)
    {
        if ($request->ajax() and permission('order-access')) {
            $fields = [
                'search_field' => 'setSearchField',
                'status' => 'setDeliveryStatus',
                'start_date' => 'setStartDate',
                'end_date' => 'setEndDate',
                'sort_table' => 'setTableOrder',
                'order_source_id' => 'setOrderSource'
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
                if (permission('order-status')) {
                    $action .= ' <a class="dropdown-item change_status"  data-id="' . $value->id . '"  data-visa_status="' . $value->delivery_status . '" ><i class="fas fa-check-circle text-success mr-2"></i> Change Status</a>';
                }
                if ($value->delivery_status == 3 and permission('order-assign')) {
                    $action .= ' <a class="dropdown-item assign_rider" data-id="' . $value->id . '" data-invoice_no="' . $value->invoice_no . '">' . $this->actionButton('Assign Delivery Man') . '</a>';
                }
                if ($value->delivery_status == 1 and permission('sale-edit')) {
                    $action .= ' <a class="dropdown-item" href="' . route("sale.edit", $value->id) . '">' . self::ACTION_BUTTON['Edit'] . '</a>';
                }
                if (permission('order-access')) {
                    $action .= ' <a class="dropdown-item view_data" href="' . url("order/order-pos-invoice", $value->id) . '" target="_blank">' . self::ACTION_BUTTON['View'] . '</a>';
                    $action .= ' <a class="dropdown-item view_log" data-id="' . $value->id . '">' . self::ACTION_BUTTON['Log'] . '</a>';
                }
                if (permission('sale-return') && $value->payment_status != 3 && !in_array($value->delivery_status, [7, 9])) {
                    if ($value->total_return_qty < $value->total_qty) {
                        $action .= '<a class="dropdown-item" href="' . route("stock.return", $value->id) . '">' . $this->actionButton('Return') . '</a>';
                    }
                    $action .= '<a class="dropdown-item" href="' . route("stock.exchange", $value->id) . '">' . $this->actionButton('Exchange') . '</a>';
                }

                if ($value->delivery_status == 1 && permission('order-delete')) {
                    $action .= ' <a class="dropdown-item delete_data" data-id="' . $value->id . '" data-name="' . $value->invoice_no . '">' . self::ACTION_BUTTON['Delete'] . '</a>';
                }

                $customer =  customer_data_provider($value, $value->customer);

                $orderList = '<a class="dropdown-item rounded view_order" style="background:#9b9fbb; opacity: 0.6; width:fit-content; display:inline-flex;" data-customer_phone_no="'. $customer->phone . '">' . self::ACTION_BUTTON['Order History'] . '</a>';

                $rider = $value->assignDeliveryMen->map(function ($assignDeliveryMan) {
                    return optional($assignDeliveryMan->user)->name;
                })->implode(', ');

                $row = [];

                $row[] = '<div><a class="text-info cursor-pointer view_invoice" data-id="' . $value->id . '">' . $value->invoice_no . '</a>
                        <hr style="border-color: transparent;"><p>'.ORDER_SOURCE_LABEL[$value->order_source_id].'</p> <hr style="border-color: transparent;">'. $orderList .'</div>';

                $row[] = '<div class="text-left"><b>Name: </b>' . ($customer->name) .
                    '<br><b>Phone: </b>' . ($customer->phone) .
                    '<br><b>Address: </b>' . $customer->information .
                    '<br>(<span style="color: #2ea8e5">Instructions : </span>' .$customer->optional_information .')<br><div class="text-left"><b>Date: </b><span class="text-warning font-weight-bolder font-size-h5">' .
                    date('Y-m-d h:i:s a', strtotime($value->created_at)) . '</span></div></div>';


                $row[] = '<div class="text-left"><b>Qty: </b>' . $value->total_qty . '<br><b>Delivery Cost: </b>' . number_format($value->shipping_cost, 2) . '/-TK<br><b>Grand Total: </b>' . number_format($value->grand_total, 2) . '/-TK</div>';

                $row[] = '<div class="text-left">
                            <div class="row mb-2">
                                <div class="col-5"><b>Payment: </b></div>
                                <div class="col-7">' . PAYMENT_STATUS_LABEL[$value->payment_status] . '</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5"><b>Sale: </b></div>
                                <div class="col-7">' . ORDER_STATUS_LABEL[$value->delivery_status] . '</div>
                            </div>
                            <div class="row">
                                <div class="col-5"><b>Rider: </b></div>
                                <div class="col-7">' . $rider . '</div>
                            </div>
                         </div>';

                $row[] = action_button($action);//custom helper function for action button
                $data[] = $row;
            }
            return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                $this->model->count_filtered(), $data);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function assign(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->model->findOrFail($request->id);
            $output = $this->data_message($data); //if data found then it will return data otherwise return error message
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function order_status(Request $request)
    {
        if (!$request->ajax()) {
            return response()->json(['error' => 'Invalid request.']);
        }

        $statusId = $request->status_id;
        $deliveryStatus = $request->delivery_status;

        $saleOrder = $this->model->find($statusId);
        $data = $saleOrder;
        if (!$saleOrder) {
            return response()->json(['error' => 'Sale order not found.']);
        }

        DB::beginTransaction();
        try {
            if ($deliveryStatus == 6) {
                foreach ($saleOrder->saleProductList as $saleProduct) {
                    $productUnit = ProductUnit::where('product_unit_id', $saleProduct->sale_unit_id)
                        ->where('product_id', $saleProduct->product_id)
                        ->first();
                    if ($productUnit) {
                        $productUnit->update(['qty' => $productUnit->qty + $saleProduct->qty]);
                    }
                }
                // Update delivery status
                $saleOrder->update(['delivery_status' => $deliveryStatus]);
                $activityType = 'sale_status_change';
                $statusName = 'Cancel';
            } else {
                // Update delivery status
                $saleOrder->update(['delivery_status' => $deliveryStatus]);
                $activityType = 'sale_status_change';
                $statusName = ORDER_STATUS_VALUE[$deliveryStatus];
            }

            if ($deliveryStatus == 5) {
                $payment_data = [];
                if (isset($data->pos_payments)) {
                    foreach ($data->pos_payments as $value) {
                        $payment_data[] = [
                            'payment_method' => $value->payment_method,
                            'account_id' => $value->account_id,
                            'paid_amount' => $value->paid_amount
                        ];
                    }
                }
                $customerAccountId = ChartOfAccount::where(['customer_id' => $data->customer_id])->first();
                $this->sale_balance_add($data->invoice_no, $data->grand_total, 0, $data->sale_date, $payment_data, $data->warehouse_id, $customerAccountId);
            }

            // Log sale user activity
            $saleOrder->user_activity()->create([
                'activity_type' => $activityType,
                'status_name' => $statusName,
                'user_id' => auth()->id(),
            ]);

            // Send notification to the user about order status change
            $this->sendOrderNotification($saleOrder->customer_id, $saleOrder);

            DB::commit();
            $output = $this->store_message(true, $statusId);
            return response()->json($output);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update sale order status.', 'message' => $e->getMessage()]);
        }
    }

    public function order_pos_invoice(int $id)
    {
        if (permission('sale-view')) {
            $this->setPageData('Online Order Sale Invoice', 'Online Order Sale Invoice', 'fas fa-file', [['name' => 'Online Order Sale Invoice']]);
            $sale = $this->model->with('products', 'coupon', 'customer', 'warehouse')->find($id);
            return view('sale::order.order-pos-invoice', compact('sale'));
        } else {
            return $this->access_blocked();
        }
    }

    public function orderLog(Request $request)
    {
        if ($request->ajax() && permission('sale-view')) {
            $sale = $this->model->with('user_activity')->find($request->id);
            return view('sale::log-data', compact('sale'))->render();
        } else {
            return response()->json($this->unauthorized());
        }
    }

    private function sendOrderNotification($userId, $sale)
    {
        try {
            $client = new OneSignalClient(config('onesignal.app_id'), config('onesignal.rest_api_key'), null);

            // Send notification
            $client->sendNotificationToExternalUser(
                "Your order #{$sale->invoice_no} status has been updated to " . ORDER_STATUS_VALUE[$sale->delivery_status], // Notification content
                (string)$userId, // External User ID
                null, // URL (optional)
                [
                    'headings' => ['en' => 'Order Status Updated'], // Title of the notification
                ]
            );

            info('Notification sent to user: ' . $userId);
            return [
                'success' => true,
                'message' => 'Notification sent successfully.',
            ];
        } catch (Exception $e) {
            info('Failed to send notification.', [
                'error_message' => $e->getMessage(),
                'user_id' => $userId,
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

//    private function sendOrderNotification($userId, $sale)
//    {
//        $client = new Client();
//        $response = $client->post('https://onesignal.com/api/v1/notifications', [
//            'headers' => [
//                'Authorization' => 'Basic ' . config('onesignal.rest_api_key'),
//                'Content-Type' => 'application/json',
//            ],
//            'json' => [
//                'app_id' => config('onesignal.app_id'),
//                'include_external_user_ids' => [(string)$userId],
//                'contents' => ['en' => "Your order #{$sale->invoice_no} status has been updated to " . ORDER_STATUS_VALUE[$sale->delivery_status]],
//                'headings' => ['en' => 'Order Status Update'],
//            ]
//        ]);
//        \Log::info('Notification sent to user: ' . $userId);
//        return json_decode($response->getBody()->getContents(), true);
//    }

    private function sale_balance_add($invoice_no, $grand_total, $total_tax, $sale_date, array $payment_data, $warehouse_id, $customerAccountId)
    {
        $saleChartOfAccountTransaction = array(
            'chart_of_account_id' => $customerAccountId->id,
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $sale_date,
            'description' => 'Customer debit For Invoice No -  ' . $invoice_no ,
            'debit' => $grand_total,
            'credit' => 0,
            'posted' => 1,
            'approve' => 1,
            'created_by' => optional(Auth::user())->name ?? '',
            'created_at' => date('Y-m-d H:i:s')
        );
        Transaction::create($saleChartOfAccountTransaction);

        $product_sale_income = array(
            'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('product_sale'))->value('id'),
            'warehouse_id' => $warehouse_id,
            'voucher_no' => $invoice_no,
            'voucher_type' => 'INVOICE',
            'voucher_date' => $sale_date,
            'description' => 'Sale Income ' . $grand_total . 'TK from Invoice No. - ' . $invoice_no,
            'debit' => 0,
            'credit' => $grand_total,
            'posted' => 1,
            'approve' => 1,
            'created_by' => optional(Auth::user())->name ?? '',
            'created_at' => date('Y-m-d H:i:s')
        );

        Transaction::create($product_sale_income);

        if ($total_tax) {
            $tax_info = array(
                'chart_of_account_id' => DB::table('chart_of_accounts')->where('code', $this->coa_head_code('tax'))->value('id'),
                'warehouse_id' => $warehouse_id,
                'voucher_no' => $invoice_no,
                'voucher_type' => 'INVOICE',
                'voucher_date' => $sale_date,
                'description' => 'Sale Total Tax For Invoice No. - ' . $invoice_no,
                'debit' => 0,
                'credit' => $total_tax,
                'posted' => 1,
                'approve' => 1,
                'created_by' => optional(Auth::user())->name ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );
            Transaction::create($tax_info);
        }

        $customerPaidAmount = 0;
        if (!empty($payment_data[0]['paid_amount'])) {
            foreach ($payment_data as $value) {
                if ($value['payment_method'] == 1) {
                    //Cah In Hand debit
                    $payment = array(
                        'chart_of_account_id' => $value['account_id'],
                        'warehouse_id' => $warehouse_id,
                        'voucher_no' => $invoice_no,
                        'voucher_type' => 'INVOICE',
                        'voucher_date' => $sale_date,
                        'description' => 'Cash Paid amount ' . $value['paid_amount'] . 'Tk for Invoice No. - ' . $invoice_no,
                        'debit' => $value['paid_amount'],
                        'credit' => 0,
                        'posted' => 1,
                        'approve' => 1,
                        'created_by' => optional(Auth::user())->name ?? '',
                        'created_at' => date('Y-m-d H:i:s')

                    );
                } else {
                    // Bank Ledger
                    $payment = array(
                        'chart_of_account_id' => $value['account_id'],
                        'warehouse_id' => $warehouse_id,
                        'voucher_no' => $invoice_no,
                        'voucher_type' => 'INVOICE',
                        'voucher_date' => $sale_date,
                        'description' => 'Paid amount ' . $value['paid_amount'] . 'Tk for Invoice No. - ' . $invoice_no,
                        'debit' => $value['paid_amount'],
                        'credit' => 0,
                        'posted' => 1,
                        'approve' => 1,
                        'created_by' => optional(Auth::user())->name ?? '',
                        'created_at' => date('Y-m-d H:i:s')
                    );
                }
                Transaction::create($payment);
                $customerPaidAmount += $value['paid_amount'];
            }

            $customerCredit = array(
                'chart_of_account_id' => $customerAccountId->id,
                'warehouse_id' => $warehouse_id,
                'voucher_no' => $invoice_no,
                'voucher_type' => 'INVOICE',
                'voucher_date' => $sale_date,
                'description' => 'Customer credit for Paid Amount For Customer Invoice NO- ' . $invoice_no ,
                'debit' => 0,
                'credit' => $customerPaidAmount,
                'posted' => 1,
                'approve' => 1,
                'Created_by' => optional(Auth::user())->name ?? '',
                'created_at' => date('Y-m-d H:i:s')
            );
            Transaction::create($customerCredit);
        }
    }
}
