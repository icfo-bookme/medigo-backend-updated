<?php
//IMAGE FOLDER PATH
define('LOGO_PATH', 'logo/');
define('USER_PHOTO_PATH', 'user/');
define('MATERIAL_IMAGE_PATH', 'material/');
define('PRODUCT_IMAGE_PATH', 'product/');
define('PRESRCIPTION_ORDER_FILE_PATH', 'prescription/');
define('CAMPAIGN_IMAGE_PATH','campaign/');
define('EMPLOYEE_NID_PHOTO', 'employee/');
define('EMPLOYEE_IMAGE_PATH', 'employee/');
define('SALESMEN_AVATAR_PATH', 'salesmen/');
define('ASM_AVATAR_PATH', 'asm/');
define('CUSTOMER_AVATAR_PATH', 'customer/');
define('PURCHASE_DOCUMENT_PATH', 'purchase-document/');
define('SALE_DOCUMENT_PATH', 'sale-document/');
define('TRANSFER_DOCUMENT_PATH', 'transfer-document/');
define('ASM_BASE_PATH', 'http://kohinoor-asm.test/');


define('GENDER_LABEL', [
    '1' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Male</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Female</span>',
    '3' => '<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Other</span>',
]);
define('STATUS', ['1' => 'Active', '2' => 'Inactive']);
define('MATERIAL_TYPE', ['1' => 'Raw', '2' => 'Packaging']);
define('PRODUCT_TYPE', ['1' => 'Can', '2' => 'Foil']);
define('APPROVE_STATUS', ['1' => 'Approved', '2' => 'Pending', '3' => 'Cancelled']);
define('EXPENSE_APPROVE_STATUS', ['1' => 'Approved', '2' => 'Rejected', '3' => 'Pending']);
define('TAX_METHOD', ['1' => 'Exclusive', '2' => 'Inclusive']);
define('HAS_OFFER', ['1' => 'Yes', '2' => 'No']);
define('TYPE', ['1' => 'Standard', '2' => 'Variant']);
define('VOUCHER_APPROVE_STATUS', [
    '1' => 'Approved',
    '2' => 'Rejected',
    '3' => 'Pending',
]);
define('TYPE_SALE', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Sale</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Hold</span>',
]);
define('TYPE_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Standard</span>',
    '2' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Variant</span>',
]);
define('MATERIAL_TYPE_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Raw</span>',
    '2' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Packaging</span>',
]);
define('PRODUCT_TYPE_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Can</span>',
    '2' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Foil</span>',
]);
define('STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Active</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Inactive</span>',
]);

define('COUPON_TYPE', [
    '1' => '<span class="label label-light-primary label-pill label-inline" style="min-width:70px !important;">General Coupon</span>',
    '2' => '<span class="label label-light-success label-pill label-inline" style="min-width:70px !important;">Category Coupon</span>',
    '3' => '<span class="label label-light-info label-pill label-inline" style="min-width:70px !important;">Customer Coupon</span>',
]);
define('COUPON_DISCOUNT_TYPE', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Fiexd</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Percentage</span>',
]);

define('CAMPAIGN_TYPE',['1' => 'Product', '2' => 'Category']);
define('CAMPAIGN_TYPE_LABEL',[
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Product</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Category</span>',
]);

define('PRODUCT_TYPES', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Search Product</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Home Product</span>',
]);
define('PRE_POST_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Pre</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Post</span>',
]);
define('PAID_UNPAID_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Unpaid</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Paid</span>',
]);
define('ALLOWANCE_DEDUCTION_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Allowance</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Deduction</span>',
    '3' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Others</span>',
]);
define('EXPENSE_APPROVE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Rejected</span>',
    '3' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Pending</span>',
]);
define('VOUCHER_APPROVE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Rejected</span>',
    '3' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Pending</span>',
]);
define('DAY_NIGHT_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Day</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Night</span>',
]);
define('SUPPLIER_TYPE_LABEL', [
    '1' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Material</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Machine</span>',
]);

