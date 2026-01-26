<?php

namespace App\Http\Controllers\Api\Service;


use Illuminate\Support\Facades\Auth;
use Modules\Sale\Entities\AssignDeliveryMan;
use Modules\Sale\Entities\Sale;

class DeliveryManService
{
    protected $basicRelations = [
        'sale:id,invoice_no,customer_id,item,total_qty,net_total,total_discount,order_tax,shipping_cost,order_discount,grand_total,payment_method,delivery_status,information,optional_information',
        'sale.customer:id,name,mobile',
        'sale.saleProductList:id,sale_id,product_id,qty,sale_unit_id,net_unit_price,total',
        'sale.saleProductList.unit:id,unit_name',
        'sale.saleProductList.product:id,name,image'
    ];

    public function getAssignedProducts()
    {
        return $this->getProductsByDeliveryStatus([1, 3, 4]);
    }

    public function updateProductStatus($saleId, $status, $date)
    {
        Sale::where('id', $saleId)->update([
            'delivery_status' => $status,
//            'delivery_date' => date('Y-m-d H:i:s'),
            'delivery_date' => $date,
        ]);
    }

    public function getProductsByDeliveryStatus($statuses)
    {
        return AssignDeliveryMan::select('id', 'delivery_man_id', 'sale_id', 'date')
            ->with($this->basicRelations)
            ->whereHas('sale', function ($query) use ($statuses) {
                $query->whereIn('delivery_status', $statuses);
            })
            ->where('delivery_man_id', Auth::id())
            ->get();
    }
}
