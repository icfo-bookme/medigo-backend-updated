<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Service\DeliveryManService;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class DeliveryManController extends BaseController
{
    protected $deliveryManService;

    public function __construct(DeliveryManService $deliveryManService)
    {
        $this->deliveryManService = $deliveryManService;
    }

    public function assignedProduct(Request $request)
    {
        try {
            $products = $this->deliveryManService->getAssignedProducts();

            return response()->json(['data' => $products]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assigned products',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function assignedProductStatus(Request $request)
    {
        $this->deliveryManService->updateProductStatus($request->sale_id, $request->delivery_status, $request->delivery_date);

        return response()->json([
            'success' => true,
            'message' => "Status Successfully Changed!"
        ]);
    }

    public function onDeliveryProductStatus()
    {
        return $this->getProductsByDeliveryStatus(5);
    }

    public function deliveredProductStatus()
    {
        return $this->getProductsByDeliveryStatus(2);
    }

    public function cancelProductStatus()
    {
        return $this->getProductsByDeliveryStatus(6);
    }

    protected function getProductsByDeliveryStatus($status)
    {
        try {
            $products = $this->deliveryManService->getProductsByDeliveryStatus($status);

            return response()->json([
                'success' => true,
                'data' => $products,
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to retrieve products with delivery status $status",
                'error' => $e->getMessage()
            ]);
        }
    }
}
