@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}"/>
    <link href="{{ asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet" type="text/css"/>
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
                                    <input type="text" class="form-control bg-secondary" name="invoice_no" id="invoice_no" value="{{ $sale->invoice_no }}"/>
                                </div>
                                <div class="form-group col-md-3 required">
                                    <label for="sale_date">Sale Date</label>
                                    <input type="text" class="form-control date bg-secondary" name="sale_date" id="sale_date" value="{{ $sale->sale_date }}" readonly/>
                                </div>
                                <x-form.date type="datetime-local" labelName="Est. Delivery Date" name="est_delivery_date" col="col-md-3" value="{{ $sale->est_delivery_date }}"/>

                                <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" class="selectpicker">
                                    @if (!$customers->isEmpty())
                                        @foreach ($customers as $value)
                                            <option
                                                value="{{ $value->id }}" {{$value->id  == $sale->customer_id ? 'selected' : ''}}>{{ $value->name.($value->mobile ? ' ('.$value->mobile.') ' : '') }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                <x-form.selectbox labelName="Delivery Status" name="delivery_status" required="required" col="col-md-3" class="selectpicker">
                                    @foreach (ORDER_STATUS_VALUE as $key => $value)
                                        @if($key != 2)
                                            <option value="{{ $key }}" {{ $sale->delivery_status == $key ? 'selected' :'' }}>{{ $value }}</option>
                                        @endif
                                    @endforeach
                                </x-form.selectbox>

                                <div class="form-group col-md-9">
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
                                        <th class="text-center">Serial No.</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center">Stock Qty</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Discount(%)</th>
                                        <th class="text-right">Discount(TK)</th>
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

                                                        if($sale_product->product_variant_id){
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
                                                    <td><input type="text" class="form-control product-serial-no" name="products[{{ $key+1 }}][serial_no]"
                                                               id="products_{{ $key+1 }}_serial_no" value="{{ $sale_product->serial_no }}"></td>

                                                    <td class="unit-name text-center">{{$unit_name}}</td>
                                                    <td><input type="text" class="stock-qty form-control text-center" name="products[{{ $key+1 }}][stock_qty]"
                                                               value="{{ $stock_qty }}" readonly></td>


                                                    <td><input type="text" class="qty form-control text-center" name="products[{{ $key+1 }}][qty]" value="{{ $sale_product->qty }}"></td>


                                                    <td><input type="text" readonly class="net_unit_price form-control text-center" name="products[{{
                                                    $key+1 }}][net_unit_price]"
                                                               value="{{ $sale_product->net_unit_price }}">
                                                    </td>
                                                    <td class="text-right">

                                                        <input type="text" class="discount-rate form-control text-center"
                                                               oninput="PercentageCalculator(this,this.value,'percentage',{{ $key+1 }})"
                                                               name="products[{{ $key+1 }}][discount_rate]" value="{{ $sale_product->discount_rate }}">

                                                    </td>
                                                    <td class="text-right">
                                                        <input type="text" class="discount discount-value  form-control text-center"
                                                               oninput="PercentageCalculator(this,this.value,'amount',{{ $key+1 }})"
                                                               name="products[{{ $key+1 }}][discount]" value="{{ $sale_product->discount }}">
                                                    </td>


                                                    <td>
                                                        <input type="text" class="sub-total form-control text-center"
                                                               name="products[{{ $key+1 }}][subtotal]" value="{{ $sale_product->total }}">

                                                    </td>

                                                    <td class="text-center d-flex">
                                                        <a class="dropdown-item remove-product" data-id="{{$sale_product->id}}" data-name="{{$sale_product->sale_id}}">
                                                            <button type="button" class="btn btn-danger btn-sm small-btn"
                                                                    style="padding: 1px 19px 19px 9px !important;font-size: 11px;border-radius: 6px;"><i class="fas fa-trash"></i>
                                                            </button>
                                                        </a>
                                                    </td>

                                                    <input type="hidden" class="product-id" name="products[{{ $key+1 }}][id]" value="{{ $sale_product->product_id }}">
                                                    <input type="hidden" class="product-varaint-id" name="products[{{ $key+1 }}][variant_id]"
                                                           value="{{ $sale_product->product_variant->id }}">

                                                    <input type="hidden" class="product-code" name="products[{{ $key+1 }}][code]"
                                                           value="{{ !empty($sale_product->product_variant_id) ? $sale_product->product_variant->item_code :  $sale_product->product->code }}">
                                                    <input type="hidden" class="product-price" name="products[{{ $key+1 }}][price]" value="{{ $product_price }}">
                                                    <input type="hidden" class="sale-unit" name="products[{{ $key+1 }}][unit]" value="{{ $unit_name }}">
                                                    <input type="hidden" name="products[{{ $key+1 }}][sale_product_id]" value="{{ $sale_product->product_variant_id }}">
                                                    <input type="hidden" class="sale-unit-operator" value="{{ $unit_operator }}">
                                                    <input type="hidden" class="sale-unit-operation-value" value="{{ $unit_operation_value }}">
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                        <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th
                                            class="text-center font-weight-bolder">
                                            <input type="text" class="form-control text-center" name="total_qty" id="total-qty" style="background-color: #003f7b; color: white;"
                                                   value=" {{ $sale->total_qty }}" readonly/>
                                            <input type="hidden" name="item"
                                                   value="{{ number_format($sale->item, 2, '.', ',') }}"
                                                   id="item">
                                        </th>
                                        <th></th>
                                        <th class="text-right font-weight-bolder">

                                        </th>
                                        <th class="text-right font-weight-bolder">
                                        </th>
                                        <th class="text-center font-weight-bolder">
                                            <input type="text" id="total_price" name="total_price" class="form-control text-center" style="background-color: #003f7b; color: white;"
                                                   value="{{  $sale->total_price }}"/>
                                        </th>
                                        <th></th>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="form-group col-md-8 text-right">Order Discount:<span>(% / TK)</span></div>
                                <div class="form-group col-md-2">
                                    <input type="text" oninput="orderDiscountCalculator(this.value, 'percentage')"
                                           class="form-control" placeholder="0.00 %" step="0.01" name="order_discount_rate" id="order_discount_rate"
                                           value="{{ $sale->order_discount_rate }}">
                                </div>
                                <div class="form-group col-md-2">
                                    <input type="text" class="form-control order_discount" id="order_discount" oninput="orderDiscountCalculator(this.value, 'amount')" step="0.01"
                                           placeholder="0.00" name="order_discount" value="{{ $sale->order_discount }}">
                                </div>

                                <div class="form-group col-md-8 text-right mt-7">Order Source:</div>

                                <x-form.selectbox labelName="" name="order_source_id" col="col-md-4" class="selectpicker">
                                    @foreach (ORDER_SOURCE as $key => $order_source)
                                        <option value="{{ $key }}" {{ $sale->order_source_id == $key ? 'selected' : ''  }}>{{ $order_source }}</option>
                                    @endforeach
                                </x-form.selectbox>

                                <div class="form-group col-md-8 text-right">Shipping Cost:</div>
                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control text-right" name="shipping_cost" id="shipping_cost" value="{{
                                    $sale->shipping_cost }}">
                                </div>

                                <div class="form-group col-md-8 text-right  ">Grand Total:</div>

                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control text-right" id="grand_total" name="grand_total" value="{{ $sale->grand_total
                                    }}">
                                </div>

                                <div class="form-group col-md-8 text-right">Paid Amount:</div>

                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control text-right" name="paid_amount" id="paid_amount" value="{{ $sale->paid_amount }}">
                                </div>

                                <div class="form-group col-md-8 text-right">Due Amount:</div>

                                <div class="form-group col-md-4">
                                    <input type="text" class="form-control text-right" name="due_amount" id="due_amount" value="{{ $sale->grand_total -
                                    $sale->paid_amount }}">
                                </div>

                                <div class="form-group col-md-8 text-right mt-7">Payment Status:</div>

                                <x-form.selectbox labelName="" name="payment_status" col="col-md-4" class="selectpicker">
                                    @foreach (PAYMENT_STATUS as $key => $value)
                                        <option value="{{ $key }}" @if($sale->payment_status === $key) selected @endif >{{ $value }}</option>
                                    @endforeach
                                </x-form.selectbox>

                                <div class="row col-md-12 pt-15 d-none" id="payment_method">
                                    @forelse($sale->pos_payments as $pay_key => $pay_item)
                                        <div class="row col-md-12" id="payment_method_tr_{{ $pay_key }}">
                                            <div class="form-group col-md-3">
                                                <label>Payment Method</label>
                                                <select class="form-control selectpicker" name="payment[{{ $pay_key }}][payment_method]"
                                                        onchange="account_list(this.value,{{ $pay_key}},{{ $pay_item->account_id }})"
                                                        id="payment_{{ $pay_key }}_payment_method" data-live-search="true"
                                                        data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @foreach (SALE_PAYMENT_METHOD as $key => $value)
                                                        <option value="{{ $key }}"
                                                                @if($pay_item->payment_method == $key) selected
                                                                @endif  data-reference_id="{{ $pay_key }}">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Account</label>
                                                <select class="form-control selectpicker" name="payment[{{ $pay_key }}][account_id]"
                                                        id="payment_{{ $pay_key  }}_account_id" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                </select>
                                            </div>

                                            <div
                                                class="form-group col-md-3 d-none  reference_no_{{ $pay_key }}">
                                                <label for="reference_no">Reference No</label>
                                                <input type="text" class="fcs form-control" name="payment[{{ $pay_key }}][reference_no]"
                                                       value="{{ $pay_item->reference_no }}" id="payment_{{ $pay_key }}_reference_no">
                                            </div>

                                            <div class="form-group col-md-2">
                                                <label for="reference_no">Amount</label>
                                                <input type="number" class="fcs form-control payment_amounts"
                                                       oninput="calculatePaymentAmount(this.value)"
                                                       value="{{ $pay_item->paid_amount }}" name="payment[{{ $pay_key }}][payment_amount]"
                                                       id="payment_{{ $pay_key }}_payment_amount">
                                            </div>

                                            <div
                                                class=" d-flex flex-column justify-content-center align-items-center">
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="addTableRow('payment_method',{{ $pay_key }})">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="row col-md-12"
                                             id="payment_method_tr_0">

                                            <div class="form-group col-md-3">
                                                <label>Payment Method</label>
                                                <select class="form-control selectpicker" name="payment[0][payment_method]"
                                                        onchange="account_list(this.value,0)" data-live-search="true"
                                                        data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @foreach (SALE_PAYMENT_METHOD as $key => $value)
                                                        <option value="{{ $key }}"
                                                                data-reference_id="0">{{ $value }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Account</label>
                                                <select class="form-control selectpicker" name="payment[0][account_id]"
                                                        id="payment_0_account_id" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3 d-none  reference_no_0">
                                                <label for="reference_no">Reference No</label>
                                                <input type="text" class="fcs form-control" name="payment[0][reference_no]"
                                                       id="reference_no_0">
                                            </div>

                                            <div class="form-group col-md-2">
                                                <label for="reference_no">Amount</label>
                                                <input type="number" class="fcs form-control payment_amounts"
                                                       oninput="calculatePaymentAmount(this.value)"
                                                       name="payment[0][payment_amount]" id="payment_amount_0">
                                            </div>

                                            <div
                                                class=" d-flex flex-column justify-content-center align-items-center">
                                                <button type="button" class="btn btn-success btn-sm" onclick="addTableRow('payment_method',0)"> <i class="fas fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforelse
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
    <script src="{{ asset('js/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/bootstrap-datetimepicker.min.js') }}"></script>
    <script>
        //array data depend on warehouse
        var product_array = [];
        var product_code = [];
        var product_name = [];
        var product_qty = [];

        // array data with selection
        var product_price = [];
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
        var customer_group_rate;
        var row_product_price;
        $(document).ready(function () {
            $('#product_code_name').on('input', function () {
                var customer_id = $('#customer_id option:selected').val();
                var temp_data = $('#product_code_name').val();
                if (!customer_id) {
                    $('#product_code_name').val(temp_data.substring(0, temp_data.length - 1));
                    notification('error', 'Please select customer');
                }
            });

            var rownumber = $('#product_table tbody tr:last').index();

            for (rowindex = 0; rowindex <= rownumber; rowindex++) {
                product_price.push(parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-price').val()));
                var total_discount = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(8)').text())
                var quantity = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val())
                product_discount.push((total_discount / quantity).toFixed(2));
                product_qty.push(parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.stock-qty').val()));
                tax_rate.push(parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val()));
                tax_name.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-name').val());
                tax_method.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-method').val());
                temp_unit_name = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val().split(',');
                unit_name.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val());
                unit_operator.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-operator').val());
                unit_operation_value.push($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit-operation-value').val());
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sale-unit').val(temp_unit_name[0]);
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.unit-name').text(temp_unit_name[0]);
            }

            //assigning value
            $('select[name="warehouse_id"]').val($('input[name="warehouse_id_hidden"]').val());
            $('select[name="customer_id"]').val($('input[name="customer_id_hidden"]').val());
            $('select[name="payment_status"]').val($('input[name="payment_status_hidden"]').val());
            $('select[name="order_tax_rate"]').val($('input[name="order_tax_rate_hidden"]').val());
            $('.selectpicker').selectpicker('refresh');

            // $('#item').text($('input[name="item"]').val() + '('+$('input[name="total_qty"]').val()+')');
            $('#item').text($('input[name="item"]').val() + '(' + $('input[name="total_qty"]').val() + ')');
            $('#subtotal').text(parseFloat($('input[name="total_price"]').val()).toFixed(2));
            $('#order_tax').text(parseFloat($('input[name="order_tax"]').val()).toFixed(2));

            if (!$('input[name="order_discount"]').val()) {
                $('input[name="order_discount"]').val('0.00');
            }
            $('#order_total_discount').text(parseFloat($('input[name="order_discount"]').val()).toFixed(2));
            if (!$('input[name="shipping_cost"]').val()) {
                $('input[name="shipping_cost"]').val('0.00');
            }
            $('#shipping_total_cost').text(parseFloat($('input[name="shipping_cost"]').val()).toFixed(2));

            $('#grand_total').text(parseFloat($('input[name="grand_total"]').val()).toFixed(2));

            var cid = $('input[name="customer_id_hidden"]').val();
            $.get('{{ url("customer/group-data") }}/' + cid, function (data) {
                customer_group_rate = (data / 100);
            });

            $('#customer_id').on('change', function () {
                var id = $(this).val();
                $.get('{{ url("customer/group-data") }}/' + id, function (data) {
                    customer_group_rate = (data / 100);
                });
                $.get('{{ url("customer/previous-balance") }}/' + id, function (data) {
                    $('#previous_due').val(parseFloat(data).toFixed(2));
                });
            });

            $('#product_code_name').autocomplete({
                source: function (request, response) {
                    // Fetch data
                    $.ajax({
                        url: "{{url('sale/product-autocomplete-search')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            _token: _token,
                            search: request.term,

                        },
                        success: function (data) {
                            response(data);
                        }
                    });
                },
                // minLength: 3,
                response: function (event, ui) {
                    if (ui.content.length == 1) {
                        var data = ui.content[0].code;
                        $(this).autocomplete("close");
                        product_search(data);
                    }
                    ;
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
            $('#product_table').on('click', '.edit-product', function () {
                rowindex = $(this).closest('tr').index();
                edit();
            });

            //Update Edit Product Data
            $('#update-btn').on('click', function () {
                var edit_discount = $('#edit_discount').val();
                var edit_qty = $('#edit_qty').val();
                var edit_unit_price = $('#edit_unit_price').val();

                if (parseFloat(edit_discount) > parseFloat(edit_unit_price)) {
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
                    product_price[rowindex] = $('#edit_unit_price').val() / row_unit_operation_value;
                } else {
                    product_price[rowindex] = $('#edit_unit_price').val() * row_unit_operation_value;
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

            $('#product_table').on('keyup', '.net_unit_price', function () {
                rowindex = $(this).closest('tr').index();
                if ($(this).val() < 1 && $(this).val() != '') {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .net_unit_price').val(1);
                    notification('error', 'Net unit price can\'t be less than 1');
                } else {
                    product_price[rowindex] = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .net_unit_price').val();
                }
                var qty = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val();
                if (qty > 0) {
                    checkQuantity(qty, true, input = 1);
                }

            });

            $('#product_table').on('click', '.remove-product', function () {
                rowindex = $(this).closest('tr').index();
                product_price.splice(rowindex, 1);
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

        });

        var count = 100;

        function product_search(data) {
            $.ajax({
                url: '{{ route("sale.product.search") }}',
                type: 'POST',
                data: {
                    data: data,
                    _token: _token,
                    warehouse_id: $('#warehouse_id option:selected').val()
                },
                success: function (data) {
                    var flag = 1;
                    $('.product-code').each(function (i) {
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
                        cols += `<td><input type="text" class="form-control text-center net_unit_price" readonly name="products[` + count + `][net_unit_price]"
                    id="products_` + count + `_net_unit_price"></td>`;


                        cols += `<td>
                <input type="text" class="discount-rate form-control text-center"
                oninput="PercentageCalculator(this,this.value,'percentage',` + count + `)"
                name="products[` + count + `][discount_rate]" value="` + data.discount_rate + `">
                </td>`;

                        cols += `<td>
            <input type="text"
              oninput="PercentageCalculator(this,this.value,'amount' , ` + count + `)"
            class="tax-value form-control discount discount-value text-center" name="products[` + count + `][discount]">
        </td>`;


                        cols += `<td>
<input type="text" readonly class="form-control sub-total text-center" name="products[` + count + `][subtotal]">
</td>`;
                        cols += `<td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-product small-btn" style="padding: 1px 19px 19px 9px !important;font-size: 11px;border-radius: 6px;"><i class="fas fa-trash"></i></button></td>`;

                        cols += `<input type="hidden" class="sale_product_id" name="products[` + count + `][sale_product_id]"  value="` + data.variant_id + `">`;
                        cols += `<input type="hidden" class="product-id" name="products[` + count + `][id]"  value="` + data.id + `">`;
                        cols += `<input type="hidden" class="product-code" name="products[` + count + `][code]" value="` + data.code + `">`;
                        cols += `<input type="hidden" class="product-unit" name="products[` + count + `][unit]" value="` + temp_unit_name[0] + `">`;


                        // cols += `<input type="hidden" class="subtotal-value" name="products[` + count + `][subtotal]">`;

                        newRow.append(cols);
                        $('#product_table tbody').append(newRow);

                        // product_price.push(parseFloat(data.price) + parseFloat(data.price * customer_group_rate));
                        product_price.push(parseFloat(data.price));
                        product_qty.push(data.qty);
                        // product_discount.push('0.00');
                        product_discount.push(data.discount);
                        product_discount.push(data.discount_rate);
                        tax_rate.push(data.discount);
                        // tax_rate.push(parseFloat(data.tax_rate));
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

        function checkQuantity(sale_qty, flag, input = 2) {
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');

            if (operator[0] == '*') {
                total_qty = sale_qty * operation_value[0];
            } else if (operator[0] == '/') {
                total_qty = sale_qty / operation_value[0];
            }

            if (total_qty > parseFloat(product_qty[rowindex])) {
                notification('error', 'Quantity exceed stock quantity');
                if (flag) {
                    sale_qty = sale_qty.substring(0, sale_qty.length - 1);
                    if (sale_qty < 1) {
                        $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(0);
                    } else {
                        $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
                    }
                } else {
                    edit();
                    return;
                }
            }

            if (!flag) {
                $('#editModal').modal('hide');
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
            }
            calculateProductData(sale_qty, input);
        }

        function edit() {
            var row_product_name = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(1)').text();
            var row_product_code = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)').text();
            $('#model-title').text(row_product_name + '(' + row_product_code + ')');

            var qty = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val();
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

            $.each(temp_unit_name, function (key, value) {
                $('#edit_unit').append('<option value="' + key + '">' + value + '</option>');
            });
            $('.selectpicker').selectpicker('refresh');
        }

        function calculateProductData(quantity, input = 2) {
            unitConversion();

            var sub_total_unit = row_product_price;
            product_discount[rowindex + 1] = $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .discount-value').val();
            var net_unit_price = sub_total_unit;
            var discount_amount = ((product_discount[rowindex + 1] / 100) * net_unit_price) * quantity
            var tax = discount_amount;
            var sub_total = (row_product_price * quantity) - discount_amount;
            if (input == 2) {
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed(2));
            }
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val(tax.toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.sub-total').val(sub_total.toFixed(2));
            calculateTotal();
        }

        function unitConversion() {
            var row_unit_operator = unit_operator[rowindex].slice(0, unit_operator[rowindex].indexOf(','));
            var row_unit_operation_value = unit_operation_value[rowindex].slice(0, unit_operation_value[rowindex].indexOf(','));
            row_unit_operation_value = parseFloat(row_unit_operation_value);
            if (row_unit_operator == '*') {
                row_product_price = product_price[rowindex] * row_unit_operation_value;
            } else {
                row_product_price = product_price[rowindex] / row_unit_operation_value;
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
            // $('#total-qty').val(total_qty);
            $('input[name="total_qty"]').val(total_qty);

            //sum of discount
            var total_discount = 0;
            $('.discount').each(function () {
                total_discount += parseFloat($(this).val());
            });
            // $('#total-discount').text(total_discount.toFixed(2));
            $('input[name="total_discount"]').val(total_discount.toFixed(2));

            //sum of tax
            var total_discount_rate = 0;
            $('.discount-rate').each(function () {
                total_discount_rate += parseFloat($(this).val());
            });

            // $('#total-tax').text(total_tax.toFixed(2));
            $('input[name="total_discount_rate"]').val(total_discount_rate.toFixed(2));

            //sum of subtotal
            var total = 0;
            $('.sub-total').each(function () {
                total += parseFloat($(this).val());
            });

            // $('#total').text(total.toFixed(2));
            $('input[name="total_price"]').val(total.toFixed(2));


            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            var total_discount = 0.00;
            var total_adjustment = 0.00;
            var item = $('#product_table tbody tr:last').index();
            var total_qty = parseFloat($('#total-qty').text());
            var subtotal = parseFloat($('input[name="total_price"]').val());
            var order_tax = parseFloat($('select[name="order_tax_rate"]').val()) ? parseFloat($('select[name="order_tax_rate"]').val()) : 0;
            var shipping_cost = parseFloat($('#shipping_cost').val());
            var order_discount = parseFloat($('input[name="order_discount"]').val()) ? parseFloat($('input[name="order_discount"]').val()) : 0;
            var order_discount_rate = parseFloat($('input[name="order_discount_rate"]').val()) ? parseFloat($('input[name="order_discount_rate"]').val()) : 0;
            var order_discount_per = $('#order_discount_per').is(":checked");
            var adjustment = parseFloat($('#adjustment').val());
            var adjustment_per = $('#adjustment_per').is(":checked");

            if (order_discount) {
                order_discount = order_discount;
            }

            if (!shipping_cost) {
                shipping_cost = 0.00;
            }

            if (!adjustment) {
                adjustment = 0.00;
            }
            if (!adjustment_per) {
                total_adjustment = adjustment;
            } else {
                total_adjustment = -(adjustment);
            }

            item = ++item + '(' + total_qty + ')';

            order_tax = (subtotal - order_discount) * (order_tax / 100);
            var grand_total = (subtotal + order_tax + shipping_cost + total_adjustment) - order_discount;

            console.log(grand_total, order_discount, 'order_discount ', subtotal, order_tax, shipping_cost, total_adjustment);

            $('#item').text(item);
            $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
            $('#total_price').val(subtotal.toFixed(2));
            $('#order_total_tax').text(order_tax.toFixed(2));
            $('input[name="order_tax"]').val(order_tax.toFixed(2));
            $('#order_total_discount').text(total_discount.toFixed(2));
            $('#shipping_total_cost').text(shipping_cost.toFixed(2));
            $('input[name="grand_total"]').val(grand_total.toFixed(2));

            let paid_amount = $('input[name="paid_amount"]').val();
            let due_amount = grand_total - paid_amount;
            $('input[name="due_amount"]').val(due_amount.toFixed(2));
        }

        $('input[name="order_discount"]').on('input', function () {
            if (parseFloat($(this).val()) > parseFloat($('input[name="grand_total"]').val())) {
                notification('error', 'Order discount can\'t exceed grand total amount');
                $('input[name="order_discount"]').val(parseFloat(0));
            }
            calculateGrandTotal();

        });
        $('input[name="shipping_cost"]').on('input', function () {
            calculateGrandTotal();
        });

        $('select[name="order_tax_rate"]').on('change', function () {
            calculateGrandTotal();
        });

        $('#payment_status').on('change', function () {
            if ($(this).val() != 3) {
                $('#payment_method *').prop('disabled', false);
                $('#payment_method select').selectpicker('refresh');
                $(`#payment_method`).removeClass('d-none');
            }
            if ($(this).val() === 3) {
                $('#payment_method *').prop('disabled', true);
                $(`#payment_method`).addClass('d-none');
            }
        });

        $('#payment_status').trigger('change');

        $('#paid_amount').on('input', function () {
            var payable_amount = parseFloat($('input[name="net_total"]').val());
            var paid_amount = parseFloat($(this).val());

            if (paid_amount > payable_amount) {
                $('#paid_amount').val(payable_amount.toFixed(2));
                notification('error', 'Paid amount cannot be bigger than net total amount');
            }
            $('#due_amount').val((payable_amount - parseFloat($('#paid_amount').val())).toFixed(2));
        });

        function account_list(payment_method, idx, account_idx = null) {
            $.ajax({
                url: "{{ route('account.list') }}",
                type: "POST",
                data: {
                    payment_method: payment_method,
                    account_id: account_idx,
                    _token: _token
                },
                success: function (data) {
                    $('#sale_update_form #payment_' + idx + '_account_id').empty().html(data);
                    $('#sale_update_form #payment_' + idx + '_account_id').selectpicker('refresh');

                    if (payment_method != 1) {
                        $(`.reference_no_${idx}`).removeClass('d-none').addClass('pt-1'); // You can change 'pt-4' to your desired padding class
                    } else {
                        $(`.reference_no_${idx}`).addClass('d-none');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function update_data() {
            var rownumber = $('table#product_table tbody tr:last').index();
            if (rownumber < 0) {
                notification("error", "Please insert product to order table!")
            } else {
                let form = document.getElementById('sale_update_form');
                let formData = new FormData(form);
                let url = "{{ route('sale.update') }}";
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

        $('#btn-filter').click(function () {
            table.ajax.reload();
        });

        $('#btn-reset').click(function () {
            $('#form-filter')[0].reset();
            $('#form-filter .selectpicker').selectpicker('refresh');
            table.ajax.reload();
        });

        $('#product_table').on('click', '.remove-product', function () {
            rowindex = $(this).closest('tr').index();
            if (rowindex === 0) {
                $('#change_amount,#paid_amount,#net_total,#grand_total,#shipping_cost,#adjustment,#order_discount,#order_tax')
                    .val((0).toFixed(2));
                $('#order_tax_rate').val(0);
                $('#order_tax_rate.selectpicker').selectpicker('refresh');
                $('#order_discount_per,#adjustment_per').prop('checked', false);
            }
            product_price.splice(rowindex, 1);
            product_discount.splice(rowindex, 1);
            tax_rate.splice(rowindex, 1);
            tax_name.splice(rowindex, 1);
            tax_method.splice(rowindex, 1);
            unit_name.splice(rowindex, 1);
            unit_operator.splice(rowindex, 1);
            unit_operation_value.splice(rowindex, 1);
            $(this).closest('tr').remove();
            calculateTotal();
            notification('success', 'Successfully Removed From Cart');
        });

        function PercentageCalculator(element, value, type, countx) {
            let inputValue = parseFloat(value);
            let result;
            let net_unit = document.querySelector(`input[name="products[${countx}][net_unit_price]"]`).value;

            let discount_amount = 0;
            if (!isNaN(inputValue)) {
                if (type === 'percentage') {
                    result = (net_unit * inputValue) / 100;

                    discount_amount = result;

                    let amountInput = document.querySelector(`input[name="products[${countx}][discount]"]`);

                    if (amountInput) {
                        amountInput.value = result.toFixed(2);
                    }

                } else if (type === 'amount') {
                    discount_amount = inputValue;

                    result = (inputValue / net_unit) * 100;

                    let percentageInput = document.querySelector(`input[name="products[${countx}][discount_rate]"]`);
                    if (percentageInput) {
                        percentageInput.value = result.toFixed(2);
                    }
                }


                let subTotalText = document.querySelector(`.sub-total${countx}`);

                let subTotalInput = document.querySelector(`input[name="products[${countx}][sub_total]"]`);


                let subTotal = net_unit - discount_amount;

                if (subTotalInput) {
                    subTotalInput.value = subTotal.toFixed(2);
                }

                if (subTotalText) {
                    subTotalText.textContent = subTotal.toFixed(2);
                }

                calculateTotal();
            }
        }

        var jsArray = <?php echo $sale->pos_payments; ?>;

        $.each(jsArray, function (index, value) {
            $('select[name="payment[' + index + '][payment_method]"]').trigger('change');
        });

        function PercentageCalculator(element, value, type, countx) {
            let inputValue = parseFloat(value);
            let result;
            let net_unit = document.querySelector(`input[name="products[${countx}][net_unit_price]"]`).value;

            let discount_amount = 0;
            if (!isNaN(inputValue)) {
                if (type === 'percentage') {
                    result = (net_unit * inputValue) / 100;

                    discount_amount = result;

                    let amountInput = document.querySelector(`input[name="products[${countx}][discount]"]`);

                    if (amountInput) {
                        amountInput.value = result.toFixed(2);
                    }

                } else if (type === 'amount') {
                    discount_amount = inputValue;

                    result = (inputValue / net_unit) * 100;

                    let percentageInput = document.querySelector(`input[name="products[${countx}][discount_rate]"]`);
                    if (percentageInput) {
                        percentageInput.value = result.toFixed(2);
                    }
                }

                let subTotalInput = document.querySelector(`input[name="products[${countx}][sub_total]"]`);

                let subTotal = net_unit - discount_amount;

                if (subTotalInput) {
                    subTotalInput.value = subTotal.toFixed(2);
                }

                calculateTotal();
            }
        }

        function orderDiscountCalculator(inputValue, type) {
            let grand_total = parseFloat($('input[name="grand_total"]').val());
            let discount, discounted_total, discount_percentage;
            if (type === 'percentage') {
                // Calculate the percentage discount
                discount = (grand_total * inputValue) / 100;
                discount_percentage = inputValue;
                $('input[name="order_discount"]').val(discount ? discount.toFixed(2) : 0);
            }
            if (type === 'amount') {
                // Subtract the fixed amount from the grand total
                discount = parseFloat(inputValue);
                discount_percentage = (discount / grand_total) * 100;
                $('input[name="order_discount_rate"]').val(discount_percentage ? discount_percentage.toFixed(2) : 0);
            }
            calculateTotal();
        }
    </script>

    @include('sale::includes.payment-method-script')
@endpush