define('APPROVE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '3' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Cancelled</span>',
]);
define('STOCK_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Available</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Out of Stock</span>',
]);
define('DELETABLE_LABEL', [
    '1' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">No</span>',
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Yes</span>',
]);
define('BARCODE_SYMBOL', ([
    "C128" => "Code 128",
    "C39" => "Code 39",
    "UPCA" => "UPC-A",
    "UPCE" => "UPC-E",
    "EAN8" => "EAN-8",
    "EAN13" => "EAN-13"
]));
define('ASSET_STATUS', ([
    "1" => "Good",
    "2" => "Broken",
    "3" => "Recycled",
    "4" => "On Service",
    "5" => "Archived",
]));


define('PRODUCTION_STATUS', ['1' => 'Pending', '2' => 'Processing', '3' => 'Finished']);

define('VOUCHER_STATUS', ['1' => 'Pending', '2' => 'Delivered', '3' => 'Processing', '4' => 'Order Placed', '5' => 'On Delivery', '6' => 'Cancle']);

define('ORDER_SOURCE', ['1' => 'Facebook', '2' => 'Whatsapp', '3' => 'Call', '4' => 'SHOWROOM', '5' => 'PRESCRIPTION', '6' => 'POS']);

define('ORDER_SOURCE_LABEL', [
    '1' => '<span class="label label-primary label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important ; background: #1b1dc9;padding: 15px;"k">FACEBOOK</span>',
    '2' => '<span class="label label-success label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important;background: #25D366;padding: 15px;">WHATSAPP</span>',
    '3' => '<span class="label label-success label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important;background: #07ce9f;padding: 15px;">CALL</span>',
    '4' => '<span class="label label-success label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important;background: #d96613;padding: 15px;">SHOWROOM</span>',
    '5' => '<span class="label label-success label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important;background: #11bccc;padding: 15px;">PRESCRIPTION</span>',
    '6' => '<span class="label label-success label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important;background: #11bccc;padding: 15px;">POS</span>',
    '' => '<span class="label label-info label-pill label-inline h3" style="border-radius: 5px;min-width:70px !important;background:#5fd21d ;padding: 15px;">WEBSITE</span>',
]);

define('TRANSFER_STATUS', ['1' => 'Pending', '2' => 'Complete']);

define('WELCOME_CALL_STATUS', ['1' => 'Pending', '2' => 'Approved']);
define('WELCOME_CALL_STATUS_LABEL', [
    '1' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
]);

define('CUSTOMER_FEEDBACK_TYPE', ['1' => 'Delivery', '2' => 'Communication']);
define('CUSTOMER_FEEDBACK_TYPE_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Delivery</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Communication</span>',
]);

define('PRESCRIPTION_POS_STATUS', ['1' => 'Pending', '2' => 'Confirmed', '3' => 'Cancelled']);

define('PRESCRIPTION_POS_STATUS_LABEL', [
    '1' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-success  label-pill label-inline" style="min-width:70px !important;">Confirmed</span>',
    '3' => '<span class="label-danger label-pill label-inline" style="min-width:70px !important;">Cancelled</span>',
    '' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">No Status</span>',
]);

define('ORDER_STATUS_VALUE', ['1' => 'Order Placed', '2' => 'Confirmed', '3' => 'Processing', '4' => 'Assign To Rider', '5' => 'Delivered', '6' => 'Cancel', '7' => 'Exchange', '8' => 'Partial Return', '9' => 'Full Return']);

