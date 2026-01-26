<?php

namespace Modules\Sale\Http\Controllers\Service;

use App\Models\Unit;
use App\Services\SaleInvoiceContainer;
use Illuminate\Support\Facades\Auth;
use Modules\Product\Entities\ProductUnit;
use Modules\Sale\Entities\SaleNotification;

class PosService
{

    public function activity_track_on_pos_create($pos, $auth_id)
    {
        return $pos->user_activity()->create([
            'activity_type' => 'sale_create',
            'status_name' => 'Created',
            'user_id' => $auth_id
        ]);
    }

    public function notification_entry($pos)
    {
        return SaleNotification::create([
            'sale_id' => $pos->id,
            'invoice' => $pos->invoice_no,
            'order_source' => $pos->order_source_id,
            'is_seen' => 0
        ]);
    }

    public function pos_entry_data_maker($request, $customer, $total_item, $total_qty)
    {
        $adjustment_per = 0;
        $order_discount_per = 0;

        $invoice_code = (new SaleInvoiceContainer())->invoice_code_provider();
        $invoice_no = 'INV-' . $invoice_code;
        $pos_data = [
            'invoice_no' => $invoice_no,
            'date_wise_serial' => $invoice_code,

            'type' => 1,
            'customer_id' => $request->customer_id,
            'ecom_customer_id' => $request->customer_id,
            'item' => $total_item,
            'total_qty' => $total_qty,
            'total_discount' => $request->total_discount,
            'total_tax' => $request->total_tax,
            'total_price' => $request->total_price,
            'net_total' => $request->total_price,
            'order_tax_rate' => $request->order_tax_rate,
            'order_tax' => $request->order_tax,
            'order_discount_per' => $order_discount_per,
            'order_discount' => $request->order_discount ?? 0,
            'order_discount_rate' => $request->order_discount_rate ?? 0,
            'adjustment_per' => $adjustment_per,
            'adjustment' => $request->adjustment ?? 0,
            'shipping_cost' => $request->shipping_cost ?? 0,
            'grand_total' => $request->grand_total,
            'paid_amount' => $request->paid_amount ?? 0,
            'due_amount' => $request->due_amount ?? 0,
            'payment_status' => $request->payment_status,
            'account_id' => 23,
            'information' => $customer->information,
            'optional_information' => $customer->optional_information,
            'order_source_id' => $request->order_source_id,
            'sale_date' => date('Y-m-d'),
            'delivery_status' => 1,
            'order_type' => 2,
            'created_by' => Auth::user()->name
        ];
        return $pos_data;
    }

    public function pos_product_data_maker($request)
    {
        $products = [];
        $total_item = 0;
        $total_qty = 0;

        if ($request->has('products')) {
            foreach ($request->products as $value) {
                $total_item++;

                $unit = Unit::where('unit_name', $value['unit'])->first();
                $total_qty += $value['qty'];

                $products[] = [
                    'product_id' => $value['id'],
                    'product_variant_id' => $value['sale_product_id'],
                    'serial_no' => $value['serial_no'],
                    'qty' => $value['qty'],
                    'sale_unit_id' => $unit ? $unit->id : null,
                    'net_unit_price' => $value['net_unit_price'],
                    'discount' => $value['discount'],
                    'discount_rate' => $value['discount_rate'],
                    'total' => $value['subtotal'],
                    'order_type' => 2,
                    'created_at' => date('Y-m-d g:i:s')
                ];
                $p_id = $value['id'];
                $p_qty = $value['qty'];

                $product = ProductUnit::where('product_unit_id', $unit->id)->where('product_id', $p_id)->first();
                $data = [
                    'qty' => $product->qty - $p_qty,
                ];
                $result = ProductUnit::where('product_unit_id', $unit->id)->where('product_id', $p_id)->update($data);
            }
        }
        return [$products, $total_item, $total_qty];
    }

    public function payment_maker($request)
    {
        $payments = [];
        if ($request->has('payment') && $request->payment_status != 3) {
            foreach ($request->payment as $payment_list) {
                $payments[] = [
                    'payment_method' => $payment_list['payment_method'],
                    'account_id' => $payment_list['account_id'] ?? 0,
                    'reference_no' => $payment_list['reference_no'] ?? 0,
                    'paid_amount' => $payment_list['payment_amount']
                ];
            }
        }
        return $payments;
    }
}
