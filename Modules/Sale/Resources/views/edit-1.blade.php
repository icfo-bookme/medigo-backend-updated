@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <style>
        .small-btn{
            width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
        }
        .small-btn i{font-size: 10px !important;}
    </style>
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
                        <a href="{{ route('sale') }}" class="btn btn-warning btn-sm font-weight-bolder">
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
                        <form id="sale_update_form" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="sale_id" id="sale_id" value="{{ $sale->id }}">
                                <input type="hidden" name="customer_id_hidden" value="{{ $sale->customer_id }}">
                                <input type="hidden" name="payment_status_hidden" value="{{ $sale->payment_status }}">
                                <input type="hidden" name="order_tax_rate_hidden" value="{{ $sale->order_tax_rate }}">

                                <div class="form-group col-md-3 required">
                                    <label for="invoice_no">Invoice No.</label>
                                    <input type="text" class="form-control" name="invoice_no" id="invoice_no" value="{{ $sale->invoice_no }}"  />
                                </div>
                                <div class="form-group col-md-3 required">
                                    <label for="sale_date">Sale Date</label>
                                    <input type="text" class="form-control date" name="sale_date" id="sale_date" value="{{ $sale->sale_date }}" readonly />
                                </div>
                                <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" required="required" class="selectpicker">
                                    @if (!$customers->isEmpty())
                                        @foreach ($customers as $value)
                                            <option value="{{ $value->id }}">{{ $value->name.($value->mobile ? ' ('.$value->mobile.') ' : '') }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Delivery Status" name="delivery_status" required="required"  col="col-md-3" class="selectpicker">
                                    @foreach (DELIVERY_STATUS as $key => $value)
                                        @if($key != 2)
                                            <option value="{{ $key }}" {{ $sale->delivery_status == $key ? 'selected' :'' }}>{{ $value }}</option>
                                        @endif
                                    @endforeach
                                </x-form.selectbox>



                                {{--                                <div class="form-group col-md-4">--}}
                                {{--                                    <label for="document">Attach Document</label>--}}
                                {{--                                    <input type="file" class="form-control" name="document" id="document">--}}
                                {{--                                </div>--}}

                                <div class="form-group col-md-12">
                                    <label for="product_code_name">Select Product</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
                                        </div>

                                        <input type="text" class="form-control" name="product_code_name" id="product_code_name" placeholder="Please type product code and select...">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="product_table">
                                        <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">Serial No.</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Available Qty</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Net Unit Price</th>
                                        <th class="text-right">Discount</th>
                                        <th class="text-right">Tax</th>
                                        <th class="text-right">Subtotal</th>
                                        <th></th>
                                        </thead>
                                        <tbody>
                                        @php
                                            $temp_unit_name = [];
                                            $temp_unit_operator = [];
                                            $temp_unit_operation_value = [];
                                        @endphp
                                        @if (!$sale->sale_products->isEmpty())
                                            @foreach ($sale->sale_products as $key => $sale_product)
                                                <tr>
                                                    @php
                                                        $tax = DB::table('taxes')->where('rate',$sale_product->tax_rate)->first();

                                                        $units = DB::table('units')->where('id',$sale_product->sale_unit_id)->get();

                                                        if($sale_product->sale_product_id){
                                                            $stock_qty = $sale_product->product_variant->qty + $sale_product->delivered;
                                                        }else{
                                                            $stock_qty =  $sale_product->delivered;
                                                        }
                                                        $unit_name            = [];
                                                        $unit_operator        = [];
                                                        $unit_operation_value = [];

                                                        if($units){
                                                            foreach ($units as $unit) {
                                                                if($sale_product->sale_unit_id == $unit->id)
                                                                {
                                                                    array_unshift($unit_name,$unit->unit_name);
                                                                    array_unshift($unit_operator,$unit->operator);
                                                                    array_unshift($unit_operation_value,$unit->operation_value);
                                                                }else{
                                                                    $unit_name           [] = $unit->unit_name;
                                                                    $unit_operator       [] = $unit->operator;
                                                                    $unit_operation_value[] = $unit->operation_value;
                                                                }
                                                            }

                                                            if($sale_product->tax_method == 1){
                                                                $product_price = ($sale_product->net_unit_price + ($sale_product->discount / $sale_product->qty));
                                                            }else{
                                                                $product_price = (($sale_product->total / $sale_product->qty) + ($sale_product->discount / $sale_product->qty));
                                                            }

                                                            if($unit_operator[0] == '*')
                                                            {
                                                                $product_price = $product_price * $unit_operation_value[0];
                                                            }else if($unit_operator[0] == '/')
                                                            {
                                                                $product_price = $product_price / $unit_operation_value[0];
                                                            }

                                                            $temp_unit_name = $unit_name = implode(",",$unit_name).',';
                                                            $temp_unit_operator = $unit_operator = implode(",",$unit_operator).',';
                                                            $temp_unit_operation_value = $unit_operation_value = implode(",",$unit_operation_value).',';
                                                        }
                                                    @endphp
                                                    <td>{{ !empty($sale_product->product_variant_id) ? $sale_product->product->name.' - ('.$sale_product->product_variant->item_name.')'.($sale_product->product->brand_id ? ' - ['.$sale_product->product->brand->name.']' : '') : $sale_product->product->name.($sale_product->product->brand_id ? ' - ['.$sale_product->product->brand->name.']' : '') }}</td>

                                                    <td class="text-center">{{ $sale_product->product_variant->item_code }}</td>
                                                    <td><input type="text" class="form-control product-serial-no" name="products[{{ $key+1 }}][serial_no]" id="products_{{ $key+1 }}_serial_no" value="{{ $sale_product->serial_no }}"></td>

                                                    <td class="unit-name text-center">{{$unit_name}}</td>
                                                    <td><input type="text" class="stock-qty form-control text-center" name="products[{{ $key+1 }}][stock_qty]"  value="{{ $stock_qty }}" readonly></td>
                                                    <td><input type="text" class="form-control qty text-center" name="products[{{ $key+1 }}][qty]"
                                                               id="products_{{ $key+1 }}_qty" value="{{ $sale_product->qty }}"></td>

                                                    <td><input type="text" readonly class="net_unit_price form-control text-right" name="products[{{ $key+1 }}][net_unit_price]" value="{{ $sale_product->net_unit_price }}"></td>
                                                    <td class="discount text-right">{{ number_format((float)$sale_product->discount, 2, '.','') }}</td>
                                                    <td class="tax text-right">{{ number_format((float)$sale_product->tax, 2, '.','') }}</td>
                                                    <td class="sub-total text-right">{{ number_format((float)$sale_product->total, 2, '.','') }}</td>
                                                    <td class="text-center d-flex">
                                                        <button type="button" class="btn btn-danger btn-sm remove-product small-btn" style="padding: 1px 19px 19px 9px !important;font-size: 11px;border-radius: 6px;"><i class="fas fa-trash"></i></button>
                                                    </td>

                                                    <input type="hidden" class="product-id" name="products[{{ $key+1 }}][id]"  value="{{ $sale_product->product_id }}">
                                                    <input type="hidden" class="product-varaint-id" name="products[{{ $key+1 }}][variant_id]"  value="{{ $sale_product->product_variant->id }}">

                                                    <input type="hidden" class="product-code" name="products[{{ $key+1 }}][code]" value="{{ !empty($sale_product->product_variant_id) ? $sale_product->product_variant->item_code :  $sale_product->product->code }}">
                                                    <input type="hidden" class="product-price" name="products[{{ $key+1 }}][price]" value="{{ $product_price }}">
                                                    <input type="hidden" class="sale-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" class="sale-unit-operator"  value="{{ $unit_operator }}">
                                                    <input type="hidden" class="sale-unit-operation-value"  value="{{ $unit_operation_value }}">

                                                    <input type="hidden" class="discount-value" name="products[{{ $key+1 }}][discount]" value="{{ $sale_product->discount }}">
                                                    <input type="hidden" class="tax-rate" name="products[{{ $key+1 }}][tax_rate]" value="{{ $sale_product->tax_rate }}">
                                                    @if ($tax)
                                                        <input type="hidden" class="tax-name" value="{{ $tax->name }}">
                                                    @else
                                                        <input type="hidden" class="tax-name" value="No Tax">
                                                    @endif
                                                    <input type="hidden" class="tax-method" value="{{ $sale_product->tax_method }}">
                                                    <input type="hidden" class="tax-value" name="products[{{ $key+1 }}][tax]" value="{{ $sale_product->tax }}">
                                                    <input type="hidden" class="subtotal-value" name="products[{{ $key+1 }}][subtotal]" value="{{ $sale_product->total }}">

                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                        <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ $sale->total_qty }}</th>
                                        <th></th>
                                        <th id="total-discount" class="text-right font-weight-bolder">{{ number_format($sale->total_discount,2,'.',',') }}</th>
                                        <th id="total-tax" class="text-right font-weight-bolder">{{ number_format($sale->total_tax,2,'.',',') }}</th>
                                        <th id="total" class="text-right font-weight-bolder">{{ number_format($sale->total_price,2,'.',',') }}</th>
                                        <th></th>
                                        </tfoot>
                                    </table>
                                </div>
                                <div class="form-group col-md-8 text-right mt-7">Order Tax: </div>

                                <x-form.selectbox labelName="" name="order_tax_rate" col="col-md-4" class="selectpicker">
                                    <option value="0" selected>No Tax</option>
                                    @if (!$taxes->isEmpty())
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>

                                <div class="form-group col-md-8 text-right">Order Discount:</div>
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control" name="order_discount" id="order_discount" value="{{ $sale->order_discount }}">
                                </div>
                                <div class="form-group col-md-8 text-right">Shipping Cost:</div>
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ $sale->shipping_cost }}">
                                </div>
                                <input type="hidden" class="form-control" name="payment_status" id="payment_status" value="1">
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="bg-primary">
                                        <th><strong>Items</strong><span class="float-right" id="item">0.00</span></th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">0.00</span></th>
                                        <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">0.00</span></th>
                                        <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">0.00</span></th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">0.00</span></th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">0.00</span></th>
                                        </thead>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <input type="hidden" name="total_qty" value="{{ $sale->total_qty }}">
                                    <input type="hidden" name="total_discount" value="{{ $sale->total_discount }}">
                                    <input type="hidden" name="total_tax" value="{{ $sale->total_tax }}">
                                    <input type="hidden" name="total_price" value="{{ $sale->total_price }}">
                                    <input type="hidden" name="item" value="{{ $sale->item }}">
                                    <input type="hidden" name="order_tax" value="{{ $sale->order_tax }}">
                                    <input type="hidden" name="grand_total" value="{{ $sale->grand_total }}">
                                </div>
                                <div class="payment col-md-12 @if($sale->payment_status == 3) d-none @endif">
                                    <div class="row">
                                        <div class="form-group col-md-4 required">
                                            <input type="hidden" class="form-control" name="net_total" id="net_total" value="{{ ($sale->total_price ) }}" readonly>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-grou col-md-12 text-center pt-5">
                                    <a href="{{ url('sale') }}" class="btn btn-danger btn-sm mr-3"><i class="far fa-window-close"></i> Cancel</a>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="update_data()"><i class="fas fa-save"></i> Update</button>
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
    <!-- Start :: Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
        <div class="modal-dialog" role="document">

            <!-- Modal Content -->
            <div class="modal-content">

                <!-- Modal Header -->
                <div class="modal-header bg-primary">
                    <h3 class="modal-title text-white" id="model-title"></h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close text-white"></i>
                    </button>
                </div>
                <!-- /modal header -->
                <form id="edit_form" method="post">
                    @csrf
                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="row">
                            <x-form.textbox labelName="Quantity" name="edit_qty" required="required" col="col-md-12"/>
                            <x-form.textbox labelName="Unit Discount" name="edit_discount" col="col-md-12"/>
                            <x-form.textbox labelName="Unit Price" name="edit_unit_price" col="col-md-12" readonly/>
                            @php
                                $tax_name_all[] = 'No Tax';
                                $tax_rate_all[] = 0;
                                foreach ($taxes as $tax) {
                                    $tax_name_all[] = $tax->name;
                                    $tax_rate_all[] = $tax->rate;
                                }
                            @endphp
                            <div class="form-group col-md-12">
                                <label for="edit_tax_rate">Tax Rate</label>
                                <select name="edit_tax_rate" id="edit_tax_rate" class="form-control selectpicker">
                                    @foreach ($tax_name_all as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="edit_unit">Product Unit</label>
                                <select name="edit_unit" id="edit_unit" class="form-control selectpicker"></select>
                            </div>
                        </div>
                    </div>
                    <!-- /modal body -->

                    <!-- Modal Footer -->
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary btn-sm" id="update-btn">Update</button>
                    </div>
                    <!-- /modal footer -->
                </form>
            </div>
            <!-- /modal content -->

        </div>
    </div>
    <!-- End :: Edit Modal -->
@endsection

@push('scripts')
    <script src="js/jquery-ui.js"></script>
    <script src="js/bootstrap-datetimepicker.min.js"></script>
    <script>
        //array data depend on warehouse
        var product_array = [];
        var product_code  = [];
        var product_name  = [];
        var product_qty   = [];

        // array data with selection
        var product_price        = [];
        var product_discount    = [];
        var tax_rate             = [];
        var tax_name             = [];
        var tax_method           = [];
        var unit_name            = [];
        var unit_operator        = [];
        var unit_operation_value = [];

        //temporary array
        var temp_unit_name            = [];
        var temp_unit_operator        = [];
        var temp_unit_operation_value = [];

        var rowindex;
        var customer_group_rate;
        var row_product_price;
        $(document).ready(function () {
            // $('.date').datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true});

            $('#product_code_name').on('input',function(){
                var customer_id  = $('#customer_id option:selected').val();
                var temp_data = $('#product_code_name').val();
                if(!customer_id){
                    $('#product_code_name').val(temp_data.substring(0,temp_data.length - 1));
                    notification('error','Please select customer');
                }
            });

            var rownumber = $('#product_table tbody tr:last').index();

            for (rowindex = 0; rowindex <= rownumber; rowindex++) {

                product_price.push(parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.product-price').val()));
                var total_discount = parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('td:nth-child(8)').text())
                var quantity = parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.qty').val())
                product_discount.push((total_discount/quantity).toFixed(2));
                product_qty.push(parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.stock-qty').val()));
                tax_rate.push(parseFloat($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-rate').val()));
                tax_name.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-name').val());
                tax_method.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.tax-method').val());
                temp_unit_name = $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit').val().split(',');
                unit_name.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit').val());
                unit_operator.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit-operator').val());
                unit_operation_value.push($('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit-operation-value').val());
                $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.sale-unit').val(temp_unit_name[0]);
                $('#product_table tbody tr:nth-child('+ (rowindex + 1) +')').find('.unit-name').text(temp_unit_name[0]);
            }

            //assigning value
            $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
            $('select[name="customer_id"]').val($('input[name="customer_id_hidden"]').val());
            $('select[name="payment_status"]').val($('input[name="payment_status_hidden"]').val());
            $('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
            $('.selectpicker').selectpicker('refresh');

            // $('#item').text($('input[name="item"]').val() + '('+$('input[name="total_qty"]').val()+')');
            $('#item').text($('input[name="item"]').val() + '('+$('input[name="total_qty"]').val()+')');
            $('#subtotal').text(parseFloat($('input[name="total_price"]').val()).toFixed(2));
            $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed(2));

            if(!$('input[name="order_discount"]').val())
            {
                $('input[name="order_discount"]').val('0.00');
            }
            $('#order_total_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed(2));
            if(!$('input[name="shipping_cost"]').val())
            {
                $('input[name="shipping_cost"]').val('0.00');
            }
            $('#shipping_total_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed(2));

            $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));


            var cid = $('input[name="customer_id_hidden"]').val();
            $.get('{{ url("customer/group-data") }}/'+cid,function(data){
                customer_group_rate = (data/100);
            });

            $('#customer_id').on('change',function(){
                var id = $(this).val();
                $.get('{{ url("customer/group-data") }}/'+id,function(data){
                    customer_group_rate = (data/100);
                });
                $.get('{{ url("customer/previous-balance") }}/'+id,function(data){
                    $('#previous_due').val(parseFloat(data).toFixed(2));
                });
            });

            $('#product_code_name').autocomplete({
                // source: "{{url('finish-goods-autocomplete-search')}}",
                source: function( request, response ) {
                    // Fetch data
                    $.ajax({
                        url:"{{url('sale/product-autocomplete-search')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            _token: _token,
                            search: request.term,

                        },
                        success: function( data ) {
                            response( data );
                        }
                    });
                },
                // minLength: 3,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        var data = ui.content[0].code;
                        $(this).autocomplete( "close" );
                        product_search(data);
                    };
                },
                select: function (event, ui) {
                    var data = ui.item.code;
                    product_search(data);
                },
            }).data('ui-autocomplete')._renderItem = function (ul, item) {
                return $("<li class='ui-autocomplete-row'></li>")
                    .data("item.autocomplete", item)
                    .append(item.label)
                    .appendTo(ul);
            };

            //Edit Product
            $('#product_table').on('click','.edit-product', function(){
                rowindex = $(this).closest('tr').index();
                edit();
            });

            //Update Edit Product Data
            $('#update-btn').on('click',function(){
                var edit_discount  = $('#edit_discount').val();
                var edit_qty       = $('#edit_qty').val();
                var edit_unit_price = $('#edit_unit_price').val();

                if(parseFloat(edit_discount) > parseFloat(edit_unit_price))
                {
                    notification('error','Invalid discount input');
                    return;
                }

                if(edit_qty < 1)
                {
                    $('#edit_qty').val(1);
                    edit_qty = 1;
                    notification('error','Quantity can\'t be less than 1');
                }

                var row_unit_operator = unit_operator[rowindex].slice(0,unit_operator[rowindex].indexOf(','));
                var row_unit_operation_value = unit_operation_value[rowindex].slice(0,unit_operation_value[rowindex].indexOf(','));
                row_unit_operation_value = parseFloat(row_unit_operation_value);
                var tax_rate_all = <?php echo json_encode($tax_rate_all); ?>;

                tax_rate[rowindex] = parseFloat(tax_rate_all[$('#edit_tax_rate option:selected').val()]);
                tax_name[rowindex] = $('#edit_tax_rate option:selected').text();

                if(row_unit_operator == '*')
                {
                    product_price[rowindex] = $('#edit_unit_price').val() / row_unit_operation_value;
                }else{
                    product_price[rowindex] = $('#edit_unit_price').val() * row_unit_operation_value;
                }

                product_discount[rowindex] = $('#edit_discount').val();
                var position = $('#edit_unit').val();
                var temp_operator = temp_unit_operator[position];
                var temp_operation_value = temp_unit_operation_value[position];
                $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.purchase-unit').val(temp_unit_name[position]);
                temp_unit_name.splice(position,1);
                temp_unit_operator.splice(position,1);
                temp_unit_operation_value.splice(position,1);

                temp_unit_name.unshift($('#edit_unit option:selected').text());
                temp_unit_operator.unshift(temp_operator);
                temp_unit_operation_value.unshift(temp_operation_value);

                unit_name[rowindex] = temp_unit_name.toString() + ',';
                unit_operator[rowindex] = temp_unit_operator.toString() + ',';
                unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
                checkQuantity(edit_qty,false);
            });
            $('#product_table').on('keyup','.qty',function(){
                rowindex = $(this).closest('tr').index();
                // if($(this).val() < 1 && $(this).val() != ''){
                //     $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val(1);
                //     notification('error','Qunatity can\'t be less than 1');
                // }
                checkQuantity($(this).val(),true,input=2);
            });
            $('#product_table').on('keyup','.net_unit_price',function(){
                rowindex = $(this).closest('tr').index();
                if($(this).val() < 1 && $(this).val() != ''){
                    $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .net_unit_price').val(1);
                    notification('error','Net unit price can\'t be less than 1');
                }else{
                    product_price[rowindex] = $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .net_unit_price').val();
                }
                var qty = $('#product_table tbody tr:nth-child('+(rowindex + 1)+') .qty').val();
                if(qty > 0){
                    checkQuantity(qty,true,input=1);
                }

            });

            $('#product_table').on('click','.remove-product',function(){
                rowindex = $(this).closest('tr').index();
                product_price.splice(rowindex,1);
                product_discount.splice(rowindex,1);
                tax_rate.splice(rowindex,1);
                tax_name.splice(rowindex,1);
                tax_method.splice(rowindex,1);
                unit_name.splice(rowindex,1);
                unit_operator.splice(rowindex,1);
                unit_operation_value.splice(rowindex,1);
                $(this).closest('tr').remove();
                calculateTotal();
            });

        });
        {{--            @if (!$sale->sale_products->isEmpty())--}}
        {{--            var count = "{{ count($sale->sale_products) + 1 }}" ;--}}
        {{--            @else--}}
        var count = 100;
        {{--            @endif--}}
        function product_search(data) {
            $.ajax({
                url: '{{ route("sale.product.search") }}',
                type: 'POST',
                data: {
                    data: data,
                    _token: _token,
                    warehouse_id: $('#warehouse_id option:selected').val()
                },
                success: function(data) {
                    var flag = 1;
                    $('.product-code').each(function(i) {
                        console.log($(this).val());
                        if ($(this).val() == data.code) {
                            rowindex = i;
                            var qty = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
                            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                            checkQuantity(String(qty), true, input = 2);
                            flag = 0;
                        }
                    });
                    $('#product_code_name').val('');
                    if (flag) {
                        temp_unit_name = data.unit_name.split(',');
                        var newRow = $('<tr>');
                        var cols = '';
                        cols += `<td>` + data.name + `<input type="hidden" name="products[` + count + `][name]" value="` + data.name + `"></td>`;
                        cols += `<td class="text-center">` + data.code + `</td>`;
                        cols += `<td class="text-center"><input type="text" class="form-control" name="products[` + count + `][serial_no]" id="products_` + count + `_serial_no"></td>`;
                        cols += `<td class="unit-name text-center"></td>`;
                        cols += `<td><input type="text" class="form-control text-center stock-qty" name="products[` + count + `][stock_qty]"  value="` + data.qty + `" readonly></td>`;
                        cols += `<td><input type="text" class="form-control qty text-center" name="products[` + count + `][qty]"
                        id="products_` + count + `_qty" value="1"></td>`;
                        cols += `<td><input type="text" class="form-control text-right net_unit_price" name="products[` + count + `][net_unit_price]" id="products_` + count + `_net_unit_price"></td>`;
                        cols += `<td class="discount text-right"></td>`;
                        cols += `<td class="tax text-right"></td>`;
                        cols += `<td class="sub-total text-right"></td>`;
                        cols += `<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-product small-btn" style="padding: 1px 19px 19px 9px !important;font-size: 11px;border-radius: 6px;"><i class="fas fa-trash"></i></button></td>`;

                        cols += `<input type="hidden" class="product-id" name="products[` + count + `][id]"  value="` + data.id + `">`;
                        cols += `<input type="hidden" class="product-variant" name="products[` + count + `][variant_id]"  value="` + data.variant_id + `">`;
                        cols += `<input type="hidden" class="product-code" name="products[` + count + `][code]" value="` + data.code + `">`;
                        cols += `<input type="hidden" class="product-unit" name="products[` + count + `][unit]" value="` + temp_unit_name[0] + `">`;
                        cols += `<input type="hidden" class="discount-value" name="products[` + count + `][discount]">`;
                        cols += `<input type="hidden" class="tax-rate" name="products[` + count + `][tax_rate]" value="` + data.tax_rate + `">`;
                        cols += `<input type="hidden" class="tax-value" name="products[` + count + `][tax]">`;
                        cols += `<input type="hidden" class="subtotal-value" name="products[` + count + `][subtotal]">`;

                        newRow.append(cols);
                        $('#product_table tbody').append(newRow);

                        // product_price.push(parseFloat(data.price) + parseFloat(data.price * customer_group_rate));
                        product_price.push(parseFloat(data.price));
                        product_qty.push(data.qty);
                        product_discount.push('0.00');
                        tax_rate.push(parseFloat(data.tax_rate));
                        tax_name.push(data.tax_name);
                        tax_method.push(data.tax_method);
                        unit_name.push(data.unit_name);
                        unit_operator.push(data.unit_operator);
                        unit_operation_value.push(data.unit_operation_value);
                        rowindex = newRow.index();
                        checkQuantity(1, true, input = 2);
                        count++;
                    }

                }
            });
        }

        function checkQuantity(sale_qty,flag,input=2)
        {
            // var row_product_code = $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
            // var pos = product_code.indexOf(row_product_code);
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');

            if(operator[0] == '*')
            {
                total_qty = sale_qty * operation_value[0];
            }else if(operator[0] == '/'){
                total_qty = sale_qty / operation_value[0];
            }

            if(total_qty > parseFloat(product_qty[rowindex])){
                notification('error','Quantity exceed stock quantity');
                if(flag)
                {
                    //alert('1');
                    sale_qty = sale_qty.substring(0,sale_qty.length - 1);
                    if(sale_qty < 1){
                        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(0);
                    }else{
                        $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(sale_qty);
                    }
                }else{
                    edit();
                    return;
                }
            }

            if(!flag)
            {
                $('#editModal').modal('hide');
                $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val(sale_qty);
            }
            calculateProductData(sale_qty,input);

        }

        function edit()
        {
            var row_product_name = $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(1)').text();
            var row_product_code = $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
            $('#model-title').text(row_product_name+'('+row_product_code+')');

            var qty = $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.qty').val();
            $('#edit_qty').val(qty);
            $('#edit_discount').val(parseFloat(product_discount[rowindex]).toFixed(2));

            unitConversion();
            $('#edit_unit_price').val(row_product_price.toFixed(2));

            var tax_name_all = <?php echo json_encode($tax_name_all); ?>;
            var pos = tax_name_all.indexOf(tax_name[rowindex]);
            $('#edit_tax_rate').val(pos);

            temp_unit_name = (unit_name[rowindex]).split(',');
            temp_unit_name.pop();
            temp_unit_operator = (unit_operator[rowindex]).split(',');
            temp_unit_operator.pop();
            temp_unit_operation_value = (unit_operation_value[rowindex]).split(',');
            temp_unit_operation_value.pop();

            $('#edit_unit').empty();

            $.each(temp_unit_name, function(key,value){
                $('#edit_unit').append('<option value="'+key+'">'+value+'</option>');
            });
            $('.selectpicker').selectpicker('refresh');
        }

        function calculateProductData(quantity,input=2){
            unitConversion();

            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(8)').text((product_discount[rowindex] * quantity).toFixed(2));
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed(2));
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.unit-name').text(unit_name[rowindex].slice(0,unit_name[rowindex].indexOf(",")));

            if(tax_method[rowindex] == 1)
            {
                var net_unit_price = row_product_price ;
                var tax = net_unit_price * quantity * (tax_rate[rowindex]/100);
                var sub_total = (net_unit_price * quantity) + tax;
            }else{
                var sub_total_unit = row_product_price;
                var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
                var tax = (sub_total_unit - net_unit_price) * quantity;
                var sub_total = sub_total_unit * quantity;
            }
            // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(5)').text(net_unit_price.toFixed(2));
            if(input==2){
                $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.net_unit_price').val(net_unit_price > 0 ? net_unit_price.toFixed(2) : '');
            }
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(9)').text(tax.toFixed(2));
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.tax-value').val(tax.toFixed(2));
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(10)').text(sub_total.toFixed(2));
            $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('.subtotal-value').val(sub_total.toFixed(2));

            calculateTotal();
        }

        function unitConversion()
        {
            var row_unit_operator = unit_operator[rowindex].slice(0,unit_operator[rowindex].indexOf(','));
            var row_unit_operation_value = unit_operation_value[rowindex].slice(0,unit_operation_value[rowindex].indexOf(','));
            row_unit_operation_value = parseFloat(row_unit_operation_value);
            if(row_unit_operator == '*')
            {
                row_product_price = product_price[rowindex] * row_unit_operation_value;
            }else{
                row_product_price = product_price[rowindex] / row_unit_operation_value;
            }
        }

        function calculateTotal()
        {
            //sum of qty
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

            //sum of discount
            var total_discount = 0;
            $('.discount').each(function() {
                total_discount += parseFloat($(this).text());
            });
            $('#total-discount').text(total_discount.toFixed(2));
            $('input[name="total_discount"]').val(total_discount.toFixed(2));

            //sum of tax
            var total_tax = 0;
            $('.tax').each(function() {
                total_tax += parseFloat($(this).text());
            });
            $('#total-tax').text(total_tax.toFixed(2));
            $('input[name="total_tax"]').val(total_tax.toFixed(2));

            //sum of subtotal
            var total = 0;
            $('.sub-total').each(function() {
                total += parseFloat($(this).text());
            });
            $('#total').text(total.toFixed(2));
            $('input[name="total_price"]').val(total.toFixed(2));

            calculateGrandTotal();
        }

        function calculateGrandTotal()
        {
            var item           = $('#product_table tbody tr:last').index();
            var total_qty      = parseFloat($('#total-qty').text());
            var subtotal       = parseFloat($('#total').text());
            var order_tax      = parseFloat($('select[name="order_tax_rate"]').val());
            var order_discount = parseFloat($('#order_discount').val());
            var shipping_cost  = parseFloat($('#shipping_cost').val());
            // var labor_cost     = parseFloat($('#labor_cost').val());

            if(!order_discount){
                order_discount = 0.00;
            }
            if(!shipping_cost){
                shipping_cost = 0.00;
            }
            // if(!labor_cost){
            //     labor_cost = 0.00;
            // }

            item = ++item + '(' + total_qty + ')';

            order_tax = (subtotal - order_discount) * (order_tax / 100);
            var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;
            var previous_due = parseFloat($('#previous_due').val());
            var net_total = grand_total + previous_due;

            $('#item').text(item);
            $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
            $('#subtotal').text(subtotal.toFixed(2));
            $('#order_total_tax').text(order_tax.toFixed(2));
            $('input[name="order_tax"]').val(order_tax.toFixed(2));
            $('#order_total_discount').text(order_discount.toFixed(2));
            $('#shipping_total_cost').text(shipping_cost.toFixed(2));
            // $('#labor_total_cost').text(labor_cost.toFixed(2));
            $('#grand_total').text(grand_total.toFixed(2));
            $('input[name="grand_total"]').val(grand_total.toFixed(2));
            $('input[name="net_total"]').val(net_total.toFixed(2));
            if($('#payment_status option:selected').val() == 1)
            {
                $('#paid_amount').val(net_total.toFixed(2));
                $('#due_amount').val(parseFloat(0).toFixed(2));
            }else if($('#payment_status option:selected').val() == 2){
                var paid_amount = $('#paid_amount').val();
                $('#due_amount').val(parseFloat(net_total-paid_amount).toFixed(2));
            }else{
                $('#due_amount').val(parseFloat(net_total).toFixed(2));
            }
        }

        $('input[name="order_discount"]').on('input',function(){
            if(parseFloat($(this).val()) > parseFloat($('input[name="grand_total"]').val()))
            {
                notification('error','Order discount can\'t exceed grand total amount');
                $('input[name="order_discount"]').val(parseFloat(0));
            }
            calculateGrandTotal();

        });
        $('input[name="shipping_cost"]').on('input',function(){
            calculateGrandTotal();
        });
        // $('input[name="labor_cost"]').on('input',function(){
        //     calculateGrandTotal();
        // });
        $('select[name="order_tax_rate"]').on('change',function(){
            calculateGrandTotal();
        });

        $('#payment_status').on('change',function(){
            if($(this).val() != 3){
                $('.payment').removeClass('d-none');
                $('#paid_amount').val($('input[name="net_total"]').val());
                $('#due_amount').val(parseFloat(0).toFixed(2));
            }else{
                $('#paid_amount').val(0);
                $('#due_amount').val(parseFloat($('input[name="net_total"]').val()).toFixed(2));
                $('.payment').addClass('d-none');
            }
        });

        $('#paid_amount').on('input',function(){
            var payable_amount = parseFloat($('input[name="net_total"]').val());
            var paid_amount = parseFloat($(this).val());

            if(paid_amount > payable_amount){
                $('#paid_amount').val(payable_amount.toFixed(2));
                notification('error','Paid amount cannot be bigger than net total amount');
            }
            $('#due_amount').val((payable_amount - parseFloat($('#paid_amount').val())).toFixed(2));

        });

        account_list("{{ $sale->payment_method }}","{{ $sale->account_id }}");
        function account_list(payment_method,account_id='')
        {
            $.ajax({
                url: "{{route('account.list')}}",
                type: "POST",
                data: { payment_method: payment_method,_token: _token},
                success: function (data) {
                    $('#sale_update_form #account_id').html('');
                    $('#sale_update_form #account_id').html(data);
                    $('#sale_update_form #account_id.selectpicker').selectpicker('refresh');
                    if(account_id)
                    {
                        $('#sale_update_form #account_id').val(account_id);
                        $('#sale_update_form #account_id.selectpicker').selectpicker('refresh');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function update_data(){
            var rownumber = $('table#product_table tbody tr:last').index();
            if (rownumber < 0) {
                notification("error","Please insert product to order table!")
            }else{
                let form = document.getElementById('sale_update_form');
                let formData = new FormData(form);
                let url = "{{route('sale.update')}}";
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
                        $('#sale_update_form').find('.is-invalid').removeClass('is-invalid');
                        $('#sale_update_form').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function (key, value) {
                                var key = key.split('.').join('_');
                                $('#sale_update_form input#' + key).addClass('is-invalid');
                                $('#sale_update_form textarea#' + key).addClass('is-invalid');
                                $('#sale_update_form select#' + key).parent().addClass('is-invalid');
                                $('#sale_update_form #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                window.location.replace("{{ route('sale') }}");

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