define('ORDER_STATUS_LABEL', [
    '1' => '<span class="label label-info label-pill label-inline" style="min-width:93px !important;">Order Placed</span>',
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:93px !important;">Confirmed</span>',
    '3' => '<span class="label label-primary label-pill label-inline" style="min-width:93px !important;">Processing</span>',
    '4' => '<span class="label label-warning label-pill label-inline" style="min-width:93px !important;">Assign To Rider</span>',
    '5' => '<span class="label label-success label-pill label-inline" style="min-width:93px !important;background-color:#4CAF50;">Delivered</span>',
    '6' => '<span class="label label-danger label-pill label-inline" style="min-width:93px !important;">Cancel</span>',
    '7' => '<span class="label label-info label-pill label-inline" style="min-width:93px !important;background-color:#FF6347;">Exchange</span>',
    '8' => '<span class="label label-warning label-pill label-inline" style="min-width:93px !important;background-color:#FFD700;">Partial Return</span>',
    '9' => '<span class="label label-danger label-pill label-inline" style="min-width:93px !important;background-color:#8B0000;">Full Return</span>',
]);

define('EXCHANGE_STATUS_LABEL', [
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '1' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
]);

define('PRODUCTION_STATUS_LABEL', [
    '1' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Processing</span>',
    '3' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Finished</span>',
]);

define('TRANSFER_STATUS_LABEL', [
    '1' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Complete</span>',
]);

define('DAYS', ['1' => 'Saturday', '2' => 'Sunday', '3' => 'Monday', '4' => 'Tuesday', '5' => 'Wednesday', '6' => 'Thursday']);

define('PURCHASE_STATUS', ['1' => 'Received', '2' => 'Partial', '3' => 'Pending', '4' => 'Ordered']);

define('PURCHASE_STATUS_VALUE', ['1' => 'Ordered', '2' => 'Pending', '3' => 'Reject', '4' => 'Approved']);

define('PURCHASE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Received</span>',
    '2' => '<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Partial</span>',
    '3' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '4' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Ordered</span>',
]);
define('REQUISITION_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Pending</span>'
]);
define('PURCHASE_TYPE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Requisition</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Purchase</span>'
]);

define('PAYMENT_STATUS', ['1' => 'Paid', '2' => 'Partial', '3' => 'Due']);

define('PAYMENT_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Paid</span>',
    '2' => '<span class="label label-warning label-pill label-inline" style="min-width:70px !important;">Partial</span>',
    '3' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Due</span>',
    '' => '<span class="label label-secondary label-pill label-inline" style="min-width:70px !important;">No Payments</span>',
]);


define('PERSONAL_LOAN_TYPE', ['1' => 'Short Term', '2' => 'Long Term']);
define('PAYMENT_METHOD', ['1' => 'Cash', '2' => 'Cheque', '3' => 'Mobile Bank']);
define('SALE_PAYMENT_METHOD', ['1' => 'Cash', '2' => 'Bank', '3' => 'Mobile Bank']);
define('DELIVERY_STATUS', ['1' => 'Pending', '2' => 'Partially Delivered', '3' => 'Delivered']);
define('MAIL_MAILER', (['smtp', 'sendmail', 'mail']));
define('MAIL_ENCRYPTION', (['none' => 'null', 'tls' => 'tls', 'ssl' => 'ssl']));

//Employee Form Constant
define('JOB_STATUS', ['1' => 'Permanent', '2' => 'Probation', '3' => 'Resigned', '4' => 'Suspended']);
define('DUTY_TYPE', ['1' => 'Full Time', '2' => 'Part Time', '3' => 'Contractual', '4' => 'Other']);
define('RATE_TYPE', ['1' => 'Hourly', '2' => 'Salary']);
define('PAY_FREQUENCY', ['1' => 'Weekly', '2' => 'Biweekly', '3' => 'Monthly', '4' => 'Annual']);
define('GENDER', ['1' => 'Male', '2' => 'Female', '3' => 'Other']);
define('MARITAL_STATUS', ['1' => 'Single', '2' => 'Married', '3' => 'Divorced', '4' => 'Widowed', '5' => 'Other']);
define('BLOOD_GROUP', ['1' => 'A+', '2' => 'B+', '3' => 'A-', '4' => 'B-', '5' => 'AB+', '6' => 'AB-', '7' => 'O+', '8' => 'O-']);
define('IS_SUPERVISOR', ['1' => 'Yes', '2' => 'No']);
define('OVERTIME', ['1' => 'Allowed', '2' => 'Not Allowed']);
define('RESIDENTIAL_STATUS', ['1' => 'Resident', '2' => 'Non Resident']);

