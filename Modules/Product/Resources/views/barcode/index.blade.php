@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
@endpush

@section('content')
<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--begin::Button-->
                    <a href="{{ route('product') }}" class="btn btn-warning btn-sm font-weight-bolder"> 
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom" style="padding-bottom: 100px !important;">
            <div class="card-body" style="padding-bottom: 100px !important;">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer pb-5">
                    
                    <form id="generate-barcode-form" method="POST">
                        @csrf
                        <div class="row">
                            <input type="hidden" class="form-control" name="product_name" id="product_name">
                            <input type="hidden" class="form-control" name="product_code" id="product_code">
                            <input type="hidden" class="form-control" name="product_price" id="product_price">
                            <input type="hidden" class="form-control" name="barcode_symbology" id="barcode_symbology">
                            <input type="hidden" class="form-control" name="tax_rate" id="tax_rate">
                            <input type="hidden" class="form-control" name="tax_method" id="tax_method">
                            <div class="form-group col-md-12">
                                <label for="product_code_name">Select Product</label>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="product_code_name" id="product_code_name" placeholder="Please type product code and select...">
                                </div>
                            </div>
                            <x-form.textbox labelName="No of Barcode" name="barcode_qty" required="required" col="col-md-2" class="text-center" value="1" placeholder="Enter barcode print quantity"/>
                            <x-form.textbox labelName="Qunatity Each Row " name="row_qty" required="required" col="col-md-2" class="text-center" value="1" placeholder="Enter barcode print quantity"/>
                            <div class="form-group col-md-2">
                                <label for="">Print With</label>
                                <div class="div">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="pname">
                                        <label class="custom-control-label" for="pname">Product Name</label>
                                    </div>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="price">
                                        <label class="custom-control-label" for="price">Price</label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-3" style="padding-top:28px;">
                                <button type="button" class="btn btn-primary btn-sm" id="generate-barcode"><i class="fas fa-barcode"></i>Generate Barcode</button>
                            </div>
                        </div>
                    </form>
                    
                    <div class="row" id="barcode-section">
                        {{-- <div class="col-md-2 barcode text-center py-5">
                            {!! DNS1D::getBarcodeHTML('8941166000003', 'EAN13'); !!}
                        </div> --}}
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@endsection

@push('scripts')
<script src="js/jquery-ui.js"></script>
<script src="js/jquery.printarea.js"></script>
<script>
$(document).ready(function () {
    $('#product_code_name').autocomplete({
        // source: "{{url('product-autocomplete-search')}}",
        source: function( request, response ) {
          // Fetch data
          $.ajax({
            url:"{{url('barcode/product-autocomplete-search')}}",
            type: 'post',
            dataType: "json",
            data: {
               _token: _token,
               search: request.term
            },
            success: function( data ) {
               response( data );
            }
          });
        },
        minLength: 3,
        response: function(event, ui) {
            if (ui.content.length == 1) {
                // var data = ui.content[0].code;
                $('#product_code_name').val(ui.content[0].value)
                $('#product_name').val(ui.content[0].name);
                $('#product_code').val(ui.content[0].code);
                $('#product_price').val(ui.content[0].price);
                $('#barcode_symbology').val(ui.content[0].barcode_symbology);
                $('#tax_rate').val(ui.content[0].tax_rate);
                $('#tax_method').val(ui.content[0].tax_method);
                $(this).autocomplete( "close" );
            };
        },
        select: function (event, ui) {
            $('#product_code_name').val(ui.item.value)
            $('#product_name').val(ui.item.name);
            $('#product_code').val(ui.item.code);
            $('#product_price').val(ui.item.price);
            $('#barcode_symbology').val(ui.item.barcode_symbology);
            $('#tax_rate').val(ui.item.tax_rate);
            $('#tax_method').val(ui.item.tax_method);

        },
    }).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $("<li class='ui-autocomplete-row'></li>")
            .data("item.autocomplete", item)
            .append(item.label)
            .appendTo(ul);
    };
    $(document).on('click','#generate-barcode',function(){
        var product_code      = $('#product_code').val();
        var barcode_symbology = $('#barcode_symbology').val();
        var tax_rate          = $('#tax_rate').val();
        var tax_method        = $('#tax_method').val();
        var product_name      = '';
        var product_price     = '';
        var barcode_qty       = $('#barcode_qty').val();
        var row_qty           = $('#row_qty').val();
        if($('#pname').prop("checked") == true){
            product_name = $('#product_name').val();
        }
        if($('#price').prop("checked") == true){
            product_price = $('#product_price').val();
        }

        $.ajax({
            url: "{{ route('generate.barcode') }}",
            type: "POST",
            data: {product_code:product_code,product_name:product_name,product_price:product_price,
                barcode_symbology:barcode_symbology,tax_rate:tax_rate,tax_method:tax_method,
                barcode_qty:barcode_qty,row_qty:row_qty,_token:_token},
            beforeSend: function(){
                $('#generate-barcode').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#generate-barcode').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#generate-barcode-form').find('.is-invalid').removeClass('is-invalid');
                $('#generate-barcode-form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#generate-barcode-form input#' + key).addClass('is-invalid');
                        $('#generate-barcode-form textarea#' + key).addClass('is-invalid');
                        $('#generate-barcode-form select#' + key).parent().addClass('is-invalid');
                        $('#generate-barcode-form #' + key).parent().append(
                        '<small class="error text-danger">' + value + '</small>');
                    });
                } 
                if(data) {
                    $('#barcode-section').html('');
                    $('#barcode-section').html(data);
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    });

    $(document).on('click','#print-barcode',function(){
        var mode = 'iframe'; // popup
        var close = mode == "popup";
        var options = {
            mode: mode,
            popClose: close
        };
        $("#printableArea").printArea(options);
    });
});
</script>
@endpush