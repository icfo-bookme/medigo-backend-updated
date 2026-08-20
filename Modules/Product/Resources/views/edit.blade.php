@extends('layouts.app')
@section('title', $page_title)
@push('styles')
<link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css" />

<!-- select2 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet" />

<!-- select2-bootstrap4-theme -->
<link href="https://raw.githack.com/ttskch/select2-bootstrap4-theme/master/dist/select2-bootstrap4.css" rel="stylesheet"> <!-- for live demo page -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">

<style>
    .tagsinput{
        height: calc(1.5em + 1.3rem + 2px) !important;
    }

    .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
        color: #495057;
        height: 25px;
    }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background: #8950FC;
        padding: 3px 3px !important;
        color: white !important;
    }


</style>


@endpush
@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                <div class="card-toolbar">
                    <a href="{{ route('product') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
            </div>
        </div>
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body">
                <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-10">
                            <div class="row">
                                <input type="hidden" name="update_id" id="update_id" value="{{ $product->id }}">   <input type="hidden" name="update_id" id="update_id" value="{{ $product->id }}">
                                <x-form.textbox labelName="Product Name" name="name" required="required" value="{{ $product->name }}" col="col-md-6" placeholder="Enter product name"/>
                                <x-form.selectbox labelName="Generic Name" name="generic_id" required="required" col="col-md-6" class="selectpicker">
                                    @if (!$generic->isEmpty())
                                        @foreach ($generic as $g_key => $g_row)
                                            <option value="{{ $g_key }}" {{$g_key == $product->generic_id ? 'selected' : ''}}>{{ $g_row
                                            }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Company Name" name="brand_id" col="col-md-6" class="selectpicker">
                                    @if (!$brands->isEmpty())
                                        @foreach ($brands as $b_key =>  $brand)
                                            <option value="{{ $b_key }}" {{ $product->brand_id == $b_key ? 'selected' : '' }}>{{ $brand }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Category" name="category_id" required="required" col="col-md-6" class="selectpicker">
                                    @if (!$categories->isEmpty())
                                        @foreach ($categories as $c_key => $category)
                                            <option value="{{ $c_key }}"  {{ $product->category_id == $c_key ? 'selected' : '' }}>{{ $category }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>

                                 <div class="form-group col-md-12">
                                    <label for="" class="col-md-3 col-from-label">YouTube Video Link</label>
                                    <div class="col-md-12">
                                          <x-form.textbox labelName="YouTube Video Link" type="text" name="yt_video" id="yt_video" class="form-control" placeholder="YouTube Video Link (Optional)" value="{{ $product->yt_video }}" />
                                    </div>
                                </div>
                               
                              


{{--                                <div class="form-group col-md-12')}}">
{{--                                    @php--}}

{{--                                        $allSimilarProductIds = $product->similar_product_list->pluck('similar_product_id')->flatten()->toArray();--}}

{{--                                    @endphp--}}
{{--                                    <label class="col-from-label">Similar Product</label>--}}
{{--                                    <select class="form-control js-example-basic-multiple" name="similar_product_id[]" id="similar_product_id" data-live-search="true" required multiple>--}}
{{--                                        @if (!$products->isEmpty())--}}
{{--                                            @foreach ($products as $item_product)--}}
{{--                                                <option value="{{ $item_product->id }}"      {{ in_array($item_product->id, $allSimilarProductIds) ? 'selected' : '' }} >{{ $item_product->name }}</option>--}}
{{--                                            @endforeach--}}
{{--                                        @endif--}}
{{--                                    </select>--}}
{{--                                </div>--}}



                                <div class="form-group col-md-6">
                                    <label for="medical_overview" class="col-md-3 col-from-label">Medical Overview</label>
                                    <div class="col-md-12">
                                        <textarea class="summernote"  name="medical_overview" >{!! $product->medical_overview !!}</textarea>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="quick_tips" class="col-md-3 col-from-label">Quick Tips</label>
                                    <div class="col-md-12">
                                        <textarea class="summernote"  name="quick_tips" >{!! $product->quick_tips !!}</textarea>
                                    </div>
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="brief_description" class="col-md-3 col-from-label">Brief Description</label>
                                    <div class="col-md-12">
                                        <textarea class="summernote"  name="brief_description" >{!! $product->brief_description !!}</textarea>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="disclaimer" class="col-md-3 col-from-label">Disclaimer</label>
                                    <div class="col-md-12">
                                        <textarea class="summernote" name="disclaimer" >{!! $product->disclaimer !!}</textarea>
                                    </div>
                                </div>


                                <div class="form-group col-md-6">
                                    <label for="indication" class="col-md-3 col-from-label">Indication</label>
                                    <div class="col-md-12">
                                        <textarea class="summernote" name="indication" >{!! $product->indication !!}</textarea>
                                    </div>
                                </div>


                                <div class="col-md-6" style="padding-top: 31px;">
                                    <input type="checkbox" id="product_type" name="product_type" value="2" {{  ($product->product_type == 2 ? ' checked' : '') }} style="height: 23px; width: 39px;">
                                    <label for="vehicle1"> Home Section Product</label><br>
                                </div>

                               

                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="row">
                                <div class="form-group col-md-12 mb-0 text-center">
                                    <label for="logo" class="form-control-label">Product Image</label>
                                    <div class="col=md-12 px-0  text-center">
                                        <div id="image"></div>
                                        <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB and Format (png,jpg,jpeg,svg,webp)</span></div>
                                    </div>
                                    <input type="hidden" name="old_image" id="old_image" value="{{ $product->image }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="form-group col-md-12 pt-5"><button type="button" class="btn btn-primary btn-sm" id="update-btn">Update</button></div>
                    </div>
                </form>
            </div>
            <div class="card card-custom">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered" >
                                <thead class="bg-primary">
                                <tr class="text-center">
                                    <th>Unit</th>
                                    <th>Item code</th>
                                    <th>Price</th>
                                    <th>Discount (%)</th>
                                    <th>Alert Qty</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php

                                    $unit_product = \Modules\Product\Entities\ProductUnit::where('product_id',$product->id)->get();

                                @endphp
                                @foreach($unit_product as $row)


                                    @php

                                        $unit= \App\Models\Unit::where('id',$row->product_unit_id)->first();
                                    @endphp
                                    <form action="{{route('product.unit.details.update')}}" method="post" >
                                        @csrf
                                        <input type="hidden" class="form-control" name="update_id" required="required" value="{{$row->id}}">
                                        <tr class="text-center" >
                                            <td>
                                                <div class="input-group" id="code_section">
                                                    <input type="text" class="form-control text-center"  name="unit_name" required="required" readonly value="{{$unit->unit_name}}">
                                                </div>
                                            </td>

                                            <td>
                                                <div class="input-group" id="code_section">
                                                    <input type="text" class="form-control text-center" name="item_code" required="required" value="{{$row->item_code}}">
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-center" id="price" name="price" value="{{$row->price}}"/>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control text-center" id="discount" name="discount" value="{{$row->discount}}"/>
                                            </td>

                                            <td>
                                                <input type="text" class="form-control text-center" id="alert_qty" name="alert_qty" value="{{$row->alert_qty}}"/>
                                            </td>
                                            <td>
                                                <button type="submit" class="btn btn-primary btn-sm "><i class="fas fa-plus-circle"></i>Update</button><br/>
                                            </td>
                                        </tr>
                                    </form>
                                @endforeach
                                </tbody>
                            </table>
                            <form method="post" action="{{route('product.unit.details.update')}}">
                                @csrf
                                <table class="table table-bordered" id="unitTable">
                                    <input type="hidden" class="form-control" name="product_id" value="{{$product->id}}"/>
                                    <tr>
                                        <td style="width: 15.5%;">
                                            <select class="form-control" name="product_unit_id">
                                                @foreach($units as $u_key => $u_row)
                                                    <option value="{{ $u_key }}">{{ $u_row }}</option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td>
                                            <input type="text" class="form-control" name="item_code" required="required" placeholder="Item code">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="price" name="price" placeholder="sale price"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="discount" name="discount" placeholder="discount"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="alert_qty" name="alert_qty" placeholder="alert qty"/>
                                        </td>
                                        <td>
                                        <td class="">
                                            <button type="submit" class="btns btn-primary btn-sm mr-3"  id="save-btn-1" style=""><i class="far fa-save"></i> Save </button>
                                        </td>

                                    </tr>
                                </table>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="{{asset('js/moment.js')}}"></script>
<script src="{{asset('js/spartan-multi-image-picker.min.js')}}"></script>
<script src="{{asset('js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script>

<script>
$(document).ready(function () {

    $('.summernote').summernote({
        placeholder: '',
        tabsize: 2,
        height: 250
    });



    $('.js-example-basic-multiple').each(function () {
        $(this).select2({
            placeholder: " Select ",
            theme: 'bootstrap4',
            width: 'style',
            allowClear: Boolean($(this).data('allow-clear')),
        });
    });


    $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});
    /** Start :: Product Image **/
    $("#image").spartanMultiImagePicker({
        fieldName:        'image',
        maxCount: 1,
        rowHeight:        '150px',
        groupClassName:   'col-md-12 col-sm-12 col-xs-12',
        maxFileSize:      '',
        dropFileLabel : "Drop Here",
        allowedExt: '',
    });
    $("input[name='image']").prop('required',true);
    $('.remove-files').on('click', function(){
        $(this).parents(".col-md-12").remove();
    });
    @if(!empty($product->image))
    $('#image img').css('display','none');
    $('#image .spartan_remove_row').css('display','block');
    $('#image .img_').css('display','block');
    $('#image .img_').attr('src',"{{ asset('storage/'.PRODUCT_IMAGE_PATH.$product->image)}}");
    @else
    $('#image img').css('display','block');
    $('#image .spartan_remove_row').css('display','none');
    $('#image .img_').css('display','none');
    $('#image .img_').attr('src','');
    @endif
    /** End :: Product Image **/
    //Generate Code
    $(document).on('click','#generate-code',function(){
        $.ajax({
            url: "{{ route('product.generate.code') }}",
            type: "GET",
            dataType: "JSON",
            beforeSend: function(){
                $('#generate-code').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#generate-code').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                data ? $('#store_or_update_form #code').val(data) : $('#store_or_update_form #code').val('');
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    });
    $(document).on('click','#offer',function() {
        if($(this).is(':checked')){
            $(this).val(1);
            $('.offer-section').removeClass('d-none');
        }else{
            $(this).val(2);
            $('.offer-section').addClass('d-none');
            $('#offer_price,#start_date,#end_date').val('');
        }
    });
    /****************************/
    $(document).on('click','#update-btn',function(){
        let form = document.getElementById('store_or_update_form');
        let formData = new FormData(form);
        $.ajax({
            url: "{{route('product.update')}}",
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#update-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#update-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value){
                        var key = key.split('.').join('_');
                        $('#store_or_update_form input#' + key).addClass('is-invalid');
                        $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                        $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                        if(key == 'code' || key == 'start_date' || key == 'end_date'){
                            $('#store_or_update_form #' + key).parents('.form-group').append('<small class="error text-danger">' + value + '</small>');
                        }else{
                            $('#store_or_update_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                        }
                    });
                } else {
                    notification(data.status, data.message);
                    {{--if (data.status == 'success') { window.location.replace("{{ route('product') }}"); }--}}

                }
            },
            error: function (xhr, ajaxOption, thrownError) { console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
        });
    });
});
</script>

@endpush