//HRM Constant
define('EVENT_STATUS', ['1' => 'Event', '2' => 'Holiday']);
define('EVENT_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Event</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Holiday</span>'
]);

define('ALLOWANCE_DEPARTMENT', ['1' => 'Employee', '2' => 'Labor']);
define('ALLOWANCE_DEPARTMENT_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Employee</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Labor</span>'
]);

define('LEAVE_STATUS', ['1' => 'Pending', '2' => 'Approved', '3' => 'Deleted']);
define('LEAVE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '3' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Deleted</span>'
]);
define('DAILY_ATTENDENCE', ['1' => 'Pending', '2' => 'Approved']);
define('DAILY_ATTENDENCE_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Approved</span>'
]);
define('EMPLOYEE_TYPE_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Provision</span>',
    '2' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Permanent</span>',
    '3' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Full Time</span>',
    '4' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Part Time</span>',
]);
define('TYPE_STATUS', ['1' => 'Event', '2' => 'Holiday']);
define('TYPE_STATUS_LABEL', [
    '1' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Event</span>',
    '2' => '<span class="label label-info label-pill label-inline" style="min-width:70px !important;">Holiday</span>'
]);

function salaryYears()
{
    $lastFiveYears = [];
    $currentYear = date('Y');

    for ($i = 0; $i < 5; $i++) {
        $lastFiveYears[] = $currentYear - $i;
    }
    return $lastFiveYears;
}

function allMonths()
{
    $months = [
        1 => "January",
        2 => "February",
        3 => "March",
        4 => "April",
        5 => "May",
        6 => "June",
        7 => "July",
        8 => "August",
        9 => "September",
        10 => "October",
        11 => "November",
        12 => "December"
    ];
    return $months;
}

if (!function_exists('permission')) {

    function permission(string $value)
    {
        if (collect(\Illuminate\Support\Facades\Session::get('user_permission'))->contains($value)) {
            return true;
        }
        return false;
    }
}

if (!function_exists('change_status')) {

    function change_status(int $id, int $status, string $name)
    {
        if ($id && $status && $name) {
            return $status == 1 ? '<span class="label label-success label-pill label-inline change_status" data-id="' . $id . '" data-name="' . $name . '" data-status="2" style="min-width:70px !important;cursor:pointer;">Active</span>'
                : '<span class="label label-danger label-pill label-inline change_status" data-id="' . $id . '" data-name="' . $name . '" data-status="1"  style="min-width:70px !important;cursor:pointer;">Inactive</span>';
        }
    }
}

if (!function_exists('action_button')) {

    function action_button($action)
    {
        return '<div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-th-list text-white"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    ' . $action . '
                    </div>
                </div>';
    }
}

if (!function_exists('row_checkbox')) {

    function row_checkbox($id)
    {
        return '<div class="custom-control custom-checkbox">
                    <input type="checkbox" value="' . $id . '"
                    class="custom-control-input select_data" onchange="select_single_item()" id="checkbox' . $id . '">
                    <label class="custom-control-label" for="checkbox' . $id . '"></label>
                </div>';
    }
}

if (!function_exists('read_more')) {

    function read_more($text, $limit = 400)
    {
        $text = $text . " ";
        $text = substr($text, 0, $limit);
        $text = substr($text, 0, strrpos($text, ' '));
        $text = $text . "...";
        return $text;
    }
}

