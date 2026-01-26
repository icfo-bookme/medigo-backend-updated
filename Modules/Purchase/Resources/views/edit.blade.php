@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link rel="stylesheet" href="css/jquery-ui.css"/>
    {{--    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />--}}
    <style>
        .small-btn {
            width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
        }

        .small-btn i {
            font-size: 10px !important;
        }
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
                        <a href="{{ route('purchase') }}" class="btn btn-warning btn-sm font-weight-bolder">
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
                        <form action="" id="purchase_store_form" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                                <input type="hidden" name="supplier_id_hidden" value="{{ $purchase->supplier_id }}">
                                <input type="hidden" name="purchase_status_hidden" value="{{ $purchase->purchase_status }}">
                                <input type="hidden" name="order_tax_rate_hidden" value="{{ $purchase->order_tax_rate }}">

                                <div class="form-group col-md-4 required">
                                    <label for="chalan_no">Invoice No.</label>
                                    <input type="text" class="form-control" name="invoice_no" id="invoice_no" value="{{ $purchase->invoice_no }}"/>
                                </div>
                                <x-form.textbox labelName="Purchase Date" name="purchase_date" value="{{ $purchase->purchase_date }}" required="required" class="date"
                                                col="col-md-4"/>

                                <x-form.selectbox labelName="Supplier" name="supplier_id" required="required" col="col-md-4" class="selectpicker">
                                    @if (!$suppliers->isEmpty())
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->company_name.' ('.$supplier->name.')' }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>

                                <x-form.selectbox labelName="Purchase Status" name="purchase_status" required="required" col="col-md-4" class="selectpicker"
                                                  onchange="received_qty(this.value)">
                                    @foreach (PURCHASE_STATUS as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                    @endforeach
                                </x-form.selectbox>

                                <div class="form-group col-md-4">
                                    <label for="document">Attach Document</label>
                                    <input type="file" class="form-control" name="document" id="document">
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="product_code_name">Select Product</label>
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
                                        </div>
                                        <input type="text" class="form-control" name="product_code_name" id="product_code_name"
                                               placeholder="Please type product code and select...">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered" id="product_table">
                                        <thead class="bg-primary">
                                        <th>Name</th>
                                        <th class="text-center">Code</th>
                                        <th class="text-center">SL No.</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Expiry Date</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Free Qty</th>
                                        <th class="text-center d-none received-product-qty">Received</th>
                                        <th class="text-right">Net Unit Cost</th>
                                        {{--                                        <th class="text-right">Discount</th>--}}
                                        {{--                                        <th class="text-right">Tax</th>--}}
                                        <th class="text-right">Subtotal</th>
                                        <th></th>
                                        </thead>
                                        <tbody>
                                        @php
                                            $temp_unit_name = [];
                                            $temp_unit_operator = [];
                                            $temp_unit_operation_value = [];
                                            $totalFreeQty = 0;
                                        @endphp
                                        @if (!$purchase->purchase_products->isEmpty())
                                            @foreach ($purchase->purchase_products as $key => $purchase_product)
                                                <tr>
                                                    @php
                                                        // dd($purchase_product->product_variant->item_code);
                                                         $tax = DB::table('taxes')->where('rate',$purchase_product->tax_rate)->first();

                                                        $units = DB::table('units')->where('base_unit',$purchase_product->product_variant->product_unit_id)
                                                                                    ->orWhere('id',$purchase_product->product_variant->product_unit_id)
                                                                                    ->get();

                                                        if($purchase_product->product_variant_id){
                                                            $stock_qty = $purchase_product->product_variant->item_qty - $purchase_product->qty;
                                                        }else{
                                                            $stock_qty = $purchase_product->product->qty - $purchase_product->qty;
                                                        }
                                                        $unit_name            = [];
                                                        $unit_operator        = [];
                                                        $unit_operation_value = [];

                                                        if($units){
                                                            foreach ($units as $unit) {
                                                                if($purchase_product->purchase_unit_id == $unit->id)
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

                                                            if($purchase_product->tax_method == 1){
                                                                $product_cost = ($purchase_product->net_unit_cost + ($purchase_product->discount / $purchase_product->qty)) / $unit_operation_value[0];
                                                            }else{
                                                                $product_cost = (($purchase_product->total + ($purchase_product->discount / $purchase_product->qty)) / $purchase_product->qty) / $unit_operation_value[0];
                                                            }

                                                            $temp_unit_name = $unit_name = implode(",",$unit_name).',';
                                                            $temp_unit_operator = $unit_operator = implode(",",$unit_operator).',';
                                                            $temp_unit_operation_value = $unit_operation_value = implode(",",$unit_operation_value).',';
                                                        }
                                                        $totalFreeQty += $purchase_product->free_qty;
                                                    @endphp
                                                    {{--                                                    <td>{{ !empty($purchase_product->product_variant_id) ? $purchase_product->product->name.' - ('.$purchase_product->product_variant->item_name.')'.($purchase_product->product->brand_id ? ' - ['.$purchase_product->product->brand->name.']' : '').' - [Avbl. Qty = '.$stock_qty.']' : $purchase_product->product->name.($purchase_product->product->brand_id ? ' - ['.$purchase_product->product->brand->name.']' : '').' - [Avbl. Qty = '.$stock_qty.']' }}</td>--}}
                                                    <td>{{$purchase_product->product_variant->product->name}}</td>
                                                    <td class="text-center code">{{ !empty($purchase_product->product_variant_id) ? $purchase_product->product_variant->item_code :  $purchase_product->product->code }}</td>
                                                    <td>
                                                        <input type="text" class="form-control product-serial-no"
                                                               name="products[{{ $key+1 }}][serial_no]" id="products_{{ $key+1 }}_serial_no"
                                                               value="{{ $purchase_product->serial_no }}">
                                                    </td>

                                                    <td class="unit-name"></td>
                                                    <td class="text-center">
                                                        <input type="date" class="form-control expiry_date text-center"
                                                               name="products[{{ $key+1 }}][expiry_date]"
                                                               id="products_{{ $key+1 }}_expiry_date" value="{{ $purchase_product->expiry_date }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control qty text-center"
                                                               name="products[{{ $key+1 }}][qty]" id="products_{{ $key+1 }}_qty"
                                                               value="{{ $purchase_product->qty }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="text" class="form-control free_qty text-center" name="products[{{ $key+1 }}][free_qty]"
                                                               value="{{ $purchase_product->free_qty }}">
                                                    </td>
                                                    <td class="received-product-qty d-none">
                                                        <input type="text" class="form-control received text-center"
                                                               name="products[{{ $key+1 }}][received]"
                                                               value="{{ $purchase_product->received }}">
                                                    </td>

                                                    <td><input type="text" class="net_unit_cost form-control text-right" name="products[{{ $key+1 }}][net_unit_cost]"
                                                               value="{{ $purchase_product->net_unit_cost }}"></td>
                                                    <td class="discount text-right" style="display:none">{{ number_format((float)$purchase_product->discount, 2, '.','') }}</td>
                                                    <td class="tax text-right" style="display:none">{{ number_format((float)$purchase_product->tax, 2, '.','') }}</td>
                                                    <td class="sub-total text-right">{{ number_format((float)$purchase_product->total, 2, '.','') }}</td>
                                                    <td class="text-center">
                                                        <button type="button" class="edit-product btn btn-sm btn-primary mr-2 small-btn" data-toggle="modal"
                                                                data-target="#editModal" style="display:none"><i class="fas fa-edit"></i></button>
                                                        <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                    <input type="hidden" class="product-id" name="products[{{ $key+1 }}][id]" value="{{ $purchase_product->product_id }}">
                                                    <input type="hidden" class="product-variant-id" name="products[{{ $key+1 }}][variant_id]"
                                                           value="{{ $purchase_product->product_variant_id }}">
                                                    <input type="hidden" class="product-code" name="products[{{ $key+1 }}][code]"
                                                           value="{{ !empty($purchase_product->product_variant_id) ? $purchase_product->product_variant->item_code :  $purchase_product->product->code }}">
                                                    <input type="hidden" class="product-cost" name="products[{{ $key+1 }}][cost]" value="{{ $product_cost }}">
                                                    <input type="hidden" class="purchase-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" class="purchase-unit-operator" value="{{ $unit_operator }}">
                                                    <input type="hidden" class="purchase-unit-operation-value" value="{{ $unit_operation_value }}">

                                                    <input type="hidden" class="discount-value" name="products[{{ $key+1 }}][discount]" value="{{ $purchase_product->discount }}">
                                                    <input type="hidden" class="tax-rate" name="products[{{ $key+1 }}][tax_rate]" value="{{ $purchase_product->tax_rate }}">
                                                    @if ($tax)
                                                        <input type="hidden" class="tax-name" value="{{ $tax->name }}">
                                                    @else
                                                        <input type="hidden" class="tax-name" value="No Tax">
                                                    @endif
                                                    <input type="hidden" class="tax-method" value="{{ $purchase_product->tax_method }}">
                                                    <input type="hidden" class="tax-value" name="products[{{ $key+1 }}][tax]" value="{{ $purchase_product->tax }}">
                                                    <input type="hidden" class="subtotal-value" name="products[{{ $key+1 }}][subtotal]" value="{{ $purchase_product->total }}">

                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                        <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ $purchase->total_qty - $totalFreeQty }}</th>
                                        <th id="total-free-qty" class="text-center font-weight-bolder">{{ $totalFreeQty }}</th>
                                        <th class="d-none received-product-qty font-weight-bolder"></th>
                                        <th></th>
                                        <th id="total-discount" class="text-right font-weight-bolder"
                                            style="display:none">{{ number_format($purchase->total_discount,2,'.',',') }}</th>
                                        <th id="total-tax" class="text-right font-weight-bolder" style="display:none">{{ number_format($purchase->total_tax,2,'.',',') }}</th>
                                        <th id="total" class="text-right font-weight-bolder">{{ number_format($purchase->total_cost,2,'.',',') }}</th>
                                        <th></th>
                                        </tfoot>
                                    </table>
                                </div>
                                <x-form.selectbox labelName="Order Tax" name="order_tax_rate" col="col-md-4" class="selectpicker">
                                    <option value="0" selected>No Tax</option>
                                    @if (!$taxes->isEmpty())
                                        @foreach ($taxes as $tax)
                                            <option value="{{ $tax->rate }}">{{ $tax->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>

                                <div class="form-group col-md-4">
                                    <label for="order_discount">Order Discount</label>
                                    <input type="text" class="form-control" name="order_discount" id="order_discount" value="{{ $purchase->order_discount }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="shipping_cost">Shipping Cost</label>
                                    <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ $purchase->shipping_cost }}">
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="shipping_cost">Note</label>
                                    <textarea class="form-control" name="note" id="note" cols="30" rows="3">{{ $purchase->note }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="bg-primary">
                                        <th><strong>Items</strong><span class="float-right" id="item">0.00</span></th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">0.00</span></th>
                                        <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">{{ $purchase->order_tax }}</span></th>
                                        <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">{{ $purchase->order_discount }}</span></th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">0.00</span></th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">0.00</span></th>
                                        </thead>
                                    </table>
                                </div>

                                <div class="col-md-12">
                                    <input type="hidden" name="total_qty" value="{{ $purchase->total_qty }}">
                                    <input type="hidden" name="total_free_qty" value="{{ $totalFreeQty }}">
                                    <input type="hidden" name="total_discount" value="{{ $purchase->total_discount }}">
                                    <input type="hidden" name="total_tax" value="{{ $purchase->total_tax }}">
                                    <input type="hidden" name="total_cost" value="{{ $purchase->total_cost }}">
                                    <input type="hidden" name="item" value="{{ $purchase->item }}">
                                    <input type="hidden" name="order_tax" value="{{ $purchase->order_tax }}">
                                    <input type="hidden" name="grand_total" value="{{ $purchase->grand_total }}">
                                    <input type="hidden" name="paid_amount" value="{{ $purchase->paid_amount }}">
                                </div>
                                <div class="form-grou col-md-12 text-center pt-5">
                                    <a href="{{ route('purchase') }}" class="btn btn-danger btn-sm mr-3"><i class="far fa-window-close"></i> Cancel</a>
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
                            <x-form.textbox labelName="Unit Cost" name="edit_unit_cost" col="col-md-12"/>
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
                                <label for="edit_unit">Material Unit</label>
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
        $(document).ready(function () {
            // $('.date').datetimepicker({format: 'YYYY-MM-DD'});

            //array data depend on warehouse
            var product_array = [];
            var product_code = [];
            var product_name = [];
            var product_qty = [];

            // array data with selection
            var product_cost = [];
            var product_labor_cost = [];
            var product_discount = [];
            var tax_rate = [];
            var tax_name = [];
            var tax_method = [];
            var unit_name = [];
            var unit_operator = [];
            var unit_operation_value = [];

            //temporary array
            var temp_unit_name = [];
            var temp_unit_operator = [];
            var temp_unit_operation_value = [];

            var rowindex;
            var row_product_cost;


            $('#product_code_name').autocomplete({
                // source: "{{url('product-autocomplete-search')}}",
                source: function (request, response) {
                    // Fetch data
                    $.ajax({
                        url: "{{url('purchase/product-autocomplete-search')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            _token: _token,
                            search: request.term
                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                minLength: 3,
                response: function (event, ui) {
                    if (ui.content.length == 1) {
                        var data = ui.content[0].code;
                        $(this).autocomplete("close");
                        productSearch(data);
                    }
                    ;
                },
                select: function (event, ui) {
                    // $('.product_search').val(ui.item.value);
                    // $('.product_id').val(ui.item.id);
                    var data = ui.item.code;
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
                product_cost.push(parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-cost').val()));
                var total_discount = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text())
                var quantity = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val())
                product_discount.push((total_discount / quantity).toFixed(2));
                tax_rate.push(parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val()));
                tax_name.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-name').val());
                tax_method.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-method').val());
                temp_unit_name = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val().split(',');
                unit_name.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val());
                unit_operator.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit-operator').val());
                unit_operation_value.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit-operation-value').val());
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(temp_unit_name[0]);
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.unit-name').text(temp_unit_name[0]);
            }

            //assigning value
            $('select[name="supplier_id"]').val($('input[name="supplier_id_hidden"]').val());
            $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
            $('select[name="purchase_status"]').val($('input[name="purchase_status_hidden"]').val());
            $('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
            $('.selectpicker').selectpicker('refresh');

            $('#item').text($('input[name="item"]').val() + '(' + $('input[name="total_qty"]').val() + ')');
            $('#subtotal').text(parseFloat($('input[name="total_cost"]').val()).toFixed(2));
            $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed(2));

            if ($('#purchase_status option:selected').val() == 2) {
                $('.received-product-qty').removeClass('d-none');
            }

            if (!$('input[name="order_discount"]').val()) {
                $('input[name="order_discount"]').val('0.00');
            }
            $('#order_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed(2));
            if (!$('input[name="shipping_cost"]').val()) {
                $('input[name="shipping_cost"]').val('0.00');
            }
            $('#shipping_total_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed(2));
            $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));

            //Edit Product
            $('#product_table').on('click', '.edit-product', function () {
                rowindex = $(this).closest('tr').index();
                var row_product_name = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
                var row_product_code = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
                $('#model-title').text(row_product_name + '(' + row_product_code + ')');

                var qty = $(this).closest('tr').find('.qty').val();
                $('#edit_qty').val(qty);
                $('#edit_discount').val(parseFloat(product_discount[rowindex]).toFixed(2));

                unitConversion();
                $('#edit_unit_cost').val(row_product_cost.toFixed(2));

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

                $.each(temp_unit_name, function (key, value) {
                    $('#edit_unit').append('<option value="' + key + '">' + value + '</option>');
                });
                $('.selectpicker').selectpicker('refresh');
            });

            //Update Edit Product Data
            $('#update-btn').on('click', function () {
                var edit_discount = $('#edit_discount').val();
                var edit_qty = $('#edit_qty').val();
                var edit_unit_cost = $('#edit_unit_cost').val();

                if (parseFloat(edit_discount) > parseFloat(edit_unit_cost)) {
                    notification('error', 'Invalid discount input');
                    return;
                }

                if (edit_qty < 1) {
                    $('#edit_qty').val(1);
                    edit_qty = 1;
                    notification('error', 'Quantity can\'t be less than 1');
                }

                var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(','));
                var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(','));
                row_unit_operation_value = parseFloat(row_unit_operation_value);
                var tax_rate_all = <?php echo json_encode($tax_rate_all); ?>;

                tax_rate[rowindex] = parseFloat(tax_rate_all[$('#edit_tax_rate option:selected').val()]);
                tax_name[rowindex] = $('#edit_tax_rate option:selected').text();

                if (row_unit_operator == '*') {
                    product_cost[rowindex] = $('#edit_unit_cost').val() / row_unit_operation_value;
                } else {
                    product_cost[rowindex] = $('#edit_unit_cost').val() * row_unit_operation_value;
                }

                product_discount[rowindex] = $('#edit_discount').val();
                var position = $('#edit_unit').val();
                var temp_operator = temp_unit_operator[position];
                var temp_operation_value = temp_unit_operation_value[position];
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.purchase-unit').val(temp_unit_name[position]);
                temp_unit_name.splice(position, 1);
                temp_unit_operator.splice(position, 1);
                temp_unit_operation_value.splice(position, 1);

                temp_unit_name.unshift($('#edit_unit option:selected').text());
                temp_unit_operator.unshift(temp_operator);
                temp_unit_operation_value.unshift(temp_operation_value);

                unit_name[rowindex] = temp_unit_name.toString() + ',';
                unit_operator[rowindex] = temp_unit_operator.toString() + ',';
                unit_operation_value[rowindex] = temp_unit_operation_value.toString() + ',';
                checkQuantity(edit_qty, false);
            });

            $('#product_table').on('keyup', '.qty', function () {
                rowindex = $(this).closest('tr').index();
                if ($(this).val() < 1 && $(this).val() != '') {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
                    notification('error', 'Qunatity can\'t be less than 1');
                }
                checkQuantity($(this).val(), true, input = 2);
            });

            $('#product_table').on('keyup', '.free_qty', function () {
                rowindex = $(this).closest('tr').index();
                var qty = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
                checkQuantity(qty, true, input = 2);
            });

            $('#product_table').on('keyup', '.net_unit_cost', function () {
                rowindex = $(this).closest('tr').index();
                if ($(this).val() < 1 && $(this).val() != '') {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .net_unit_cost').val(1);
                    notification('error', 'Net unit price can\'t be less than 1');
                } else {
                    product_cost[rowindex] = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .net_unit_cost').val();
                }
                var qty = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
                if (qty > 0) {
                    checkQuantity(qty, true, input = 1);
                }

            });

            $('#product_table').on('click', '.remove-product', function () {
                rowindex = $(this).closest('tr').index();
                product_cost.splice(rowindex, 1);
                product_discount.splice(rowindex, 1);
                tax_rate.splice(rowindex, 1);
                tax_name.splice(rowindex, 1);
                tax_method.splice(rowindex, 1);
                unit_name.splice(rowindex, 1);
                unit_operator.splice(rowindex, 1);
                unit_operation_value.splice(rowindex, 1);
                $(this).closest('tr').remove();
                calculateTotal();
            });

            @if (!$purchase->purchase_products->isEmpty())
            var count = "{{ count($purchase->purchase_products) + 1 }}";
            @else
            var count = 1;
            @endif
            function productSearch(data) {
                $.ajax({
                    url: '{{ route("purchase.product.search") }}',
                    type: 'POST',
                    data: {
                        data: data, _token: _token
                    },
                    success: function (data) {
                        var flag = 1;
                        $('.product-code').each(function (i) {
                            if ($(this).val() == data.code) {
                                rowindex = i;
                                var qty = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val()) + 1;
                                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(qty);
                                calculateProductData(qty, input = 2);
                                flag = 0;
                            }
                        });
                        $('#product_code_name').val('');
                        if (flag) {
                            temp_unit_name = data.unit_name.split(',');
                            var newRow = $('<tr>');
                            var cols = '';
                            cols += `<td>` + data.name + `</td>`;

                            cols += `<td class="text-center code">` + data.code + `</td>`;
                            cols += `<td class="text-center"><input type="text" class="form-control" name="products[` + count + `][serial_no]" id="products_` + count + `_serial_no" value="${count}"></td>`;
                            cols += `<td class="unit-name text-center"></td>`;
                            cols += `<td class="text-center"><input type="date" class="form-control expiry_date text-center" name="products[` + count + `][expiry_date]" id="products_` + count + `_expiry_date"></td>`;
                            cols += `<td><input type="text" class="form-control qty text-center" name="products[` + count + `][qty]"
                            id="products_` + count + `_qty" value="1"></td>`;
                            cols += `<td class="text-center"><input type="text" class="form-control free_qty text-center" name="products[` + count + `][free_qty]" id="products_` + count + `_free_qty" value="0"></td>`;

                            if ($('#purchase_status option:selected').val() == 1) {
                                cols += `<td class="received-product-qty d-none"><input type="text" class="form-control received text-center"
                            name="products[` + count + `][received]" value="1"></td>`;
                            } else if ($('#purchase_status option:selected').val() == 2) {
                                cols += `<td class="received-product-qty"><input type="text" class="form-control received text-center"
                            name="products[` + count + `][received]" value="1"></td>`;
                            } else {
                                cols += `<td class="received-product-qty d-none"><input type="text" class="form-control received text-center"
                            name="products[` + count + `][received]" value="0"></td>`;
                            }

                            cols += `<td><input type="text" class="net_unit_cost form-control text-right" name="products[` + count + `][net_unit_cost]"></td>`;
                            cols += `<td class="discount text-right d-none"></td>`;
                            cols += `<td class="tax text-right d-none"></td>`;
                            cols += `<td class="sub-total text-right"></td>`;
                            cols += `<td class="text-center" >
                            <button type="button" class="edit-product btn btn-sm btn-primary small-btn mr-2" data-toggle="modal"data-target="#editModal" style="display:none"><i class="fas fa-edit"></i></button>
                            <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button></td>`;
                            cols += `<input type="hidden" class="product-id" name="products[` + count + `][id]"  value="` + data.id + `">`;
                            cols += `<input type="hidden" class="product-variant-id" name="products[` + count + `][variant_id]"  value="` + data.variant_id + `">`;
                            cols += `<input type="hidden" class="product-code" name="products[` + count + `][code]" value="` + data.code + `">`;
                            cols += `<input type="hidden" class="product-unit" name="products[` + count + `][unit]" value="` + temp_unit_name[0] + `">`;

                            cols += `<input type="hidden" class="discount-value" name="products[` + count + `][discount]" >`;
                            cols += `<input type="hidden" class="tax-rate" name="products[` + count + `][tax_rate]" value="` + data.tax_rate + `" >`;
                            cols += `<input type="hidden" class="tax-value" name="products[` + count + `][tax]">`;
                            cols += `<input type="hidden" class="subtotal-value" name="products[` + count + `][subtotal]">`;

                            newRow.append(cols);
                            $('#product_table tbody').append(newRow);

                            product_cost.push(parseFloat(data.cost));
                            product_discount.push('0.00');
                            tax_rate.push(parseFloat(data.tax_rate));
                            tax_name.push(data.tax_name);
                            tax_method.push(data.tax_method);
                            unit_name.push(data.unit_name);
                            unit_operator.push(data.unit_operator);
                            unit_operation_value.push(data.unit_operation_value);
                            rowindex = newRow.index();
                            calculateProductData(1, input = 2);
                            count++;
                        }

                    }
                });
            }

            function checkQuantity(purchase_qty, flag, input = 2) {
                var row_product_code = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.code').text();
                var pos = product_code.indexOf(row_product_code);
                var operator = unit_operator[rowindex].split(',');
                var operation_value = unit_operation_value[rowindex].split(',');

                if (operator[0] == '*') {
                    total_qty = purchase_qty * operation_value[0];
                } else if (operator[0] == '/') {
                    total_qty = purchase_qty / operation_value[0];
                }

                $('#editModal').modal('hide');
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(purchase_qty);
                var status = $('#purchase_status option:selected').val();
                if (status == '1' || status == '2') {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.received').val(purchase_qty);
                }
                calculateProductData(purchase_qty, input);
            }

            function calculateProductData(quantity, input = 2) {
                unitConversion();

                // $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(8)').text((product_discount[rowindex] * quantity).toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount').text((product_discount[rowindex] * quantity).toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.unit-name').text(unit_name[rowindex].slice(0, unit_name[rowindex].indexOf(",")));

                console.log([
                    'Product Cost: ' + row_product_cost,
                    'Quantity: ' + quantity,
                    'Discount: ' + product_discount,
                    'Tax Rate: ' + tax_rate[rowindex],
                    'Tax Method: ' + tax_method[rowindex],
                    'Unit Name: ' + unit_name[rowindex],
                    'Unit Operator: ' + unit_operator[rowindex],
                    'Unit Operation Value: ' + unit_operation_value[rowindex],
                ]);

                if (tax_method[rowindex] == 1) {
                    var net_unit_cost = row_product_cost - product_discount[rowindex];
                    var tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
                    var sub_total = (net_unit_cost * quantity) + tax;
                } else {
                    var sub_total_unit = row_product_cost - product_discount[rowindex];
                    var net_unit_cost = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
                    var tax = (sub_total_unit - net_unit_cost) * quantity;
                    var sub_total = (sub_total_unit * quantity);
                    // console.log([
                    //     'Sub Total Unit: '+sub_total_unit,
                    //     'Net Unit Cost: '+net_unit_cost,
                    //     'Tax: '+tax,
                    //     'Sub Total: '+sub_total,
                    // ])
                }

                // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(6)').text(net_unit_cost.toFixed(2));
                if (input == 2) {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost.toFixed(2));
                }
                // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(9)').text(tax.toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
                // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(11)').text(sub_total.toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').text(sub_total.toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed(2));

                calculateTotal();
            }

            function unitConversion() {
                var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(','));
                var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(','));
                row_unit_operation_value = parseFloat(row_unit_operation_value);

                if (row_unit_operator == '*') {
                    row_product_cost = product_cost[rowindex] * row_unit_operation_value;
                    // console.log([
                    //     'Unit Operator: '+row_unit_operator,
                    //     'Unit Operation Value: '+row_unit_operation_value,
                    //     'Product Cost: '+row_product_cost,
                    // ]);
                } else {
                    row_product_cost = product_cost[rowindex] / row_unit_operation_value;
                }
            }

            function calculateTotal() {
                //sum of qty
                var total_qty = 0;
                $('.qty').each(function () {
                    if ($(this).val() == '') {
                        total_qty += 0;
                    } else {
                        total_qty += parseFloat($(this).val());
                    }
                });
                $('#total-qty').text(total_qty);
                $('input[name="total_qty"]').val(total_qty);

                //sum of free qty
                var total_free_qty = 0;
                $('.free_qty').each(function () {
                    if ($(this).val() == '') {
                        total_free_qty += 0;
                    } else {
                        total_free_qty += parseFloat($(this).val());
                    }
                });
                $('#total-free-qty').text(total_free_qty);
                $('input[name="total_free_qty"]').val(total_free_qty);

                //sum of discount
                var total_discount = 0;
                $('.discount').each(function () {
                    total_discount += parseFloat($(this).text());
                });
                $('#total-discount').text(total_discount.toFixed(2));
                $('input[name="total_discount"]').val(total_discount.toFixed(2));

                //sum of tax
                var total_tax = 0;
                $('.tax').each(function () {
                    total_tax += parseFloat($(this).text());
                });
                $('#total-tax').text(total_tax.toFixed(2));
                $('input[name="total_tax"]').val(total_tax.toFixed(2));

                //sum of subtotal
                var total = 0;
                $('.sub-total').each(function () {
                    total += parseFloat($(this).text());
                });
                $('#total').text(total.toFixed(2));
                $('input[name="total_cost"]').val(total.toFixed(2));

                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                var item = $('#product_table tbody tr:last').index();
                var total_qty = parseFloat($('#total-qty').text()) + parseFloat($('#total-free-qty').text());
                var subtotal = parseFloat($('input[name="total_cost"]').val());
                var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
                var order_discount = parseFloat($('#order_discount').val());
                var shipping_cost = parseFloat($('#shipping_cost').val());
                var labor_cost = parseFloat($('#labor_cost').val());
                if (!order_discount) {
                    order_discount = 0.00;
                }
                if (!shipping_cost) {
                    shipping_cost = 0.00;
                }
                if (!labor_cost) {
                    labor_cost = 0.00;
                }
                item = ++item + '(' + total_qty + ')';
                order_tax = (subtotal - order_discount) * (order_tax / 100);
                var grand_total = (subtotal + order_tax + shipping_cost + labor_cost) - order_discount;
                $('#item').text(item);
                $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);

                $('#subtotal').text(subtotal.toFixed(2));
                $('#order_total_tax').text(order_tax.toFixed(2));
                $('input[name="order_tax"]').val(order_tax.toFixed(2));
                $('#order_total_discount').text(order_discount.toFixed(2));
                $('#shipping_total_cost').text(shipping_cost.toFixed(2));
                $('#labor_total_cost').text(labor_cost.toFixed(2));
                $('#grand_total').text(grand_total.toFixed(2));
                $('input[name="grand_total"]').val(grand_total.toFixed(2));
            }

            $('input[name="order_discount"]').on('input', function () {
                calculateGrandTotal();
            });
            $('input[name="shipping_cost"]').on('input', function () {
                calculateGrandTotal();
            });
            $('select[name="order_tax_rate"]').on('change', function () {
                calculateGrandTotal();
            });
        });

        received_qty($('#purchase_status option:selected').val());

        function received_qty(purchase_status) {
            if (purchase_status == 2) {
                $(".recieved-product-qty").removeClass("d-none");
                $(".qty").each(function () {
                    rowindex = $(this).closest('tr').index();
                    $('table#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
                });
            } else if ((purchase_status == 3) || (purchase_status == 4)) {
                $(".recieved-product-qty").addClass("d-none");
                $(".recieved").each(function () {
                    $(this).val(0);
                });
            } else {
                $(".recieved-product-qty").addClass("d-none");
                $(".qty").each(function () {
                    rowindex = $(this).closest('tr').index();
                    $('table#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.recieved').val($(this).val());
                });
            }
        }

        function store_data() {
            var rownumber = $('table#product_table tbody tr:last').index();
            if (rownumber < 0) {
                notification("error", "Please insert product to order table!")
            } else {
                let form = document.getElementById('purchase_store_form');
                let formData = new FormData(form);
                let url = "{{route('purchase.update')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $('#save-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
                        $('#save-btn').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        $('#purchase_store_form').find('.is-invalid').removeClass('is-invalid');
                        $('#purchase_store_form').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function (key, value) {
                                var key = key.split('.').join('_');
                                $('#purchase_store_form input#' + key).addClass('is-invalid');
                                $('#purchase_store_form textarea#' + key).addClass('is-invalid');
                                $('#purchase_store_form select#' + key).parent().addClass('is-invalid');
                                $('#purchase_store_form #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                window.location.replace("{{ route('purchase') }}");

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
