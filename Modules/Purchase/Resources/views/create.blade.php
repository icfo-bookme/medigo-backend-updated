@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('css/jquery-ui.css')}}" rel="stylesheet"/>
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
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('purchase') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form action="" id="purchase_store_form" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="purchase_id">
                            <div class="row">
                                <div class="form-group col-md-3 required">
                                    <label for="invoice_no">Invoice No.</label>
                                    <input type="text" class="form-control" name="invoice_no" id="invoice_no"
                                           value="{{ isset($purchase_data) ? $purchase_data['purchase']['invoice_no'] : $invoice_no }}" readonly/>
                                </div>
                                <x-form.textbox labelName="Purchase Date" name="purchase_date"
                                                value="{{ isset($purchase_data) ? $purchase_data['purchase']['purchase_date'] : date('Y-m-d') }}" required="required" class="date"
                                                col="col-md-3"/>
                                <x-form.selectbox labelName="Supplier" name="supplier_id" required="required" col="col-md-3" class="selectpicker">
                                    @if (!$suppliers->isEmpty())
                                        @foreach ($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" @if(isset($purchase_data))
                                                {{ ($purchase_data['purchase']['supplier_id'] == $supplier->id) ? 'selected' : ''}}
                                                @endif>{{ $supplier->company_name.' ('.$supplier->name.')' }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>
                                {{--                            <x-form.selectbox labelName="Purchase Status" name="purchase_status" required="required" col="col-md-4" class="selectpicker" onchange="received_qty(this.value)">--}}
                                {{--                                @foreach (PURCHASE_STATUS as $key => $value)--}}
                                {{--                                    <option value="{{ $key }}" @if(isset($purchase_data)) {{ ($purchase_data['purchase']['purchase_status'] == $key) ? 'selected' : ''}} @else {{ ($key == 1) ? 'selected' : '' }} @endif  >{{ $value }}</option>--}}
                                {{--                                @endforeach--}}
                                {{--                            </x-form.selectbox>--}}
                                <div class="form-group col-md-3">
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
                                        </tbody>
                                        <tfoot class="bg-primary">
                                        <th colspan="5" class="font-weight-bolder">Total</th>
                                        <th id="total-qty" class="text-center font-weight-bolder">{{ isset($purchase_data) ? $purchase_data['purchase']['total_qty']: '0' }}</th>
                                        <th id="total-free-qty" class="text-center font-weight-bolder">0</th>
                                        <th class="d-none received-product-qty font-weight-bolder"></th>
                                        <th></th>
                                        {{--                                        <th id="total-discount" class="text-right font-weight-bolder">{{ isset($purchase_data) ? $purchase_data['purchase']['total_discount']: '0.00' }}</th>--}}
                                        {{--                                        <th id="total-tax" class="text-right font-weight-bolder">{{ isset($purchase_data) ? $purchase_data['purchase']['total_tax']: '0.00' }}</th>--}}
                                        <th id="total" class="text-right font-weight-bolder">{{ isset($purchase_data) ? $purchase_data['purchase']['total_cost']: '0.00' }}</th>
                                        <th></th>
                                        </tfoot>
                                    </table>
                                </div>

                                <x-form.selectbox labelName="Order Tax" name="order_tax_rate" col="col-md-3" class="selectpicker">
                                    <option value="0">No Tax</option>
                                    @if (!$taxes->isEmpty())
                                        @foreach ($taxes as $key => $tax)
                                            <option value="{{ $tax->rate }}" @if($key == 0) selected @endif>{{ $tax->name }}</option>
                                        @endforeach
                                    @endif
                                </x-form.selectbox>

                                <div class="form-group col-md-3">
                                    <label for="order_discount">Order Discount</label>
                                    <input type="text" class="form-control" name="order_discount" id="order_discount"
                                           value="{{ isset($purchase_data) ? $purchase_data['purchase']['order_discount'] : '' }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="shipping_cost">Shipping Cost</label>
                                    <input type="text" class="form-control" name="shipping_cost" id="shipping_cost"
                                           value="{{ isset($purchase_data) ? $purchase_data['purchase']['shipping_cost'] : '' }}">
                                </div>

                                <div class="form-group col-md-12">
                                    <label for="shipping_cost">Note</label>
                                    <textarea class="form-control" name="note" id="note" cols="30"
                                              rows="3">{{ isset($purchase_data) ? $purchase_data['purchase']['note'] : '' }}</textarea>
                                </div>
                                <div class="col-md-12">
                                    <table class="table table-bordered">
                                        <thead class="bg-primary">
                                        <th><strong>Items</strong><span class="float-right" id="item">
                                                {{ isset($purchase_data) ? $purchase_data['purchase']['item'].'('.$purchase_data['purchase']['total_qty'].')': '0.00' }}</span>
                                        </th>
                                        <th><strong>Total</strong><span class="float-right" id="subtotal">
                                                {{ isset($purchase_data) ? $purchase_data['purchase']['total_cost']: '0.00' }}</span></th>
                                        <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">
                                                {{ isset($purchase_data) ? $purchase_data['purchase']['order_tax']: '0.00' }}</span>
                                        </th>
                                        <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">
                                                {{ isset($purchase_data) ? $purchase_data['purchase']['total_discount']: '0.00' }}</span>
                                        </th>
                                        <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">
                                                {{ isset($purchase_data) ? $purchase_data['purchase']['shipping_cost']: '0.00' }}</span>
                                        </th>
                                        <th><strong>Grand Total</strong><span class="float-right" id="grand_total">
                                                {{ isset($purchase_data) ? $purchase_data['purchase']['grand_total']: '0.00' }}</span>
                                        </th>
                                        </thead>
                                    </table>
                                </div>
                                <div class="col-md-12">
                                    <input type="hidden" name="total_qty" value="{{ isset($purchase_data) ? $purchase_data['purchase']['total_qty']: '' }}">
                                    <input type="hidden" name="total_free_qty" value="">
                                    <input type="hidden" name="total_discount" value="{{ isset($purchase_data) ? $purchase_data['purchase']['total_discount']: '' }}">
                                    <input type="hidden" name="total_tax" value="{{ isset($purchase_data) ? $purchase_data['purchase']['total_tax']: '' }}">
                                    <input type="hidden" name="total_cost" value="{{ isset($purchase_data) ? $purchase_data['purchase']['total_cost']: '' }}">
                                    <input type="hidden" name="item" value="{{ isset($purchase_data) ? $purchase_data['purchase']['item']: '' }}">
                                    <input type="hidden" name="order_tax" value="{{ isset($purchase_data) ? $purchase_data['purchase']['order_tax']: '' }}">
                                    <input type="hidden" name="grand_total" value="{{ isset($purchase_data) ? $purchase_data['purchase']['grand_total']: '' }}">
                                </div>
                                <div
                                    class="payment col-md-12 @if(isset($purchase_data)) {{ ($purchase_data['purchase']['payment_status'] == 3) ? 'd-none' : '' }} @else {{ 'd-none' }} @endif">
                                    <div class="row">
                                        <div class="form-group col-md-3 required">
                                            <label for="paid_amount">Paid Amount</label>
                                            <input type="text" class="form-control" name="paid_amount" id="paid_amount"
                                                   value="{{ isset($purchase_data) ? $purchase_data['purchase']['paid_amount']: '' }}">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label for="due_amount">Due Amount</label>
                                            <input type="text" class="form-control" id="due_amount"
                                                   value="{{ isset($purchase_data) ? $purchase_data['purchase']['due_amount']: '' }}" readonly>
                                        </div>
                                        <x-form.selectbox labelName="Payment Method" name="payment_method" onchange="account_list(this.value)" required="required" col="col-md-3"
                                                          class="selectpicker">
                                            @foreach (PAYMENT_METHOD as $key => $value)
                                                <option value="{{ $key }}" @if(isset($purchase_data))
                                                    {{ ($purchase_data['purchase']['payment_method'] == $key) ? 'selected' : '' }}
                                                    @endif>{{ $value }}</option>
                                            @endforeach
                                        </x-form.selectbox>
                                        <x-form.selectbox labelName="Account" name="account_id" required="required" col="col-md-3" class="selectpicker"/>
                                        <div
                                            class="form-group col-md-3 @if(isset($purchase_data)) {{ ($purchase_data['purchase']['payment_method'] != 2) ? 'd-none' : '' }} @else {{ 'd-none' }} @endif reference_no required">
                                            <label for="reference_no">Reference No.</label>
                                            <input type="text" class="form-control" name="reference_no" id="reference_no"
                                                   value="{{ isset($purchase_data) ? $purchase_data['purchase']['reference_no']: '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-grou col-md-12 text-center pt-5">
                                    <button type="button" class="btn btn-danger btn-sm mr-3" onClick="refreshPage()"><i class="fas fa-sync-alt"></i> Reset</button>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
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

            @if (isset($purchase_data))
            @if (!empty($purchase_data['products']) && (count($purchase_data['products']) > 0))
            var rownumber = $('#product_table tbody tr:last').index();
            for (rowindex = 0; rowindex <= rownumber; rowindex++) {
                product_cost.push(parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.product-cost').val()));
                var total_discount = parseFloat($('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(8)').text())
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
            @endif
            @endif

            $('#product_code_name').autocomplete({
                source: function (request, response) {
                    // Fetch data
                    $.ajax({
                        url: "{{url('purchase/product-autocomplete-search')}}",
                        type: 'POST',
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
                        notification('success', 'Successfully Added To Cart');
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
                return $("<li class='ui-autocomplete-row'></li>").data("item.autocomplete", item).append(item.label).appendTo(ul);
            };

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
                    $(this).val(1);
                    notification('error', 'Qunatity can\'t be less than 1');
                }
                checkQuantity($(this).val(), true, input = 2);
            });

            $('#product_table').on('keyup', '.free_qty', function () {
                var qty = $(this).closest('tr').find('.qty').val();
                checkQuantity(qty, true, input = 2);
            });

            $('#product_table').on('keyup', '.net_unit_cost', function () {
                rowindex = $(this).closest('tr').index();
                if ($(this).val() < 1 && $(this).val() != '') {
                    $(this).closest('tr').find('.net_unit_cost').val(1);
                    notification('error', 'Net unit price can\'t be less than 1');
                } else {
                    product_cost[rowindex] = $(this).closest('tr').find('.net_unit_cost').val();
                }
                var qty = $(this).closest('tr').find('.qty').val();
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

            @if (isset($purchase_data) && (count($purchase_data['products']) > 0))
            var count = "{{ count($purchase_data['products']) }}";
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
                                var qty = parseFloat($(this).closest('tr').find('.qty').val()) + 1;
                                $(this).closest('tr').find('.qty').val(qty);
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
                            cols += `<td class="discount text-right" style="display: none"></td>`;
                            cols += `<td class="tax text-right hidden" style="display: none"></td>`;
                            cols += `<td class="sub-total text-right"></td>`;
                            cols += `<td class="text-center">
                            <button type="button" class="btn btn-danger btn-sm remove-product small-btn"><i class="fas fa-trash"></i></button></td>`;
                            cols += `<input type="hidden" class="product-id" name="products[` + count + `][id]"  value="` + data.id + `">`;
                            cols += `<input type="hidden" class="product-variant-id" name="products[` + count + `][variant_id]"  value="` + data.variant_id + `">`;
                            cols += `<input type="hidden"  name="products[` + count + `][name]" value="` + data.name + `">`;
                            cols += `<input type="hidden" class="product-code" name="products[` + count + `][code]" value="` + data.code + `">`;
                            cols += `<input type="hidden" class="product-unit" name="products[` + count + `][unit]" value="` + temp_unit_name[0] + `">`;
                            cols += `<input type="hidden" class="discount-value" name="products[` + count + `][discount]">`;
                            cols += `<input type="hidden" class="tax-rate" name="products[` + count + `][tax_rate]" value="` + data.tax_rate + `">`;
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

                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').text((product_discount[rowindex] * quantity).toFixed(2));

                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.unit-name').text(unit_name[rowindex].slice(0, unit_name[rowindex].indexOf(",")));

                if (tax_method[rowindex] == 1) {
                    var net_unit_cost = row_product_cost - product_discount[rowindex];
                    var tax = net_unit_cost * quantity * (tax_rate[rowindex] / 100);
                    var sub_total = (net_unit_cost * quantity) + tax;

                } else {
                    var sub_total_unit = row_product_cost - product_discount[rowindex];
                    var net_unit_cost = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
                    var tax = (sub_total_unit - net_unit_cost) * quantity;
                    var sub_total = (sub_total_unit * quantity);
                }

                // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(6)').text(net_unit_cost.toFixed(2));
                if (input == 2) {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_cost').val(net_unit_cost.toFixed(2));
                }

                // $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(9)').text(tax.toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax').text(tax.toFixed(2));
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
                // $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(10)').text(sub_total.toFixed(2));
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
                var subtotal = parseFloat($('#total').text());
                var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
                var order_discount = parseFloat($('#order_discount').val());
                var shipping_cost = parseFloat($('#shipping_cost').val());
                // var labor_cost = parseFloat($('input[name="total_labor_cost"]').val());
                if (!order_discount) {
                    order_discount = 0.00;
                }
                if (!shipping_cost) {
                    shipping_cost = 0.00;
                }


                item = ++item + '(' + total_qty + ')';
                order_tax = (subtotal - order_discount) * (order_tax / 100);
                var grand_total = (subtotal + order_tax + shipping_cost) - order_discount;
                $('#item').text(item);
                $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
                $('#subtotal').text(subtotal.toFixed(2));
                $('#order_total_tax').text(order_tax.toFixed(2));
                $('input[name="order_tax"]').val(order_tax.toFixed(2));
                $('#order_total_discount').text(order_discount.toFixed(2));
                $('#shipping_total_cost').text(shipping_cost.toFixed(2));
                $('#grand_total').text(grand_total.toFixed(2));
                $('input[name="grand_total"]').val(grand_total.toFixed(2));
                if ($('#payment_status option:selected').val() == 1) {
                    $('#paid_amount').val(grand_total.toFixed(2));
                } else if ($('#payment_status option:selected').val() == 2) {
                    var paid_amount = $('#paid_amount').val();
                    $('#due_amount').val(parseFloat(grand_total - paid_amount).toFixed(2));
                }
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

            /** Start :: Payment Code **/
            $('#payment_status').on('change', function () {
                if ($(this).val() != 3) {
                    $('.payment').removeClass('d-none');
                    $('#paid_amount').val($('input[name="grand_total"]').val());
                    $('#due_amount').val(parseFloat(0).toFixed(2));
                } else {
                    $('#paid_amount').val(0);
                    $('.payment').addClass('d-none');
                }
            });


            $('#paid_amount').on('input', function () {
                var payable_amount = parseFloat($('input[name="grand_total"]').val());
                var paid_amount = parseFloat($(this).val());

                if (paid_amount > payable_amount) {
                    $('#paid_amount').val(payable_amount.toFixed(2));
                    notification('error', 'Paid amount cannot be bigger than grand total amount');
                }
                $('#due_amount').val((payable_amount - parseFloat($('#paid_amount').val())).toFixed(2));

            });
            /** End :: Payment Code **/

        });

        @if(isset($purchase_data))
        @if(!empty($purchase_data['purchase']['payment_method']))
        account_list("{{ $purchase_data['purchase']['payment_method'] }}", "{{ $purchase_data['purchase']['account_id'] }}");
        @endif
        @endif
        function account_list(payment_method, account_id = '') {
            $.ajax({
                url: "{{route('account.list')}}",
                type: "POST",
                data: {payment_method: payment_method, _token: _token},
                success: function (data) {
                    $('#purchase_store_form #account_id').html('');
                    $('#purchase_store_form #account_id').html(data);
                    $('#purchase_store_form #account_id.selectpicker').selectpicker('refresh');
                    if (account_id) {
                        $('#purchase_store_form #account_id').val(account_id);
                        $('#purchase_store_form #account_id.selectpicker').selectpicker('refresh');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        @if(isset($purchase_data))
        @if(!empty($purchase_data['purchase']['purchase_status']))
        received_qty("{{ $purchase_data['purchase']['purchase_status'] }}");
        @endif
        @endif
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
                var form = document.getElementById('purchase_store_form');
                var formData = new FormData(form);
                var url = "{{route('purchase.store')}}";
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

        function hold_data() {
            var rownumber = $('table#product_table tbody tr:last').index();
            if (rownumber < 0) {
                notification("error", "Please insert product to order table!")
            } else {
                var form = document.getElementById('purchase_store_form');
                var formData = new FormData(form);
                var url = "{{route('purchase.hold')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $('#hold-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
                        $('#hold-btn').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        $('#purchase_store_form').find('.is-invalid').removeClass('is-invalid');
                        $('#purchase_store_form').find('.error').remove();
                        notification(data.status, data.message);
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }

        }

        function refreshPage() {
            window.location.reload();
        }
    </script>
@endpush