if (!function_exists('generator')) {
    function generator($lenth)
    {
        $number = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "N", "M", "O", "P", "Q", "R", "S", "U", "V", "T", "W", "X", "Y", "Z", "1", "2", "3", "4", "5", "6", "7", "8", "9", "0");

        for ($i = 0; $i < $lenth; $i++) {
            $rand_value = rand(0, 34);
            $rand_number = $number["$rand_value"];

            if (empty($con)) {
                $con = $rand_number;
            } else {
                $con = "$con" . "$rand_number";
            }
        }
        return $con;
    }
}

if (!function_exists('customer_data_provider')) {
    function customer_data_provider($order, $customer)
    {
        $data = (object)[];

        $data->name = $order->customer_id ? optional($customer)->name : optional($order)->name;
        $data->phone = $order->customer_id ? optional($customer)->phone : optional($order)->phone;
        $data->information = $order->customer_id ? optional($customer)->information : optional($order)->information;
        $data->optional_information = $order->customer_id ? optional($customer)->optional_information : optional($order)->optional_information;

        return $data;
    }
}


if (!function_exists('numberTowords')) {
    function numberTowords($num)
    {

        $ones = array(
            0 => "Zero",
            1 => "One",
            2 => "Two",
            3 => "Three",
            4 => "Four",
            5 => "Five",
            6 => "Six",
            7 => "Seven",
            8 => "Eight",
            9 => "Nine",
            10 => "Ten",
            11 => "Eleven",
            12 => "Twelve",
            13 => "Thirteen",
            14 => "Fourteen",
            15 => "Fifteen",
            16 => "Sixteen",
            17 => "Seventeen",
            18 => "Eighteen",
            19 => "Nineteen",
            "014" => "Fourteen",
        );
        $tens = array(
            0 => "Zero",
            1 => "Ten",
            2 => "Twenty",
            3 => "Thirty",
            4 => "Forty",
            5 => "Fifty",
            6 => "Sixty",
            7 => "Seventy",
            8 => "Eighty",
            9 => "Ninety",
        );
        $hundreds = array(
            "Hundred",
            "Thousand",
            "Million",
            "Billion",
            "Trillion",
            "Quardrillion",
        ); /*limit t quadrillion */
        $num = number_format($num, 2, ".", ",");
        $num_arr = explode(".", $num);
        $wholenum = $num_arr[0];
        $decnum = $num_arr[1];
        $whole_arr = array_reverse(explode(",", $wholenum));
        krsort($whole_arr, 1);
        $rettxt = "";
        foreach ($whole_arr as $key => $i) {

            while (substr($i, 0, 1) == "0") {
                $i = substr($i, 1, 5);
            }

            if ($i < 20) {
                $rettxt .= $ones[$i];
            } elseif ($i < 100) {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $tens[substr($i, 0, 1)];
                }

                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 1, 1)];
                }

            } else {
                if (substr($i, 0, 1) != "0") {
                    $rettxt .= $ones[substr($i, 0, 1)] . " " . $hundreds[0];
                }

                if (substr($i, 1, 1) != "0") {
                    $rettxt .= " " . $tens[substr($i, 1, 1)];
                }

                if (substr($i, 2, 1) != "0") {
                    $rettxt .= " " . $ones[substr($i, 2, 1)];
                }

            }
            if ($key > 0) {
                $rettxt .= " " . $hundreds[$key] . " ";
            }
        }
        if ($decnum > 0) {
            $rettxt .= " AND ";
            if ($decnum < 20) {
                $rettxt .= $ones[$decnum];
            } elseif ($decnum < 100) {
                $rettxt .= $tens[substr($decnum, 0, 1)];
                $rettxt .= " " . $ones[substr($decnum, 1, 1)];
            }
        }
        return $rettxt;
    }

}

define('DRAFT_LABEL', [
    '1' => '<span class="label label-primary label-pill label-inline" style="min-width:70px !important;">Pending</span>',
    '2' => '<span class="label label-success label-pill label-inline" style="min-width:70px !important;">Approved</span>',
    '3' => '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Canceled</span>',
]);

define('DRAFT_STATUS', ['1' => 'Pending', '2' => 'Approved', '3' => 'Canceled']);
