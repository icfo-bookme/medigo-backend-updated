<?php

namespace App\Jobs;

use App\Http\Controllers\Api\OrderController;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Keygen\Keygen;
use Modules\Sale\Entities\Sale;

class AutoOrder implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $invoice = null;
    private $pack = null;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($pack_item) {
        $this->pack = $pack_item;

        $this->invoice = $this->product_variant_generate();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {

        try {

            DB::beginTransaction();


            $sale = Sale::create(['invoice_no'           => $this->invoice,
                                  'customer_id'          => $this->pack->user_id,
                                  'item'                 => $this->pack->item,
                                  'total_qty'            => $this->pack->total_qty,
                                  'total_discount'       => $this->pack->total_discount,
                                  'shipping_cost'        => $this->pack->shipping_cost,
                                  'total_price'          =>  $this->pack->net_total,
                                  'net_total'            => $this->pack->net_total,
                                  'grand_total'          => $this->pack->grand_total,
                                  'sale_date'            => date('Y-m-d'),
                                  'payment_method'       => 1,
                                  'name'                 => isset($this->pack->customer->name) ? $this->pack->customer->name : '',
                                  'phone'                => isset($this->pack->customer->mobile) ? $this->pack->customer->mobile : '',
                                  'information'          => isset($this->pack->customer->information) ? $this->pack->customer->information : '',
                                  'optional_information' => isset($this->pack->customer->optional_information) ? $this->pack->customer->optional_information : '',
                                  'order_type'           => 3]);


            $saleProductData = [];
            foreach ($this->pack->productsList as $product) {
                $saleProductData[] = [
                    'sale_product_id' => $product->sale_product_id,
                    'product_id'      => $product->product_id,
                    'qty'             => $product->qty,
                    'sale_unit_id'    => $product->sale_unit_id,
                    'net_unit_price'  => $product->net_unit_price,
                    'tax_rate'        => $product->tax_rate,
                    'discount'        => $product->discount,
                    'tax'             => $product->tax,
                    'total'           => $product->total,
                    'order_type'      => 3
                ];
            }

            if (count($saleProductData) > 0) {

                $sale->products()->sync($saleProductData);

            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }

    }

    public  function product_variant_generate()
    {
        $code = 'INV-';
        $code .=Keygen::numeric(6)->generate();
        //Check Code ALready Exist or Not
        if (DB::table('sales')->where('invoice_no', $code)->exists()) {
            $this->product_variant_generate();
        }else {
            return $code;
        }
    }

}
