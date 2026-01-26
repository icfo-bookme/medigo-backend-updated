<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Service\OrderService;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends BaseController
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function order(Request $request)
    {
        return $this->orderService->createOrder($request);
    }

    public function couponCheck(Request $request)
    {
        return $this->orderService->checkCoupon($request);
    }

    public function orderList()
    {
        $orderList = $this->orderService->getUserOrders(Auth::guard('customer')->id());
        return response()->json(['success' => true, 'data' => $orderList]);
    }

    public function orderFeedback(Request $request)
    {
        return $this->orderService->getorderFeedback($request);
    }

    public function deliveryCharge()
    {
        return $this->orderService->getDeliveryCharge();
    }
}
