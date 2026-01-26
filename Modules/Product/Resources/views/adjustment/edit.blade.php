@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link rel="stylesheet" href="css/jquery-ui.css" />
<link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
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
                    <a href="{{ route('adjustment') }}" class="btn btn-warning btn-sm font-weight-bolder">
                        <i class="fas fa-arrow-left"></i> Back</a>
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <form action="" id="update_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="adjustment_id" value="{{ $adjustment->id }}">

                            <div class="form-group col-md-4 required">
                                <label for="adjustment_no">Adjustment No.</label>
                                <input type="text" class="form-control" name="adjustment_no" id="adjustment_no" value="{{ $adjustment->adjustment_no }}"  />
                            </div>

                            @if(Auth::user()->warehouse_id)
                            <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ $adjustment->warehouse_id }}">
                            @else
                            <x-form.selectbox labelName="Showroom" name="warehouse_id" required="required" col="col-md-4" class="selectpicker">
                              @if (!$warehouses->isEmpty())
                                  @foreach ($warehouses as $value)
                                  <option value="{{ $value->id }}" {{ $adjustment->warehouse_id == $value->id ? 'selected' : '' }}>{{ $value->name }}</option>
                                  @endforeach
                              @endif
                            </x-form.selectbox>
                            @endif

                            <div class="form-group col-md-12">
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                    <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
                                    </div>
                                    <input type="text" class="form-control" name="product_code_name" id="product_code_name" placeholder="Scan/Search by product name/code">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <table class="table table-bordered" id="product_table">
                                    <thead class="bg-primary">
                                        <th width="40%">Name</th>
                                        <th  width="15%" class="text-center">Code</th>
                                        <th width="10%" class="text-center">Unit</th>
                                        <th width="10%" class="text-center">Quantity</th>
                                        <th width="15%" class="text-center">Action</th>
                                        <th width="10%"></th>
                                    </thead>
                                    <tbody>
                                        @php
                                            $temp_unit_name = [];
                                        @endphp
                                        @if (!$adjustment->products->isEmpty())
                                            @foreach ($adjustment->products as $key => $adjustment_product)
                                                <tr>
                                                    @php
                                                        $tax = DB::table('taxes')->where('rate',$adjustment_product->tax_rate)->first();
                                                        $code = DB::table('product_units')->where('product_id',$adjustment_product->id)->select('id', 'product_id', 'product_unit_id', 'item_code')->first();
                                                        $units = DB::table('units')->where('id', $code->product_unit_id)->get();

                                                        $unit_name            = [];

                                                        if($units){
                                                            foreach ($units as $unit) {
                                                                array_unshift($unit_name,$unit->unit_name);
                                                            }
                                                            $temp_unit_name = $unit_name = implode(",",$unit_name).',';
                                                        }
                                                    @endphp
                                                    <td>{{ $adjustment_product->name }}</td>

                                                    <td class="text-center">{{ $code->item_code }}</td>
                                                    <td class="unit-name text-center"></td>
                                                    <td><input type="text" class="form-control qty text-center" name="products[{{ $key+1 }}][qty]" id="products_{{ $key+1 }}_qty" value="{{ $adjustment_product->pivot->qty }}"></td>

                                                    <td>
                                                        <select class="form-control" name="products[{{ $key+1 }}][action]" id="products_{{ $key+1 }}_action">
                                                            <option value="+" {{ $adjustment_product->pivot->action == '+' ? 'selected' : '' }}>Addition</option>
                                                            <option value="-" {{ $adjustment_product->pivot->action == '-' ? 'selected' : '' }}>Subtraction</option>
                                                        </select>
                                                        </td>
                                                    <td class="text-center"> <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button></td>
                                                    <input type="hidden" class="product-id" name="products[{{ $key+1 }}][id]"  value="{{ $adjustment_product->id }}">
                                                    <input type="hidden" class="product-code" name="products[{{ $key+1 }}][code]" value="{{ $code->item_code }}">
                                                    <input type="hidden" class="adjustment-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot class="bg-primary">
                                        <th colspan="3" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ $adjustment->total_qty }}</th>
                                        <th colspan="2"></th>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="form-group col-md-12">
                                <label for="shipping_cost">Note</label>
                                <textarea  class="form-control" name="note" id="note" cols="30" rows="3">{{ $adjustment->note }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <input type="hidden" name="total_qty" value="{{ $adjustment->total_qty }}">
                                <input type="hidden" name="item" value="{{ $adjustment->item }}">
                            </div>
                            <div class="form-grou col-md-12 text-center pt-5">
                                <a href="{{ route('adjustment') }}" class="btn btn-danger btn-sm mr-3"><i class="far fa-window-close"></i> Cancel</a>
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Update</button>
                            </div>
                        </div>
                    </form>
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
<script>
$(document).ready(function () {

    //array data depend on warehouse
    var product_array = [];
    var product_code  = [];
    var product_name  = [];
    var product_qty   = [];
    var unit_name            = [];


    //temporary array
    var temp_unit_name            = [];

    var rowindex;
    $('#product_code_name').autocomplete({
        // source: "{{url('product-autocomplete-search')}}",
        source: function( request, response ) {
          // Fetch data
          $.ajax({
            url:"{{url('sale/product-autocomplete-search')}}",
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
                var data = ui.content[0].code;
                $(this).autocomplete( "close" );
                productSearch(data);
            };
        },
        select: function (event, ui) {
            // $('.product_search').val(ui.item.value);
            // $('.product_id').val(ui.item.id);
            var data = ui.item.value;
            productSearch(data);
        },
    }).data('ui-autocomplete')._renderItem = function (ul, item) {
        return $("<li class='ui-autocomplete-row'></li>")
            .data("item.autocomplete", item)
            .append(item.label)
            .appendTo(ul);
    };

    var rownumber = $('#product_table tbody tr:last').index();
    for (rowindex = 0; rowindex <= rownumber; rowindex++) {
        temp_unit_name = $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.adjustment-unit').val().split(',');
        console.log(temp_unit_name);
        unit_name.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.adjustment-unit').val());
        $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.adjustment-unit').val(temp_unit_name[0]);
        $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.unit-name').text(temp_unit_name[0]);
    }

    $('#product_table').on('keyup','.qty',function(){
        rowindex = $(this).closest('tr').index();
        if($(this).val() < 1 && $(this).val() != ''){
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(1);
            notification('error','Qunatity can\'t be less than 1');
        }

        checkQuantity($(this).val(),true);
    });

    $('#product_table').on('click','.remove-product',function(){
        rowindex = $(this).closest('tr').index();
        unit_name.splice(rowindex,1);
        $(this).closest('tr').remove();
        calculateTotal();
    });

    @if (!$adjustment->products->isEmpty())
    var count = "{{ count($adjustment->products) + 1 }}" ;
    @else
    var count = 1;
    @endif
    function productSearch(data) {
        $.ajax({
            url: '{{ url("sale/product-search") }}',
            type: 'POST',
            data: {
                data: data,_token:_token,warehouse_id:document.getElementById('warehouse_id').value
            },
            success: function(data) {
                var flag = 1;
                $('.product-code').each(function(i){
                    if($(this).val() == data.code){
                        rowindex = i;
                        var qty = parseFloat($('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val()) + 1;
                        $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(qty);
                        calculateProductData(qty);
                        flag = 0;
                    }
                });
                $('#product_code_name').val('');
                if(flag)
                {
                    temp_unit_name = data.unit_name.split(',');
                    var newRow = $('<tr>');
                    var cols = '';
                    cols += `<td>`+data.name+`</td>`;

                    cols += `<td class="text-center">`+data.code+`</td>`;
                    cols += `<td class="unit-name text-center"></td>`;
                    cols += `<td><input type="text" class="form-control qty text-center" name="products[`+count+`][qty]"
                        id="products_`+count+`_qty" value="1"></td>`;
                    cols += `<td>
                        <select class="form-control" name="products[`+count+`][action]" id="products_`+count+`_action">
                            <option value="+">Addition</option>
                            <option value="-">Subtraction</option>
                        </select>
                        </td>`;

                    cols += `<td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button></td>`;
                    cols += `<input type="hidden" class="product-id" name="products[`+count+`][id]"  value="`+data.id+`">`;
                    cols += `<input type="hidden"  name="products[`+count+`][name]" value="`+data.name+`">`;
                    cols += `<input type="hidden" class="product-code" name="products[`+count+`][code]" value="`+data.code+`">`;
                    cols += `<input type="hidden" class="product-unit" name="products[`+count+`][unit]" value="`+temp_unit_name[0]+`">`;

                    newRow.append(cols);
                    $('#product_table tbody').append(newRow);
                    unit_name.push(data.unit_name);
                    rowindex = newRow.index();
                    calculateProductData(1);
                    count++;
                }

            }
        });
    }

    function checkQuantity(adjustment_qty,flag)
    {
        var row_product_code = $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
        var pos = product_code.indexOf(row_product_code);
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(adjustment_qty);
        calculateProductData(adjustment_qty);

    }

    function calculateProductData(quantity){
        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.unit-name').text(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));
        calculateTotal();
    }

    function calculateTotal()
    {
        var total_qty = 0;
        $('.qty').each(function() {
            if($(this).val() == ''){
                total_qty += 0;
            }else{
                total_qty += parseFloat($(this).val());
            }
        });
        $('#total-qty').text(total_qty);
        $('input[name="total_qty"]').val(total_qty);
        $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
    }


});

function store_data(){
    var rownumber = $('table#product_table tbody tr:last').index();
    if (rownumber < 0) {
        notification("error","Please insert product to order table!")
    }else{
        let form = document.getElementById('update_form');
        let formData = new FormData(form);
        let url = "{{route('adjustment.update')}}";
        $.ajax({
            url: url,
            type: "POST",
            data: formData,
            dataType: "JSON",
            contentType: false,
            processData: false,
            cache: false,
            beforeSend: function(){
                $('#save-btn').addClass('spinner spinner-white spinner-right');
            },
            complete: function(){
                $('#save-btn').removeClass('spinner spinner-white spinner-right');
            },
            success: function (data) {
                $('#update_form').find('.is-invalid').removeClass('is-invalid');
                $('#update_form').find('.error').remove();
                if (data.status == false) {
                    $.each(data.errors, function (key, value) {
                        var key = key.split('.').join('_');
                        $('#update_form input#' + key).addClass('is-invalid');
                        $('#update_form textarea#' + key).addClass('is-invalid');
                        $('#update_form select#' + key).parent().addClass('is-invalid');
                        $('#update_form #' + key).parent().append(
                            '<small class="error text-danger">' + value + '</small>');
                    });
                } else {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        window.location.replace("{{ route('adjustment') }}");

                    }
                }

            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }

}
</script>
@endpush
