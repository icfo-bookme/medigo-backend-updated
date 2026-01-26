<?php

namespace Modules\Product\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Product\Entities\Product;
use App\Http\Controllers\BaseController;
use Modules\Product\Http\Requests\BarcodeFormRequest;

class BarcodeController extends BaseController
{

    public function index()
    {
        if(permission('print-barcode-access')){
            $this->setPageData('Print Barcode','Print Barcode','fas fa-barcode',[['name'=>'Product','link'=> route('product')],['name' => 'Print Barcode']]);
            return view('product::barcode.index');
        }else{
            return $this->access_blocked();
        }
    }

    public function generateBarcode(BarcodeFormRequest $request)
    {
        if($request->ajax())
        {
            $data = [
                'barcode'           => $request->product_code,
                'product_name'      => $request->product_name,
                'variant_name'      => $request->variant_name,
                'product_price'     => $request->product_price,
                'barcode_symbology' => $request->barcode_symbology,
                'tax_rate'          => $request->tax_rate,
                'tax_method'        => $request->tax_method,
                'barcode_qty'       => $request->barcode_qty,
                'row_qty'           => $request->row_qty
            ];
            return view('product::barcode.print-area',$data)->render();
        }
    }

    public function autocomplete_search_product(Request $request)
    {
        if(!empty($request->search)){
            $output = array();
            $search_text = $request->search;
            $products = Product::with('tax')->where(function($q) use($search_text){
                $q->where('code', 'like','%'.$search_text.'%')
                ->orWhere('name', 'like','%'.$search_text.'%');
            })->get();

            if(!$products->isEmpty())
            {
                $temp_array = [];
                foreach ($products as $value) {
                    $temp_array['code']              = $value->code;
                    $temp_array['name']              = $value->name;
                    $temp_array['price']             = $value->price;
                    $temp_array['barcode_symbology'] = $value->barcode_symbology;
                    $temp_array['tax_rate']          = $value->tax->rate ? $value->tax->rate : 0;
                    $temp_array['tax_method']        = $value->tax_method;
                    $temp_array['value']             = $value->code.' - '.$value->name;
                    $temp_array['label']             = $value->code.' - '.$value->name;
                    $output[] = $temp_array;
                }
            }
            if(empty($output) && count($output) == 0)
            {
                $output['value'] = '';
                $output['label'] = 'No Record Found';
            }
            return $output; 
        }
    }


}
