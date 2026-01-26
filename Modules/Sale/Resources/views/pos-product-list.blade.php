@if(!$products->isEmpty())
    @for ($i=0; $i < ceil(count($products)/4); $i++) <tr>
        <td width="25%" class="product-img sound-btn text-center" onclick="product_search_click('{{$products[0+$i*4]->code}}','{{$products[0+$i*4]->id}}')" title="{{$products[0+$i*4]->name}}" data-product="{{$products[0+$i*4]->code . ' (' . $products[0+$i*4]->name . ')'}}">
            @if($products[0+$i*4]->image)
                <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$products[0+$i*4]->image)}}" style="width:100%;height:120px;" />
            @else
                <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
            @endif
            <p>{{$products[0+$i*4]->name}}</p>
            <span class="text-primary font-weight-bolder">{{$products[0+$i*4]->code}}</span>
        </td>
        @if(!empty($products[1+$i*4]))
            <td width="25%" class="product-img sound-btn text-center" title="{{$products[1+$i*4]->name}}" onclick="product_search_click('{{$products[1+$i*4]->code}}','{{$products[1+$i*4]->id}}')" data-product="{{$products[1+$i*4]->code . ' (' . $products[1+$i*4]->name . ')'}}">
                @if($products[1+$i*4]->image)
                    <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$products[1+$i*4]->image)}}" style="width:100%;height:120px;" />
                @else
                    <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
                @endif
                <p>{{$products[1+$i*4]->name}}</p>
                <span class="text-primary font-weight-bolder">{{$products[1+$i*4]->code}}</span>
            </td>
        @else
            <td style="border:none;"></td>
        @endif
        @if(!empty($products[2+$i*4]))
            <td width="25%" class="product-img sound-btn text-center" title="{{$products[2+$i*4]->name}}" onclick="product_search_click('{{$products[2+$i*4]->code}}','{{$products[2+$i*4]->id}}')" data-product="{{$products[2+$i*4]->code . ' (' . $products[2+$i*4]->name . ')'}}">
                @if($products[2+$i*4]->image)
                    <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$products[2+$i*4]->image)}}" style="width:100%;height:120px;" />
                @else
                    <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
                @endif
                <p>{{$products[2+$i*4]->name}}</p>
                <span class="text-primary font-weight-bolder">{{$products[2+$i*4]->code}}</span>
            </td>
        @else
            <td style="border:none;"></td>
        @endif
        @if(!empty($products[3+$i*4]))
            <td width="25%" class="product-img sound-btn text-center" title="{{$products[3+$i*4]->name}}" onclick="product_search_click('{{$products[3+$i*4]->code}}','{{$products[3+$i*4]->id}}')" data-product="{{$products[3+$i*4]->code . ' (' . $products[3+$i*4]->name . ')'}}">
                @if($products[3+$i*4]->image)
                    <img src="{{asset('storage/'.PRODUCT_IMAGE_PATH.$products[3+$i*4]->image)}}" style="width:100%;height:120px;" />
                @else
                    <img src="{{ asset('images/product.svg') }}" style="width:100%;height:120px;">
                @endif
                <p>{{$products[3+$i*4]->name}}</p>
                <span class="text-primary font-weight-bolder">{{$products[3+$i*4]->code}}</span>
            </td>
        @else
            <td style="border:none;"></td>
        @endif
    </tr>
    @endfor
@endif
