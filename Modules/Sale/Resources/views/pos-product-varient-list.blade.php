@if(!$products_varient->isEmpty())
@for ($i=0; $i < ceil(count($products_varient)/4); $i++) <tr>
    <td width="25%" class="product-img sound-btn text-center" onclick="product_search_click('{{$products_varient[0+$i*4]->item_code}}')" title="{{$products_varient[0+$i*4]->item_name}}" data-product="{{$products_varient[0+$i*4]->item_code . ' (' . $products_varient[0+$i*4]->item_name . ')'}}">
        @if($product->image)
            <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}" style="width:100%;height:120px;" />
        @else
            <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
        @endif

        <p>{{$products_varient[0+$i*4]->item_name}}</p>
        <span class="text-primary font-weight-bolder">{{$products_varient[0+$i*4]->item_code}} <br> {{$products_varient[0+$i*4]->unit_name}} <br> {{$products_varient[0+$i*4]->price-(($products_varient[0+$i*4]->discount / 100)*$products_varient[0+$i*4]->price)}}tk</span>

    </td>

    @if(!empty($products_varient[1+$i*4]))
    <td width="25%" class="product-img sound-btn text-center" title="{{$products_varient[1+$i*4]->name}}" onclick="product_search_click('{{$products_varient[1+$i*4]->item_code}}')" data-product="{{$products_varient[1+$i*4]->item_code . ' (' . $products_varient[1+$i*4]->item_name . ')'}}">

        @if($product->image)
            <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}" style="width:100%;height:120px;" />
        @else
            <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
        @endif
        <p>{{$products_varient[1+$i*4]->item_name}}</p>
        <span class="text-primary font-weight-bolder">{{$products_varient[1+$i*4]->item_code}} <br> {{$products_varient[1+$i*4]->unit_name}} <br> {{$products_varient[1+$i*4]->price-(($products_varient[1+$i*4]->discount / 100)*$products_varient[1+$i*4]->price)}}tk</span>

    </td>
    @else
    <td style="border:none;"></td>
    @endif


    @if(!empty($products_varient[2+$i*4]))
    <td width="25%" class="product-img sound-btn text-center" title="{{$products_varient[2+$i*4]->item_name}}" onclick="product_search_click('{{$products_varient[2+$i*4]->item_code}}')" data-product="{{$products_varient[2+$i*4]->item_code . ' (' . $products_varient[2+$i*4]->item_name . ')'}}">

        @if($product->image)
            <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}" style="width:100%;height:120px;" />
        @else
            <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
        @endif
        <p>{{$products_varient[2+$i*4]->item_name}}</p>
        <span class="text-primary font-weight-bolder">{{$products_varient[2+$i*4]->item_code}} <br>{{$products_varient[2+$i*4]->unit_name}} <br> {{$products_varient[2+$i*4]->price-(($products_varient[2+$i*4]->discount / 100)*$products_varient[2+$i*4]->price)}}tk</span>
    </td>
    @else
    <td style="border:none;"></td>
    @endif

    @if(!empty($products_varient[3+$i*4]))
    <td width="25%" class="product-img sound-btn text-center" title="{{$products_varient[3+$i*4]->item_name}}" onclick="product_search_click('{{$products_varient[3+$i*4]->item_code}}')" data-product="{{$products_varient[3+$i*4]->item_code . ' (' . $products_varient[3+$i*4]->item_name . ')'}}">
        @if($product->image)
            <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}" style="width:100%;height:120px;" />
        @else
            <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
        @endif
        <p>{{$products_varient[3+$i*4]->item_name}}</p>
        <span class="text-primary font-weight-bolder">{{$products_varient[3+$i*4]->item_code}} <br> {{$products_varient[3+$i*4]->unit_name}} <br> {{$products_varient[3+$i*4]->price-(($products_varient[3+$i*4]->discount / 100)*$products_varient[3+$i*4]->price)}}tk</span>

    </td>
    @else
    <td style="border:none;"></td>
    @endif
    </tr>
    @endfor
    @endif
