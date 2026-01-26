@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link rel="stylesheet" href="{{asset('css/jquery-ui.css')}}"/>
    <link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css"/>
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
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    @if(permission('sale-access'))
                        <div class="card-toolbar"><a href="{{ route('stock.exchange.list') }}" class="btn btn-warning btn-sm font-weight-bolder"><i
                                    class="fas fa-arrow-left"></i> Back</a></div>
                    @endif
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form action="" id="sale_store_form" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <input type="hidden" name="customer_id" value="{{ $sale->customer_id }}">
                                <input type="hidden" class="sale_type" name="sale_type" value="{{ $sale->sale_type }}">
                                <input type="hidden" name="warehouse_id" value="{{ $sale->warehouse_id }}">
                                <input type="hidden" name="name" value="{{ $sale->name }}">
                                <input type="hidden" name="phone" value="{{ $sale->phone }}">
                                <input type="hidden" name="information" value="{{ $sale->information }}">
                                <input type="hidden" name="sale_type" value="{{ $sale->sale_type }}">
                                <input type="hidden" class="form-control" name="sale_date" id="sale_date" value="{{ $sale->sale_date }}" readonly/>

                                <x-form.textbox class="bg-secondary" labelName="Invoice No." name="invoice_no" col="col-md-3"
                                                value="{{ $sale->invoice_no }}" property="readonly" required="required"/>
                                <x-form.selectbox labelName="Exchange Status" name="status" col="col-md-3" class="selectpicker"
                                                  required="required">
                                    <option value="1" selected>Pending</option>
                                    <option value="2">Approved</option>
                                </x-form.selectbox>
                                <x-form.textbox class="bg-secondary" labelName="Exchange Date" name="return_date" col="col-md-3"
                                                value="{{ date('Y-m-d') }}" property="readonly" required="required"/>
                                <x-form.textbox class="bg-secondary" labelName="Customer Name" name="customer_name" col="col-md-3"
                                                value="{{ $sale->name ? $sale->name : $sale->customer->name}}" property="readonly" required="required"/>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="">
                                            <table class="table table-bordered quarterSelectTable" id="oldSaleTable">
                                                <thead class="bg-primary text-center">
                                                <th width="20%">Product Name</th>
                                                <th width="20%" style="width: 10% !important;">Code</th>
                                                <th width="5%" style="width: 5% !important;">Sold Qty</th>
                                                <th width="10%">Sale Price</th>
                                                <th width="5%" style="width: 5% !important;">Exchange Qty</th>
                                                <th width="10%">Action</th>
                                                </thead>
                                                <tbody>
                                                <tr class="text-center">
                                                    <td width="20%">
                                                        <select class="form-control selectpicker productDetails"
                                                                id="old_sale_1_old_product_id" data-product_id="old_sale_1_product_id"
                                                                data-product_code="old_sale_1_product_code"
                                                                data-stock_qty="old_sale_1_stock_qty" data-price="old_sale_1_price"
                                                                name="old_sale[1][old_product_id]" data-live-search="true">
                                                            <option value="" selected>Select Product</option>
                                                            @if (!$sale->sale_products->isEmpty())
                                                                @foreach ($sale->sale_products as $key => $sale_product)
                                                                    <option
                                                                        value="{{ $sale_product->id }}">{{ $sale_product->product->name }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <input type="hidden" class="form-control" id="old_sale_1_product_id"
                                                               name="old_sale[1][old_product_id]" readonly/>
                                                    </td>
                                                    <td width="10%">
                                                        <input type="text" class="form-control bg-primary text-white"
                                                               id="old_sale_1_product_code" name="old_sale[1][old_product_code]"
                                                               readonly/>
                                                    </td>
                                                    <td width="10%">
                                                        <input type="text" class="form-control bg-primary text-white"
                                                               id="old_sale_1_stock_qty" name="old_sale[1][old_stock_qty]" readonly/>
                                                    </td>
                                                    <td width="10%">
                                                        <input type="number" class="form-control price" id="old_sale_1_price"
                                                               name="old_sale[1][old_price]"/>
                                                    </td>
                                                    <td width="10%">
                                                        <input type="number" class="form-control old_exchange_qty"
                                                               id="old_sale_1_old_exchange_qty" name="old_sale[1][old_exchange_qty]"/>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm oldProductAddRaw"><i class="fas fa-plus-circle"></i></button>
                                                    </td>
                                                </tr>
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Exchange Qty</td>

                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        <input class="form-control  text-right exchange_qty" type="text"
                                                               name="exchange_qty" id="exchange_qty" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Previous Payment</td>

                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        <input class="form-control  text-right" type="text" name="previous_payment" value="{{$sale->grand_total}}" readonly>
                                                    </td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>


                                {{--     New product                           --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <div class="">
                                            <table class="table table-bordered quarterSelectTable" id="saleTable">
                                                <thead class="bg-primary text-center">
                                                <th width="10%">Exchange Product</th>
                                                <th width="10%">Stock Qty</th>
                                                <th width="10%" style="width: 8%;">Price</th>
                                                <th width="10%" style="width: 5%;">Exchange Qty</th>
                                                <th width="10%">Action</th>
                                                </thead>
                                                <tbody>
                                                <tr class="text-center">
                                                    <td width="20%">
                                                        <select class="form-control selectpicker showroomProductDetails"
                                                                data-product_id="new_sale_1_product_id" data-product_code="new_sale_1_product_code" data-price="new_sale_1_price"
                                                                data-price="new_sale_1_price" data-stock_qty="new_sale_1_stock_qty"
                                                                name="new_sale[1][product_id]" data-charge_amount="new_sale_1_charge_amount" data-live-search="true">
                                                            <option value="" selected>Select Product</option>
                                                            @if (!$warehouse_product->isEmpty())
                                                                @foreach ($warehouse_product as $value)
                                                                    <option
                                                                        value="{{ $value->product_id }}">{{ $value->product->name }}
                                                                    </option>
                                                                @endforeach
                                                            @endif
                                                        </select>
                                                        <input type="hidden" class="form-control" id="new_sale_1_product_id" name="new_sale[1][product_id]" readonly/>
                                                        <input type="hidden" class="form-control" id="new_sale_1_product_code" name="new_sale[1][product_code]" readonly/>
                                                    </td>

                                                    <td><input type="text" class="form-control bg-primary text-white" id="new_sale_1_stock_qty" name="new_sale[1][stock_qty]"
                                                               readonly/></td>
                                                    <td><input type="number" class="form-control price" id="new_sale_1_price" name="new_sale[1][price]"/></td>
                                                    <td>
                                                        <input type="number" class="form-control exchange_qty" id="new_sale_1_exchange_qty" name="new_sale[1][exchange_qty]"/>
                                                    </td>
                                                    {{--                                                    <td><input type="number" class="form-control sub_total charge_amount" id="new_sale_1_charge_amount" name="new_sale[1][charge_amount]"/></td>--}}
                                                    <td>
                                                        <button type="button" class="btn btn-primary btn-sm addRaw"><i class="fas fa-plus-circle"></i></button>
                                                    </td>
                                                </tr>
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Total</td>
                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        <input class="form-control total_exchange_price" type="text" name="total_price" value="" id="total_exchange_price" readonly>
                                                        <input class="form-control item" type="hidden" name="item" value="" id="item" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Exchange Product Amount
                                                    </td>
                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        {{--                                                        <input class="form-control prv_value" type="text" name="prv_pay_amount" value="{{$sale->paid_amount}}" id="prv_value" readonly>--}}
                                                        <input type="number" class="form-control old_total_price prv_value" id="old_total_price" readonly/>
                                                        <input type="hidden" class="form-control total_qty " id="total_qty" name="total_qty"/>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Net Total
                                                    </td>
                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        <input class="form-control sub_total" type="text" name="net_total" value="" id="total_sum_value" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Delivery Charge
                                                    </td>
                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        <input class="form-control shipping_cost" type="text" name="shipping_cost" value="" id="shipping_cost">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Grand Total
                                                    </td>
                                                    <td class="text-right font-weight-bolder" colspan="2">
                                                        <input class="form-control grand_total" type="text" name="grand_total" value="" id="grand_total" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Paid Amount:
                                                    </td>
                                                    <td colspan="2">
                                                        <input type="text" class="form-control text-right bg-secondary paid_amount" name="paid_amount" id="paid_amount" value="0"
                                                               placeholder="0.00">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Due Amount:
                                                    </td>
                                                    <td colspan="2">
                                                        <input type="text" class="form-control text-right bg-secondary due_amount"
                                                               name="due_amount" id="due_amount" value="0" placeholder="0.00" readonly>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="4" style="color: #000 !important;border: none;"
                                                        class="text-right font-weight-bolder">Payment Status:
                                                    </td>
                                                    <td colspan="2">
                                                        <select class="form-control selectpicker" data-live-search="true"
                                                                name="payment_status" id="payment_status"
                                                                data-live-search-placeholder="Search">
                                                            <option value="">Select Please</option>
                                                            @foreach (PAYMENT_STATUS as $key => $value)
                                                                <option value="{{ $key }}"
                                                                        @if($key  == $sale->payment_status ) selected @endif >{{ $value }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row col-md-12 pt-15 d-none" id="payment_method">
                                        <div class="row col-md-12"
                                             id="payment_method_tr_0">
                                            <div class="form-group col-md-3">
                                                <label>Payment Method</label>
                                                <select class="form-control selectpicker" name="payment[0][payment_method]" onchange="account_list(this.value,0)"
                                                        id="payment_0_payment_method" data-live-search="true" data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                    @foreach (SALE_PAYMENT_METHOD as $key => $value)
                                                        <option value="{{ $key }}" data-reference_id="0">{{ $value }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3">
                                                <label>Account</label>
                                                <select class="form-control selectpicker" name="payment[0][account_id]" id="payment_0_account_id" data-live-search="true"
                                                        data-live-search-placeholder="Search">
                                                    <option value="">Select Please</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-3 d-none  reference_no_0">
                                                <label for="reference_no">Reference No</label>
                                                <input type="text" class="fcs form-control" name="payment[0][reference_no]" id="payment_0_reference_no">
                                            </div>

                                            <div class="form-group col-md-2">
                                                <label for="reference_no">Amount</label>
                                                <input type="number" class="fcs form-control payment_amounts" oninput="calculatePaymentAmount(this.value)"
                                                       name="payment[0][payment_amount]" id="payment_0_payment_amount">
                                            </div>

                                            <div
                                                class=" d-flex flex-column justify-content-center align-items-center">
                                                <button type="button" class="btn btn-success btn-sm"
                                                        onclick="addTableRow('payment_method',0)"><i class="fas fa-plus"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-12 text-center pt-5">
                                    <a class="btn btn-danger btn-sm mr-3" href="{{route('sale.add')}}"><i class="fas fa-sync-alt"></i>{{__('Reset')}}</a>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="storeData()"><i class="fas fa-save"></i>{{__('Save')}}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{asset('js/jquery-ui.js')}}"></script>
    <script src="{{asset('js/bootstrap-datetimepicker.min.js')}}"></script>
    <script>
        let i = 2;
        var item = [];
        var total_exchange_price = [];
        var total_qty = [];
        var sub_total = [];
        var paid_amount = [];
        var due_amount = [];
        let table_row = 0;

        $(document).ready(function () {
            $("#kt_body").addClass("aside-minimize");

            $(document).on('click', '.oldProductAddRaw', function () {
                let html;
                html = `<tr>
                         <td width="20%">
                             <select class="form-control productDetails selectpicker" id="old_sale_` + i + `_old_product_id" data-product_id="old_sale_` + i + `_product_id" data-product_code="old_sale_` + i + `_product_code" data-warehouse="sale1_` + i + `_warehouse_id" data-unit_name = "old_sale_` + i + `_unit_name" data-price = "old_sale_` + i + `_price" data-stock_qty = "old_sale_` + i + `_stock_qty"  name="old_sale[` + i + `][old_product_id]" data-live-search="true">
                                <option value="" selected>Select Product</option>
                                    @if (!$sale->sale_products->isEmpty())
                @foreach ($sale->sale_products as $key => $sale_product)
                <option
                    value="{{ $sale_product->id }}">{{ $sale_product->product->name }}
                </option>
@endforeach
                @endif
                </select>
                <input type="hidden" class="form-control" id="old_sale_` + i + `_product_id"
                                name="old_sale[` + i + `][old_product_id]" readonly/>
                         </td>
                         <td>
                            <input type="text" class="form-control bg-primary text-white" id="old_sale_` + i + `_product_code"
                                name="old_sale[` + i + `][old_product_code]" readonly/>
                         </td>
                         <td>
                             <input type="text" class="form-control bg-primary text-white" id="old_sale_` + i + `_stock_qty"
                                name="old_sale[` + i + `][old_stock_qty]" readonly/>
                         </td>
                         <td>
                             <input type="number" class="form-control price" id="old_sale_` + i + `_price" data-qty = "old_sale_` + i + `_qty"
                              data-sub_total = "old_sale_` + i + `_sub_total" name="old_sale[` + i + `][old_price]"/>
                         </td>
                             <td><input type="number" class="form-control old_exchange_qty " id="old_sale_` + i + `_old_exchange_qty" name="old_sale[` + i + `][old_exchange_qty]"/>
                         </td>
                         <td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm oldDeleteRaw"><i class="fas fa-minus-circle"></i></button>
                         </td>
                    </tr>`;
                $('#oldSaleTable tbody').append(html);
                $('.selectpicker').selectpicker('refresh');
                i++;
            });
            $(document).on('click', '.oldDeleteRaw', function () {
                $(this).parent().parent().remove();
                calculateTotal();
                $('.selectpicker').selectpicker('refresh');
            });

            $(document).on('click', '.addRaw', function () {
                let html;
                html = `<tr>
                   <td width="20%">
                     <select class="form-control selectpicker showroomProductDetails" id="new_sale_` + i + `_sp_product_id" data-product_id = "new_sale_` + i + `_product_id" data-product_code = "new_sale_` + i + `_product_code" data-price = "new_sale_` + i + `_price" data-stock_qty = "new_sale_` + i + `_stock_qty" name="new_sale[` + i + `][product_id]" data-live-search="true">
                        <option value="" selected>Select Product</option>
                            @foreach ($warehouse_product as $value)
                <option
                    value="{{ $value->product_id }}">{{ $value->product->name }}
                </option>
@endforeach
                </select>
               <input type="hidden" class="form-control" id="new_sale_` + i + `_product_id" name="new_sale[` + i + `][product_id]" readonly/>
                    <input type="hidden" class="form-control" id="new_sale_` + i + `_product_code" name="new_sale[` + i + `][product_code]" readonly/>
                  </td>
                <td><input type="text" class="form-control bg-primary text-white" id="new_sale_` + i + `_stock_qty" name="new_sale[` + i + `][stock_qty]" readonly/></td>
                 <td><input type="number" class="form-control price" id="new_sale_` + i + `_price" data-qty = "new_sale_` + i + `_qty" data-sub_total = "new_sale_` + i + `_sub_total" name="new_sale[` + i + `][price]"/></td>
                 <td><input type="number" class="form-control exchange_qty" id="new_sale_` + i + `_exchange_qty"  name="new_sale[` + i + `][exchange_qty]"/></td>
                 <td class = "text-center"><button type = "button" class = "btn btn-danger btn-sm deleteRaw"><i class = "fas fa-minus-circle"></i></button></td>
               </tr>`;
                $('#saleTable tbody').append(html);
                $('.selectpicker').selectpicker('refresh');
                i++;
            });
            $(document).on('click', '.deleteRaw', function () {
                $(this).parent().parent().remove();
                calculateTotal();
                $('.selectpicker').selectpicker('refresh');
            });

            $(document).on('change', '.productDetails', function () {
                let $this = $(this);
                let productId = $this.val();
                let warehouseId = $('#' + $this.data('warehouse')).find('option:selected').val();
                let product_id = $this.data('product_id');
                let product_code = $this.data('product_code');
                let sale_type = $this.data('sale_type');
                let price = $this.data('price');
                let stockQty = $this.data('stock_qty');

                if (product_code) {
                    $.ajax({
                        url: "{{ route('sale.product.details') }}",
                        type: "GET",
                        data: {product_id: productId},
                        success: function (data) {
                            if (data) {
                                $('#' + product_id).val(data.product_id);
                                $('#' + price).val(data.price);
                                $('#' + stockQty).val(data.stockQty);
                                $('#' + product_code).val(data.product_code);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error: " + status + " - " + error);
                        }
                    });
                }
            });

            $(document).on('change', '.showroomProductDetails', function () {
                // Set the shipping cost to 0
                $('#shipping_cost').val(0);

                // Retrieve values from the selected option and data attributes
                let productId = $(this).val();
                let warehouseId = $('#' + $(this).data('warehouse')).val();
                let product_id = $(this).data('product_id');
                let product_code = $(this).data('product_code');
                let price = $(this).data('price');
                let charge_amount = $(this).data('charge_amount');
                let stockQty = $(this).data('stock_qty');

                if (productId) {
                    $.ajax({
                        url: "{{ route('get.product.details') }}",
                        type: "GET",
                        data: {product_id: productId},
                        success: function (data) {
                            if (data) {
                                // Assuming `product_id`, `price`, `stockQty`, and `product_code` are the IDs of input fields
                                $('#' + product_id).val(data.product_id);
                                $('#' + price).val(data.price);
                                $('#' + stockQty).val(data.stockQty);
                                $('#' + product_code).val(data.product_code);
                                $('#' + charge_amount).val(data.price);
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error("AJAX Error: " + status + " - " + error);
                        }
                    });
                }
            });

            $("#oldSaleTable").on('input', '.old_exchange_qty, .price', function () {
                var calculated_total_sum = 0;
                var total_qty = 0;

                $("#oldSaleTable tr").each(function () {
                    var quantity = parseFloat($(this).find('.old_exchange_qty').val());
                    var old_price = parseFloat($(this).find('.price').val());

                    if (!isNaN(quantity) && !isNaN(old_price)) {
                        calculated_total_sum += quantity * old_price;
                        total_qty += quantity;
                    }
                });

                $("#old_total_price").val(calculated_total_sum.toFixed(2));
                $("#exchange_qty").val(total_qty);
                calculateTotal();
            });

            $("#saleTable").on('input', '.exchange_qty, .price', function () {
                var total_price = 0;
                var total_qty = 0;

                // Loop through each row in the table and calculate totals
                $("#saleTable tbody tr").each(function () {
                    var quantity = parseFloat($(this).find('.exchange_qty').val());
                    var price = parseFloat($(this).find('.price').val());

                    if (!isNaN(quantity) && !isNaN(price)) {
                        total_price += quantity * price;
                        total_qty += quantity;
                    }
                });

                // Update the total exchange price and quantity in the respective input fields
                $("#total_exchange_price").val(total_price.toFixed(2)); // Format to 2 decimal places
                $("#total_qty").val(total_qty);

                // Recalculate other totals if necessary
                calculateTotal();
            });

            $('#payment_status').on('change', function () {
                if ($(this).val() != 3) {
                    $('#payment_method *').prop('disabled', false);
                    $(`#payment_method`).removeClass('d-none');
                } else {
                    $('#payment_method *').prop('disabled', true);
                    $(`#payment_method`).addClass('d-none');
                }
            });

            $('#payment_status').trigger('change');

            $('#payment_method').on('change', function () {
                if ($(this).val() != 1) {
                    $('.reference_no').removeClass('d-none');
                } else {
                    $('.reference_no').addClass('d-none');
                }
            });

            $('.payment_amounts').on('input', function () {
                var value = $(this).val();
                if ((value !== '') && (value.indexOf('.') === -1)) {
                    let payable_amount = parseFloat($('#total_price').val());
                    let paid_amount = $('#paid_amount').val() ? parseFloat($('#paid_amount').val()) : 0;
                    let ongoing = (payable_amount - paid_amount).toFixed(2);
                    if (value > payable_amount) {
                        notification('error', 'The Payment Can not grater than payable amount');
                    }
                }
            });
        });

        function addTableRow(tableId, idx) {
            table_row = idx;
            ++table_row;

            // Assuming PAYMENT_STATUS is a JavaScript array or object
            let paymentOptions = [
                    @foreach (SALE_PAYMENT_METHOD as $key => $value)
                {
                    key: '{{ $key }}', value: '{{ $value }}'
                },
                @endforeach
            ];

            let selectOptions = '';
            paymentOptions.forEach(option => {
                selectOptions += `<option value="${option.key}" data-reference_id="${table_row}" >${option.value}</option>`;
            });

            let html = `
            <div class="row col-md-12" id="payment_method_tr_${table_row}">
                <div class="form-group col-md-3">
                    <label>Payment Method</label>
                    <select class="form-control selectpicker" data-live-search="true" name="payment[${table_row}][payment_method]" id="payment_${table_row}_payment_method" onchange="account_list(this.value,${table_row})" data-reference_id="${table_row}" data-live-search-placeholder="Search">
                       <option value="">Select Please</option>
                        ${selectOptions}
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Account</label>
                   <select class="form-control  selectpicker" id="payment_${table_row}_account_id" data-live-search="true" name="payment[${table_row}][account_id]" data-live-search-placeholder="Search">
                       <option value="">Select Please</option>
                   </select>
                </div>
                <div class="form-group col-md-3  d-none reference_no_${table_row}"
                <label for="reference_no">Reference No</label>
                <input type="text" class="fcs form-control" name="payment[${table_row}][reference_no]" id="payment_${table_row}_reference_no">
            </div>
            <div class="form-group col-md-2 ">
               <label for="payment_amount">Amount</label>
               <input type="number" class="fcs form-control payment_amounts" oninput="calculatePaymentAmount(this.value)" name="payment[${table_row}][payment_amount]" id="payment_${table_row}_payment_amount">
            </div>

            <div class="mb-2 d-flex flex-column justify-content-center align-items-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeRow('payment_method_tr_','${table_row}')"><i class="fas fa-minus"></i></button>
                <button type="button" class="btn btn-success btn-sm" onclick="addTableRow('payment_method','${table_row}')"><i class="fas fa-plus"></i></button>
            </div>
            </div>`;

            // Append the new row to the table or container with the specified ID
            $('#' + tableId).append(html);

            // Refresh the selectpicker to apply Bootstrap-select styling
            $('#' + tableId + ' .selectpicker').selectpicker('refresh');
        }

        function setReturnValue(row) {
            $('#old_sale_' + row + '_return_checkbox').is(':checked') ? $('#old_sale_return_' + row).val(1) : $('#old_sale_return_' + row).val(2);
        }

        function calculatePaymentAmount(value) {
            let totals = 0;
            $(".payment_amounts").each(function () {
                var inputValue = $(this).val() > 0 ? $(this).val() : 0;
                if (!isNaN(inputValue)) {
                    totals += parseFloat(inputValue);
                }
            });

            if (!isNaN(totals)) {
                let payable_amount = parseFloat($('#total_sum_value').val());
                let dues = (payable_amount - totals).toFixed(2);
                if (dues >= 0) {
                    $('#paid_amount').val(totals);
                    $('#due_amount').val(dues);

                }

            }
        }

        function calculateTotal() {
            var itemIndex = $('#saleTable tbody tr:last').index() + 1;
            let prv_value = $("#old_total_price").val() ? parseFloat($("#old_total_price").val()) : 0;
            let sum_value = $("#total_exchange_price").val() ? parseFloat($("#total_exchange_price").val()) : 0;

            let calculated_total_sum = 0;
            let difference = 0;

            $("#saleTable .sub_total").each(function () {
                let textbox_value = $(this).val();
                if ($.isNumeric(textbox_value)) {
                    calculated_total_sum += parseFloat(textbox_value);
                }
            });

            // Calculate difference
            if (prv_value > sum_value) {
                difference = 0;
            } else {
                difference = (sum_value - prv_value).toFixed(2);
            }

            // Update the relevant fields
            $("#total_sum_value").val(difference);
            $('#grand_total').val(difference);
            $('#item').text(itemIndex);
            $('input[name="item"]').val(itemIndex);

            let paid_amount = $('#paid_amount').val() ? parseFloat($('#paid_amount').val()) : 0;
            let due_amount = (difference - paid_amount).toFixed(2);

            $('input[name="due_amount"]').val(due_amount);
        }

        function storeData() {
            let form = document.getElementById('sale_store_form');
            let formData = new FormData(form);
            $.ajax({
                url: "{{ route('stock.exchange.store') }}",
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
                    $('#sale_store_form').find('.is-invalid').removeClass('is-invalid');
                    $('#sale_store_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#sale_store_form input#' + key).addClass('is-invalid');
                            $('#sale_store_form textarea#' + key).addClass('is-invalid');
                            $('#sale_store_form select#' + key).parent().addClass('is-invalid');
                            $('#sale_store_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            window.location.replace("{{ route('stock.exchange.list') }}");
                        }
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

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
                    // console.log(data, idx, payment_method);

                    $('#sale_store_form #payment_' + idx + '_account_id').empty().html(data);
                    $('#sale_store_form #payment_' + idx + '_account_id').selectpicker('refresh');

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
    </script>
@endpush
