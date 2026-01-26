<?php

namespace Modules\Sale\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductUnit;
use Modules\Product\Entities\ProductVariant;

class ProductController extends BaseController
{
    public function product_autocomplete_search(Request $request){
        if($request->ajax()) {
            if(!empty($request->search)){
                $output      = array();
                $search_text = $request->search;
                $temp_array  = [];
                $product_variations = ProductUnit::join('products as p','product_units.product_id','=','p.id')
                                    ->leftjoin('brands as b', 'p.brand_id', '=','b.id')
                                    ->leftjoin('units as unt', 'product_units.product_unit_id', '=','unt.id')
                                    ->selectRaw('product_units.*,p.name,p.brand_id,b.name as brand_name,unt.id,unt.unit_name')
                                    ->where('product_units.qty', '>=',0)
                                    ->where(function($q) use($search_text){
                                        $q->where('product_units.item_code', 'like','%'.$search_text.'%')
                                            ->orWhere('p.name', 'like','%'.$search_text.'%');
                                    })->get();
                if(!$product_variations->isEmpty()) {
                    foreach ($product_variations as $value) {
                        $temp_array['code']  = $value->item_code;
                        $temp_array['value'] = $value->item_code.' - '.$value->name.' ('.$value->unit_name.')'.' - '.$value->price.($value->brand_id ? ' - ['.$value->brand_name.']' : '');
                        $temp_array['label'] = $value->item_code.' - '.$value->name.' ('.$value->unit_name.')
                        '.' - Stock ('.($value->qty).' QTY)
                          - Raw Price('.($value->price).' Tk)
                         - Sale price('.($value->price - ($value->discount / 100) * $value->price).'Tk)'.
                            ($value->brand_id ? ' - ['.$value->brand_name.']' : '');
                        $output[] = $temp_array;
                    }
                }

                if(empty($output) && count($output) == 0) {
                    $output['value'] = '';
                    $output['label'] = 'No Record Found';
                }
                return $output;
            }
        }
    }

    public function product_search(Request $request) {
        if($request->ajax()) {
            $product_data = Product::with('brand')->where('code',$request->data)->first();
            if(!$product_data) {
                $product_data = Product::join('product_units', 'products.id', '=','product_units.product_id')
                    ->leftjoin('brands as b', 'products.brand_id', '=','b.id')
                    ->select('products.*', 'product_units.id as variant_id',
                        'product_units.item_code','product_units.price as item_price','product_units.qty as item_qty','b.name as brand_name','product_units.product_unit_id','product_units.discount')
                    ->where('product_units.item_code', $request->data)
                    ->where('product_units.qty', '>=', 0)
                    ->first();
            }
            if($product_data){
                $product['id']         = $product_data->id;
                $product['name']       = $product_data->name.' - '.($product_data->brand_id ? ' ['.$product_data->brand_name.']' : '');
                $product['code']       = $product_data->item_code;
                $product['variant_id'] = $product_data->variant_id;
                $product['price']      = $product_data->item_price;
                $product['qty']        = $product_data->item_qty;
                $product['discount']   = $product_data->discount;


                $product['tax_rate']   = $product_data->tax->rate ? $product_data->tax->rate : 0;
                $product['tax_name']   = $product_data->tax->name;
                $product['tax_method'] = $product_data->tax_method;

                $units = Unit::where('id',$product_data->product_unit_id)->get();
                $unit_name            = [];
                $unit_operator        = [];
                $unit_operation_value = [];
                if($units) {
                    foreach ($units as $unit) {
                            $unit_name           [] = $unit->unit_name;
                            $unit_operator       [] = $unit->operator;
                            $unit_operation_value[] = $unit->operation_value;
                    }
                }
                $product['unit_name'] = implode(',',$unit_name);
                $product['unit_operator'] = implode(',',$unit_operator).',';
                $product['unit_operation_value'] = implode(',',$unit_operation_value).',';
                return $product;
            }
        }
    }
}
