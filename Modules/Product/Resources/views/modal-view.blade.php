@php use Modules\Product\Entities\ProductUnit; @endphp
@php use Modules\Product\Entities\Product; @endphp
@php use App\Models\Unit; @endphp
<div class="card card-custom " style="padding-bottom: 100px !important;">
    <div class="card-body">
        <div class="row">
            @if (!empty($product->image))
                <div class="col-md-4">
                    <img src="{{ asset('storage/'.PRODUCT_IMAGE_PATH.$product->image) }}" alt="{{ $product->name }}" style="width: 100%;">
                </div>
            @else
                <div class="col-md-4">
                    <img src="{{ asset('images/product.svg') }}" alt="{{ $product->name }}" style="width: 100%;">
                </div>
            @endif
            <div class="col-md-8">
                <table class="table table-borderless table-hover">
                    <tr>
                        <td width="25%"><b>Name</b></td>
                        <td width="2%" class="text-center"><b>:</b></td>
                        <td><b>{{ $product->name }}</b></td>
                    </tr>
                    <tr>
                        <td width="25%"><b>Brand</b></td>
                        <td width="2%" class="text-center"><b>:</b></td>
                        <td>{{ $product->brand->name }}</td>
                    </tr>
                    <tr>
                        <td width="25%"><b>Category</b></td>
                        <td width="2%" class="text-center"><b>:</b></td>
                        <td>{{ $product->category->name }}</td>
                    </tr>
                    {{--                                <tr><td width="25%"><b>Tax</b></td> <td  width="2%" class="text-center"><b>:</b></td> <td>{{ $product->tax->rate }}%</td></tr>--}}
                </table>
            </div>
        </div>
        <div class="card card-custom mt-5">
            <div class="card-header">
                <div class="card-toolbar">
                    <ul class="nav nav-bold nav-pills">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#description">Description</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link " data-toggle="tab" href="#variants">Product Package</a>
                        </li>

                    </ul>
                </div>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description">
                        <div class="table-responsive padding-top-10px">

                            @if(isset($product->indication))
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <th class="text-center">Indication</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="text-left"><b>{!! $product->indication !!}</b></td>
                                    </tr>
                                    </tbody>
                                </table>
                                </br>
                            @endif

                            @if(isset($product->medical_overview))
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <th class="text-center">Medical Overview</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="text-left"><b>{!! $product->medical_overview !!}</b></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <br/>
                            @endif




                            @if(isset($product->quick_tips))
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <th class="text-center">Quick Tips</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="text-left"><b>{!! $product->quick_tips !!}</b></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <br/>
                            @endif

                            @if(isset($product->brief_description))
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <th class="text-center">Brief Description</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="text-left"><b>{!! $product->brief_description !!}</b></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <br/>
                            @endif


                            @if(isset($product->disclaimer))
                                <table class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <th class="text-center">Disclaimer</th>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td class="text-left"><b>{!! $product->disclaimer !!}</b></td>
                                    </tr>
                                    </tbody>
                                </table>
                                <br/>
                            @endif

                            <br/>

                        </div>
                    </div>

                    <div class="tab-pane fade" id="variants" role="tabpanel" aria-labelledby="variants">
                        <div class="table-responsive padding-top-10px">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Item Code</th>
                                <th class="text-center">Product Unit</th>
                                <th class="text-center">Stock Quantity</th>
                                <th class="text-center">Price</th>
                                </thead>
                                <tbody>
                                @php
                                    $p_details = ProductUnit::where('product_id',$product->id)->get();
                                @endphp
                                @foreach($p_details as $row)
                                    @php
                                        $product   = Product::where('id',$row->product_id)->first();
                                        $u_details = Unit::where('id',$row->product_unit_id)->first();
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{$product->name }}</td>
                                        <td class="text-center">{{$row->item_code }}</td>
                                        <td class="text-center">{{$u_details->unit_name}}</td>
                                        <td class="text-center">{{$row->qty}}</td>
                                        <td class="text-center">{{$row->price}} /-TK</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="similar_product" role="tabpanel" aria-labelledby="similar_product">
                        <div class="table-responsive padding-top-10px">
                            <table class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                <th class="text-center">Product Name</th>
                                <th class="text-center">Generic Name</th>
                                <th class="text-center">Category</th>
                                </thead>
                                <tbody>

                                @foreach($product->similar_product_list as $item)
                                    <tr>
                                        <td class="text-center">{{$item->product->name }}</td>
                                        <td class="text-center">{{$item->product->generic->generic_name}}</td>
                                        <td class="text-center">{{$item->product->category->name}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
