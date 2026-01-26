<?php

namespace Modules\Customer\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Modules\Customer\Entities\Customer;

class CustomerPointController extends BaseController
{
    public function __construct(Customer $model)
    {
        $this->model = $model;
    }
    public function getCustomerPoint(Request $request)
    {
        $customer = $this->model->findOrFail($request->customer_id);
        return response()->json(
            [
                'status' => 'success',
                'customer_point' => $customer->customerPoint,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
            ]
        );
    }

    public function setCustomerPoint(Request $request)
    {
        $customer = $this->model->findOrFail($request->customer_id);
        $customer->customerPoint()->updateOrCreate(
            ['customer_id' => $request->customer_id],
            [
                'available_point' => $request->available_point,
                'min_use_point' => $request->min_use_point,
                'conversion_rate' => $request->conversion_rate,
            ]
        );
        return response()->json(['status' => 'success', 'message' => 'Customer point updated successfully']);
    }
}
