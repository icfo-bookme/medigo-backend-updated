<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class BaseController extends Controller
{
    protected $model;
    protected const DELETABLE = ['1' => 'No', '2' => 'Yes'];
    protected const ACTION_BUTTON = [
        'Edit' => '<i class="fas fa-edit text-primary mr-2"></i> Edit',
        'View' => '<i class="fas fa-eye text-warning mr-2"></i> View',
        'Change Status' => '<i class="fas fa-check-circle text-success mr-2"></i>Change Status',
        'Delete' => '<i class="fas fa-trash text-danger mr-2"></i> Delete',
        'Order History' => '<i class="fas fa-eye text-light-primary mr-2"></i> Order History',
        'Assign' => '<i class="fas fa-eye text-warning mr-2"></i> Assign DeliveryMan',
        'Set Point' => '<i class="fas fa-bullseye mr-2"></i> Set Point',
        'Log' => '<i class="fas fa-history text-info mr-2"></i> View Log',
    ];

    public function actionButton($key)
    {
        $button = [
            'Assign Delivery Man' => '<i class="fas fa-user-check text-primary mr-2"></i>Assign Delivery Man',
            'Set Point' => '<i class="fas fa-bullseye"></i> Set Point',
            'Edit' => '<i class="fas fa-edit text-primary mr-2"></i>Edit',
            'View' => '<i class="fas fa-eye text-warning mr-2"></i>View',
            'Details' => '<i class="fas fa-info-circle text-primary mr-2"></i>Details',
            'Purchase Details' => '<i class="fas fa-shopping-cart text-success mr-2"></i>Purchase Details',
            'Requisition Details' => '<i class="fas fa-list-alt text-info mr-2"></i>Requisition Details',
            'Receive Details' => '<i class="fas fa-box-open text-info mr-2"></i>Receive Details',
            'Return Details' => '<i class="fas fa-undo-alt text-danger mr-2"></i>Return Details',
            'Delete' => '<i class="fas fa-trash-alt text-danger mr-2"></i>Delete',
            'Change Status' => '<i class="fas fa-exchange-alt text-success mr-2"></i>Change Status',
            'Received' => '<i class="fas fa-check text-success mr-2"></i>Received',
            'Add Payment' => '<i class="fas fa-wallet text-info mr-2"></i>Add Payment',
            'Payment List' => '<i class="fas fa-file-invoice-dollar text-dark mr-2"></i>Payment List',
            'Finish Good' => '<i class="fas fa-box text-info mr-2"></i>Finish Good',
            'Production Product' => '<i class="fas fa-industry text-info mr-2"></i>Production Product',
            'Update Delivery' => '<i class="fas fa-truck-loading text-info mr-2"></i>Update Delivery',
            'Report' => '<i class="fas fa-chart-bar text-info mr-2"></i>Report',
            'Delivery' => '<i class="fas fa-shipping-fast text-info mr-2"></i>Delivery',
            'Receive' => '<i class="fas fa-dolly text-info mr-2"></i>Receive',
            'Return' => '<i class="fas fa-undo-alt text-danger mr-2"></i>Return',
            'Exchange' => '<i class="fas fa-sync-alt text-primary mr-2"></i>Exchange',
            'Return Invoice' => '<i class="fas fa-receipt text-danger mr-2"></i>Return Invoice',
            'Purchase Invoice' => '<i class="fas fa-file-invoice text-info mr-2"></i>Purchase Invoice',
            'Purchase' => '<i class="fas fa-cart-arrow-down text-info mr-2"></i>Purchase',
            'Received Invoice' => '<i class="fas fa-receipt text-info mr-2"></i>Received Invoice',
            'Delivery Invoice' => '<i class="fas fa-receipt text-info mr-2"></i>Delivery Invoice',
            'Save' => '<i class="fas fa-save text-success mr-2"></i>Save',
            'Generate Slip' => '<i class="fas fa-file-alt text-dark mr-2"></i>Generate Slip',
            'Builder' => '<i class="fas fa-tools text-success mr-2"></i>Builder',
            'Summary' => '<i class="fas fa-file-alt text-primary mr-2"></i>Summary',
            'POS' => '<i class="fas fa-cash-register text-primary mr-2"></i>POS',
        ];
        return $button[$key];
    }

    protected function setPageData(string $page_title, string $sub_title = null, string $page_icon = null, $breadcrumb = null)
    {
        view()->share(['page_title' => $page_title, 'sub_title' => $sub_title ?? $page_title, 'page_icon' => $page_icon, 'breadcrumb' => $breadcrumb]);
    }

    protected function table_image($path, $image, $alt_text, $gender = null)
    {
        if (!empty($path) && !empty($image) && !empty($alt_text)) {
            return "<img class='image' src='" . asset("storage/" . $path . $image) . "' alt='" . $alt_text . "' style='width:80px;'/>";
        } else {
            if ($gender) {
                return "<img src='" . asset("images/" . ($gender == 1 ? 'male' : 'female') . ".svg") . "' alt='Default Image' style='width:80px;'/>";
            } else {
                return "<img src='" . asset("images/default.svg") . "' alt='Default Image' style='width:50px;'/>";
            }
        }
    }

    protected function set_datatable_default_properties(Request $request)
    {
        $this->model->setOrderValue($request->input('order.0.column'));
        $this->model->setDirValue($request->input('order.0.dir'));
        $this->model->setLengthValue($request->input('length'));
        $this->model->setStartValue($request->input('start'));
    }

    protected function showErrorPage($errorCode = 404, $message = null)
    {
        $data['message'] = $message;
        return response()->view('errors.' . $errorCode, $data, $errorCode);
    }

    protected function response_json($status = 'success', $message = null, $data = null, $response_code = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data,
            'response_code' => $response_code,
        ]);
    }

    protected function datatable_draw($draw, $recordsTotal, $recordsFiltered, $data)
    {
        return array(
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $data,
        );
    }

    protected function assign_collector_message($result, $update_id = null)
    {
        return $result ? ['status' => 'success', 'message' => !empty($update_id) ? $this->responseMessage('Data Update') : $this->responseMessage('Collector Assign')]
            : ['status' => 'error', 'message' => !empty($update_id) ? $this->responseMessage('Failed Accept') : $this->responseMessage('Failed Save')];
    }

    protected function store_message($result, $update_id = null)
    {
        return $result ? ['status' => 'success', 'success' => true, 'message' => !empty($update_id) ? 'Data Has Been Updated Successfully' : 'Data Has Been Saved Successfully'] : ['status' => 'error', 'success' => false, 'message' => !empty($update_id) ? 'Failed To Update Data' : 'Failed To Save Data'];
    }

    protected function store_message_true_false($result, $update_id = null)
    {
        return $result ? ['status' => true, 'success' => true, 'message' => !empty($update_id) ? 'Data Has Been Updated Successfully' : 'Data Has Been Saved Successfully'] : ['status' => false, 'success' => false, 'message' => !empty($update_id) ? 'Failed To Update Data' : 'Failed To Save Data'];
    }

    protected function delete_message($result)
    {
        return $result ? ['status' => 'success', 'message' => 'Data Has Been Delete Successfully'] : ['status' => 'error', 'message' => 'Failed To Delete Data'];
    }

    protected function bulk_delete_message($result)
    {
        return $result ? ['status' => 'success', 'message' => 'Selected Data Has Been Delete Successfully'] : ['status' => 'error', 'message' => 'Failed To Delete Selected Data'];
    }

    protected function unauthorized()
    {
        return ['status' => 'error', 'message' => 'Unauthorized Access Blocked!'];
    }

    protected function data_message($data)
    {
        return $data ? $data : ['status' => 'error', 'message' => 'No data found'];
    }

    protected function access_blocked()
    {
        return redirect('unauthorized')->with(['status' => 'error', 'message' => 'Unauthorized Access Blocked']);
    }

    protected function track_data($collection, $update_id = null)
    {
        $created_by = $modified_by = auth()->user()->name ?? auth()->user()->phone;
        $created_at = $updated_at = Carbon::now();
        return $update_id ? $collection->merge(compact('modified_by', 'updated_at')) : $collection->merge(compact('created_by', 'created_at'));
    }


    protected function coa_head_code(string $head_name)
    {
        switch (strtolower(str_replace('-', '_', $head_name))) {
            case 'assets':
                return 1;
                break;
            case 'non_current_asset':
                return 101;
                break;
            case 'inventory':
                return 10101;
                break;
            case 'current_asset':
                return 102;
                break;
            case 'cash_&_cash_equivalent':
                return 10201;
                break;
            case 'cash_in_hand':
                return 1020101;
                break;
            case 'cash_at_bank':
                return 1020102;
                break;
            case 'cash_at_mobile_bank':
                return 1020103;
                break;
            case 'account_receivable':
                return 10202;
                break;
            case 'customer_receivable':
                return 102020100001;
                break;
            case '1_walking_customer':
                return 10202010001;
                break;
            case 'loan_receivable':
                return 1020202;
                break;
            case 'equity':
                return 2;
                break;
            case 'income':
                return 3;
                break;
            case 'product_sale':
                return 301;
                break;
            case 'service_income':
                return 302;
                break;
            case 'expense':
                return 4;
                break;
            case 'default_expense':
                return 401;
                break;
            case 'material_purchase':
                return 402;
                break;
            case 'employee_salary':
                return 403;
                break;
            case 'machine_purchase':
                return 404;
                break;
            case 'maintenance_service':
                return 405;
                break;
            case 'liabilities':
                return 5;
                break;
            case 'non_current_liabilities':
                return 501;
                break;
            case 'current_liabilities':
                return 502;
                break;
            case 'account_payable':
                return 50201;
                break;
            case 'default_supplier':
                return 5020100001;
                break;
            case 'employee_ledger':
                return 50202;
                break;
            case 'tax':
                return 50203;
                break;
        }
    }


    public function responseMessage($response)
    {
        $message = [
            'Barcode Create' => __('Barcode Has Been Generated Successfully'),
            'Request Accept' => __('Request Has Been Accepted Successfully'),
            'Request Assign' => __('Assign Executive Successfully'),
            'Collector Assign' => __('Assign Delivery man Successfully'),
            'Data Saved' => __('Data Has Been Saved Successfully'),
            'Data Update' => __('Data Has Been Updated Successfully'),
            'Failed Save' => __('Failed To Save Data'),
            'Failed Update' => __('Failed To Update Data'),
            'Data Delete' => __('Data Has Been Delete Successfully'),
            'Data Delete Failed' => __('Failed To Delete Data'),
            'Select Data Delete' => __('Selected Data Has Been Delete Successfully'),
            'Select Data Delete Failed' => __('Failed To Delete Selected Data'),
            'Unauthorized Blocked' => __('Unauthorized Access Blocked!'),
            'No Data' => __('No data found'),
            'Status Changed' => __('Status Has Been Changed Successfully'),
            'Status Changed Failed' => __('Failed To Change Status'),
            'Hold' => __('Data Hold Successfully'),
            'Hold Failed' => __('Failed to Hold Purchase Data'),
            'Select Status' => __('Please select status'),
            'Approval Status' => __('Approval Status Changed Successfully'),
            'Approval Status Failed' => __('Failed To Change Approval Status'),
            'Unauthorized' => __('Unauthorized Access Blocked!'),
            'Related Data' => __('This data cannot delete because it is related with others data.'),
            'Associated Data' => __('can\'t delete because they are associated with others data.'),
            'Expected Menu' => __('Except these menus'),
            'Associated Other Data' => __('because they are associated with others data.'),
            'Customer' => __('These customers'),
            'Payment Data' => __('Payment Data Saved Successfully'),
            'Payment Data Delete' => __('Failed to Save Payment Data'),
            'Account Deleted Transaction' => __('This account cannot delete because it is related with many transactions.'),
            'Selected Data Delete' => __('Selected Data Has Been Deleted Successfully.'),
            'Expected Role' => __('Except these roles'),
            'Roles' => __('These roles'),
            'Except' => __('Except these'),
            'Current Password' => __('Current password does not match!'),
            'Changed Password' => __('Password changed successfully'),
            'Failed Password' => __('Failed to change password. Try Again!'),
            'Warehouse Choose' => __('Please Choose An Warehouse')
        ];
        return $message[$response];
    }
}
