<?php

namespace App\Http\Controllers\Api\Service;

use App\Services\SaleInvoiceContainer;
use Berkayk\OneSignal\OneSignalClient;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Keygen\Keygen;
use Modules\Coupon\Entities\Coupon;
use Modules\Customer\Entities\Customer;
use Modules\Customer\Entities\CustomerFeedback;
use Modules\Customer\Entities\WelcomeCall;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\Sale;
use Modules\Sale\Http\Controllers\Service\PosService;
use Modules\Setting\Entities\DeliveryCharge;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class OrderService
{
    public function createOrder(Request $request)
    {
        $validatedData = $request->validate([
            'invoice_no' => 'required',
            'item' => 'required|integer',
            'total_qty' => 'required|integer',
            'shipping_cost' => 'required|numeric',
            'net_total' => 'required|numeric',
            'grand_total' => 'required|numeric',
            'sale_date' => 'required|date',
            'customer_id' => 'nullable',
            'total_discount' => 'nullable|numeric',
            'total_discount_amount' => 'nullable|numeric',
            'name' => 'required',
            'phone' => 'required',
            'information' => 'required',
            'optional_information' => 'nullable',
            'coupon_id' => 'nullable',
            'coupon_discount_value' => 'nullable',
        ]);

        DB::beginTransaction();
        try {
            $invoice_code = (new SaleInvoiceContainer())->invoice_code_provider();

            $invoice_no = 'INV-' . $invoice_code;
            $customer = null;
            if (!empty($validatedData['customer_id'])) {
                $customer = Customer::find($validatedData['customer_id']);
            }

            // $customer = Customer::findOrFail($validatedData['customer_id']);

            if ($customer) {
                $isNewCustomerOrder = Sale::where('customer_id', $customer->id)->exists();

                // Check if a welcome call already exists for this customer
                $welcomeCallExists = WelcomeCall::where('customer_id', $customer->id)->exists();

                if (!$isNewCustomerOrder && !$welcomeCallExists) {
                    WelcomeCall::create([
                        'customer_id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'call_status' => 1,
                        'created_at' => Carbon::now(),
                    ]);
                }
            } else {
                $isNewCustomerOrder = Sale::where('phone', $validatedData['phone'])->exists();

                // Check if a welcome call already exists for this phone number
                $welcomeCallExists = WelcomeCall::where('phone', $validatedData['phone'])->exists();

                if (!$isNewCustomerOrder && !$welcomeCallExists) {
                    WelcomeCall::insert([
                        'name' => $validatedData['name'],
                        'phone' => $validatedData['phone'],
                        'email' => $validatedData['email'] ?? null,
                        'call_status' => 1,
                        'created_at' => Carbon::now(),
                    ]);
                }
            }

            $sale = new Sale();
            $sale->invoice_no = $invoice_no;
            $sale->date_wise_serial = $invoice_code;
            $this->fillSaleData($sale, $validatedData);

            if ($customer && $request->redeem_points_used) {
                $redeem_points = $request->redeem_points_used;
                if ($customer->customerPoint->available_point >= $redeem_points) {
                    $prev_grand_total = $request->prev_grand_total;

                    $redeemed_amount = $redeem_points / $customer->customerPoint->conversion_rate;
                    $new_grand_total = $prev_grand_total - $redeemed_amount;

                    if (round($new_grand_total, 2) !== round($validatedData['grand_total'], 2)) {
                        return response()->json(['success' => false, 'message' => 'Invalid grand total.'], Response::HTTP_BAD_REQUEST);
                    }
                    $customer->customerPoint->decrement('available_point', $redeem_points);
                }
            }
            $sale->save();

            $user_id = $customer->id ?? Customer::where('name', 'Ecom Guest Customer')->value('id');
            (new PosService())->activity_track_on_pos_create($sale, $user_id);
            (new PosService())->notification_entry($sale);

            $this->updateProductUnits($request->input('s_product'));

            $this->insertSaleProducts($sale, $request->input('s_product'));

            // Check if a coupon_id is provided and increment the used_count
            if ($validatedData['coupon_id']) {
                $coupon = Coupon::find($validatedData['coupon_id']);
                if ($coupon->coupon_type == 3 && $validatedData['customer_id']) {
                    $customerCoupon = $coupon->customer_coupon->where('customer_id', $validatedData['customer_id'])->first();
                    $customerCoupon->used_count += 1;
                    $customerCoupon->save();
                }
            }

            if ($customer) {
                $this->sendOrderNotification($customer->id, $sale);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Order created successfully.', 'data' => $sale], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Failed to create order.', 'error' => $e->getMessage()], 500);
        }
    }

    public function checkCoupon(Request $request)
    {
        $couponCode = $request->input('coupon_code');
        if (empty($couponCode)) {
            return response()->json(['success' => false, 'message' => 'Coupon code cannot be empty'], Response::HTTP_BAD_REQUEST);
        }
        // Fetch the coupon
        $coupon = Coupon::with('categories', 'customer_coupon')
            ->where('name', $couponCode)
            ->where('status', 1)->first();
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code.'], 400);
        }

        // Check if the coupon is expired
        $endDate = Carbon::parse($coupon->end_date);
        if (Carbon::now()->gt($endDate)) {
            return response()->json(['success' => false, 'message' => 'Coupon has expired'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the coupon type is 2 (category-specific)
        if ($coupon->coupon_type == 2) {
            $productIds = collect($request->input('products'))->pluck('id')->toArray();
            $categoryIds = Product::whereIn('id', $productIds)->pluck('category_id')->unique()->toArray();
            // Get category IDs associated with the coupon
            if (!$coupon->categories->pluck('category.id')->intersect($categoryIds)->count()) {
                return response()->json(['success' => false, 'message' => 'Coupon is not applicable to any of the selected product categories.'], Response::HTTP_BAD_REQUEST);
            }
        }

        // Check if the coupon type is 3 (customer-specific) and limit_count is not exceeded
        if ($coupon->coupon_type == 3) {
            $customerId = request()->input('customer_id');
            if (is_null($customerId)) {
                return response()->json(['success' => false, 'message' => 'Customer needs to be logged in for this coupon'], Response::HTTP_BAD_REQUEST);
            }
            $customerCoupon = $coupon->customer_coupon->where('customer_id', $customerId)->first();
            if (!$customerCoupon) {
                return response()->json(['success' => false, 'message' => 'Coupon is not applicable to this customer'], Response::HTTP_NOT_FOUND);
            }
            // Check if the limit_count is exceeded
            $limitCount = $customerCoupon->limit_count;
            $usedCount = $customerCoupon->used_count;
            if ($limitCount !== null && $usedCount !== null && $usedCount >= $limitCount) {
                return response()->json(['success' => false, 'message' => 'Coupon limit exceeded'], Response::HTTP_BAD_REQUEST);
            }
        }

        return response()->json(['success' => true, 'message' => 'Coupon applied successfully.', 'data' => $coupon], Response::HTTP_OK);
    }

    public function getUserOrders(int $userId)
    {
        return Sale::select('id', 'invoice_no', 'customer_id', 'name', 'phone', 'item', 'total_qty', 'net_total', 'total_discount', 'shipping_cost', 'order_discount', 'order_tax', 'grand_total', 'payment_method', 'sale_date', 'delivery_status', 'information', 'optional_information')
            ->where('customer_id', $userId)
            ->with(
                'saleProductList:sale_id,product_id,product_variant_id,qty,sale_unit_id,net_unit_price,total',
                'saleProductList.product:id,name,image,slug,generic_id',
                'saleProductList.product.generic:generic_name,id,slug',
                'saleProductList.product_variant:discount,id,product_id,price,qty,product_unit_id',
                'saleProductList.product_variant.unit:id,unit_name',
                'saleProductList.productUnits:id,product_id,product_unit_id,price,discount,qty',
                'saleProductList.productUnits.unit:id,unit_name'
            )
            ->orderBy('id', 'desc')
            ->paginate(15);
    }

    public function getorderFeedback(Request $request)
    {
        $validatedData = $request->validate([
            'customer_id' => 'nullable|integer',
            'invoice_no' => 'required',
            'name' => 'required|string',
            'phone' => 'required',
            'email' => 'nullable',
            'type' => 'required|string',
            'feedback' => 'required|string',
        ]);

        $order = Sale::where('invoice_no', $validatedData['invoice_no'])->first();
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found. Please Insert a valid invoice number'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'customer_id' => $validatedData['customer_id'] ?? null,
            'invoice_no' => $validatedData['invoice_no'],
            'name' => $validatedData['name'],
            'phone' => $validatedData['phone'],
            'email' => $validatedData['email'] ?? null,
            'type' => $validatedData['type'],
            'feedback' => $validatedData['feedback'],
            'created_at' => Carbon::now(),
        ];
        CustomerFeedback::insert($data);

        return response()->json(['success' => true, 'message' => 'Feedback submitted successfully'], Response::HTTP_OK);
    }

    public function getDeliveryCharge()
    {
        $deliveryCharge = DeliveryCharge::select('id', 'name', 'value')->where('status', 1)->get();
        return response()->json(['success' => true, 'data' => $deliveryCharge], Response::HTTP_OK);
    }

    protected function generateInvoiceCode()
    {
        $code = 'INV-' . Keygen::numeric(6)->generate();
        if (DB::table('sales')->where('invoice_no', $code)->exists()) {
            $this->generateInvoiceCode();
        } else {
            return $code;
        }
    }

    protected function fillSaleData(Sale $sale, array $data)
    {
        $sale->customer_id = $data['customer_id'];
        $sale->ecom_customer_id = isset($data['customer_id']) ? $data['customer_id'] : 50; // ecom guest customer id is 50
        $sale->item = $data['item'];
        $sale->total_qty = $data['total_qty'];
        $sale->total_discount = $data['total_discount'] ?? null;
        $sale->shipping_cost = $data['shipping_cost'];
        $sale->total_tax = $data['total_discount_amount'] ?? null;
        $sale->total_price = $data['net_total'];
        $sale->net_total = $data['net_total'];
        $sale->grand_total = $data['grand_total'];
        $sale->sale_date = $data['sale_date'];
        $sale->payment_method = 1;
        $sale->name = $data['name'];
        $sale->phone = $data['phone'];
        $sale->information = isset($data['information']) ? json_encode($data['information']) : null;
        $sale->optional_information = $data['optional_information'] ?? null;
        $sale->coupon_id = $data['coupon_id'] ?? null;
        $sale->coupon_discount_value = $data['coupon_discount_value'] ?? null;
        $sale->order_type = 1;
    }

    protected function updateProductUnits(array $products)
    {
        foreach ($products as $product) {
            $p_qty = $product['qty'];
            $productUnit = ProductUnit::where('id', $product['id'])
                ->where('product_id', $product['product_id'])
                ->first();
            if ($productUnit) {
                $productUnit->qty -= $p_qty;
                $productUnit->save();
            }
        }
    }

    protected function insertSaleProducts(Sale $sale, array $products)
    {
        $saleProductData = [];
        foreach ($products as $product) {
            $saleProductData[] = [
                'sale_id' => $sale->id,
                'product_variant_id' => $product['id'],
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
                'sale_unit_id' => $product['sale_unit_id'],
                'net_unit_price' => $product['net_unit_price'],
                'tax_rate' => $product['discount'],
                'discount' => $product['discount'],
                'discount_rate' => $product['discount_rate'],
                'tax' => $product['discount_rate'],
                'total' => $product['total'],
                'order_type' => 1,
            ];
        }
        DB::table('sale_products')->insert($saleProductData);
    }

    // Onesignal notification
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
    //                'include_external_user_ids' => [(string) $userId],
    //                'contents' => ['en' => "Your order #{$sale->invoice_no} has been created!"],
    //                'headings' => ['en' => 'Order Confirmation'],
    //            ],
    //        ]);
    //        info('Notification sent to user: ' . $userId);
    //        return json_decode($response->getBody()->getContents(), true);
    //    }

    private function sendOrderNotification($userId, $sale)
    {
        try {
            $client = new OneSignalClient(config('onesignal.app_id'), config('onesignal.rest_api_key'), null);

            // Send notification
            $client->sendNotificationToExternalUser(
                "Your order #{$sale->invoice_no} has been created!", // Notification content
                (string)$userId, // External User ID
                null, // URL (optional)
                [
                    'headings' => ['en' => 'Order Confirmation'], // Title of the notification
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
}
