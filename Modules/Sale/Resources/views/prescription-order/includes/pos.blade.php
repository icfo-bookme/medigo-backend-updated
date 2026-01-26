@extends('layouts.app')

@section('title', $page_title)

@push('styles')
 @include('sale::includes.pos-styles')
@endpush

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">

            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-body">
                    <div class="row">

                        @include('sale::prescription-order.includes.product-list-section')


                        <div class="col-md-7">
                            <form action="" id="sale_store_form" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden"  class="form-control" name="invoice_no" id="invoice_no" value="{{ isset($sale_data) ?
                                $sale_data['sale']['invoice_no'] : $invoice_no }}"  />

                                <div class="card card-custom card-border">
                                    <div class="card-body">
                                        @include('sale::includes.pos-table')
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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
                            <x-form.textbox labelName="Quantity" name="edit_qty" required="required" col="col-md-12" />
                            <x-form.textbox labelName="Unit Discount" name="edit_discount" col="col-md-12" />
                            <x-form.textbox labelName="Unit Price" name="edit_unit_price" col="col-md-12" readonly />
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
    <!-- Start :: productVarientModal Modal -->
    <div class="modal fade" id="productVarientModal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
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
                <!-- Modal Body -->
                <div class="modal-body">
                    <table class="table table-bordered product-varient-table">
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <!-- /modal body -->
            </div>
            <!-- /modal content -->

        </div>
    </div>
    <!-- End :: productVarientModal Modal -->

    @include('sale::customer-form-modal')
@endsection

@push('scripts')

    <script>
        function refreshPage(){
            window.location.reload();
        }
    </script>

    <script src="js/jquery-ui.js"></script>
    <script src="js/bootstrap-datetimepicker.min.js"></script>
    <script>
        var count = 1;
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

        $(document).ready(function() {
            $("#kt_body").addClass("aside-minimize");
            //Ajax Pagination Data
            $(document).on('click', '.pagination a', function(event) {
                event.preventDefault();
                var page = $(this).attr('href').split('page=')[1];
                $('#hidden_page').val(page);
                $('li').removeClass('active');
                $(this).parent().addClass('active');
                load_products(page);
            });

            customer_list(1);
            $('#product_code_name').on('input', function() {
                var customer_id = $('#customer_id option:selected').val();
                var temp_data = $('#product_code_name').val();
                if (!customer_id) {
                    $('#product_code_name').val(temp_data.substring(0, temp_data.length - 1));
                    notification('error', 'Please select customer');
                }
            });

            $('#product_code_name').autocomplete({
                // source: "{{url('finish-goods-autocomplete-search')}}",
                source: function(request, response) {
                    // Fetch data
                    $.ajax({
                        url: "{{url('sale/product-autocomplete-search')}}",
                        type: 'post',
                        dataType: "json",
                        data: {
                            _token: _token,
                            search: request.term,
                            warehouse_id: $('#warehouse_id option:selected').val()
                        },
                        success: function(data) {
                            response(data);
                        }
                    });
                },
                minLength: 3,
                response: function(event, ui) {
                    if (ui.content.length == 1) {
                        var data = ui.content[0].code;
                        $(this).autocomplete("close");
                        product_search(data);
                    };
                },
                select: function(event, ui) {
                    var data = ui.item.code;
                    product_search(data);
                },
            }).data('ui-autocomplete')._renderItem = function(ul, item) {
                return $("<li class='ui-autocomplete-row'></li>")
                    .data("item.autocomplete", item)
                    .append(item.label)
                    .appendTo(ul);
            };

            //Edit Product
            $('#product_table').on('click', '.edit-product', function() {
                rowindex = $(this).closest('tr').index();
                edit();
            });

            //Update Edit Product Data
            $('#update-btn').on('click', function() {
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

            $('#product_table').on('keyup', '.qty', function() {
                rowindex = $(this).closest('tr').index();
                if ($(this).val() < 1 && $(this).val() != '') {
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ') .qty').val(1);
                    notification('error', 'Qunatity can\'t be less than 1');
                }
                checkQuantity($(this).val(), true, input = 2);
            });
            $('#product_table').on('keyup', '.net_unit_price', function() {
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

            $('#product_table').on('click', '.remove-product', function() {
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

            $('input[name="order_discount"]').on('input', function() {
                if (parseFloat($(this).val()) > parseFloat($('input[name="grand_total"]').val())) {
                    notification('error', 'Order discount can\'t exceed grand total amount');
                    $('input[name="order_discount"]').val(parseFloat(0));
                }
                calculateGrandTotal();
            });
            $('input[name="shipping_cost"]').on('input', function() {
                calculateGrandTotal();
            });
            $('input[name="adjustment"]').on('input', function() {
                calculateGrandTotal();
            });
            $('input[name="adjustment_per"]').on('input', function() {
                calculateGrandTotal();
            });
            $('input[name="order_discount_per"]').on('input', function() {

                //var order_discount_per = parseFloat($(this).val());

                if (parseFloat($(this).val()) > parseFloat($('input[name="grand_total"]').val())) {
                    notification('error', 'Order discount can\'t exceed grand total amount');
                    $('input[name="order_discount_per"]').val(parseFloat(0));
                }
                calculateGrandTotal();
            });
            $('select[name="order_tax_rate"]').on('change', function() {
                calculateGrandTotal();
            });

            $('#paid_amount').on('input', function() {
                var payable_amount = parseFloat($('input[name="net_total"]').val());
                var paid_amount = parseFloat($(this).val());

                if (paid_amount > payable_amount) {
                    $('#paid_amount').val(payable_amount.toFixed(2));
                    notification('error', 'Paid amount cannot be bigger than net total amount');
                }
                $('#due_amount').val((payable_amount - parseFloat($('#paid_amount').val())).toFixed(2));

            });
            //Customer Form Data Save Code
            $(document).on('click', '#save-customer-btn', function() {
                let form = document.getElementById('store_or_update_form');
                let formData = new FormData(form);
                $.ajax({
                    url: "{{route('customer.store.or.update')}}",
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function() {
                        $('#save-customer-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function() {
                        $('#save-customer-btn').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function(data) {
                        $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                        $('#store_or_update_form').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function(key, value) {
                                var key = key.split('.').join('_');
                                $('#store_or_update_form input#' + key).addClass('is-invalid');
                                $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                                $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                                $('#store_or_update_form #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                $('#store_or_update_modal').modal('hide');
                                customer_list(data.customer_id);
                                customer_previous_balance(customer_id)
                            }
                        }

                    },
                    error: function(xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });
        });

        function product_search_click(data, product_id) {
            if (data == '') {
                $('#productVarientModal').modal('show');
                load_products_varient('', product_id);
            } else {
                product_search(data);
                // var customer_id = $('#customer_id option:selected').val();
                // if (!customer_id) {
                //     notification('error', 'Please select customer');
                // } else {
                //     product_search(data);
                // }
            }
        }

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

                        cols += `<input type="hidden" class="sale_product_id" name="products[` + count + `][sale_product_id]"  value="` + data.variant_id + `">`;
                        cols += `<input type="hidden" class="product-id" name="products[` + count + `][id]"  value="` + data.id + `">`;
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
                        // product_discount.push('0.00');
                        product_discount.push(data.discount);
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
                    $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
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

            $.each(temp_unit_name, function(key, value) {
                $('#edit_unit').append('<option value="' + key + '">' + value + '</option>');
            });
            $('.selectpicker').selectpicker('refresh');
        }

        function calculateProductData(quantity, input = 2) {
            unitConversion();

            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(8)').text((product_discount[rowindex] ).toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] ).toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.unit-name').text(unit_name[rowindex].slice(0, unit_name[rowindex].indexOf(",")));

            if (tax_method[rowindex] == 1) {
                var net_unit_price = row_product_price - product_discount[rowindex];
                var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
                var sub_total = (net_unit_price * quantity) + tax;
            } else {
                // var sub_total_unit = row_product_price - product_discount[rowindex];
                var sub_total_unit = row_product_price;
                var net_unit_price =  sub_total_unit;
                var discount_amount = ((product_discount[rowindex] / 100) * net_unit_price) * quantity

                var tax = discount_amount;
                var sub_total = (row_product_price * quantity) - discount_amount;
            }

            // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(5)').text(net_unit_price.toFixed(2));
            if (input == 2) {
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(net_unit_price.toFixed(2));
            }
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(9)').text(tax.toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(10)').text(sub_total.toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.subtotal-value').val(sub_total.toFixed(2));

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
            $('.qty').each(function() {
                if ($(this).val() == '') {
                    total_qty += 0;
                } else {
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

        function calculateGrandTotal() {
            var total_discount = 0.00;
            var total_adjustment = 0.00;
            var item = $('#product_table tbody tr:last').index();
            var total_qty = parseFloat($('#total-qty').text());
            var subtotal = parseFloat($('#total').text());
            var order_tax = parseFloat($('select[name="order_tax_rate"]').val());
            var order_discount = parseFloat($('#order_discount').val());
            var shipping_cost = parseFloat($('#shipping_cost').val());
            var order_discount_per = $('#order_discount_per').is(":checked");
            var adjustment = parseFloat($('#adjustment').val());
            var adjustment_per = $('#adjustment_per').is(":checked");

            if (!order_discount) {
                order_discount = 0.00;
            }
            if (!shipping_cost) {
                shipping_cost = 0.00;
            }
            if (!order_discount_per) {
                total_discount = order_discount;
            } else {
                total_discount = (subtotal * order_discount) / 100;
            }
            if (!adjustment) {
                adjustment = 0.00;
            }
            if (!adjustment_per) {
                total_adjustment = adjustment;
            } else {
                total_adjustment = -(adjustment);
            }

            //console.log(total_adjustment+' '+subtotal);

            item = ++item + '(' + total_qty + ')';
            order_tax = (subtotal - total_discount) * (order_tax / 100);
            var grand_total = (subtotal + order_tax + shipping_cost + total_adjustment) - total_discount;
            var previous_due = parseFloat($('#previous_due').val());
            var net_total = grand_total + previous_due;

            $('#item').text(item);
            $('input[name="item"]').val($('#product_table tbody tr:last').index() + 1);
            $('#subtotal').text(subtotal.toFixed(2));
            $('#order_total_tax').text(order_tax.toFixed(2));
            $('input[name="order_tax"]').val(order_tax.toFixed(2));
            $('#order_total_discount').text(total_discount.toFixed(2));
            $('#shipping_total_cost').text(shipping_cost.toFixed(2));
            //$('#labor_total_cost').text(labor_cost.toFixed(2));
            $('#grand_total').text(grand_total.toFixed(2));
            $('input[name="grand_total"]').val(grand_total.toFixed(2));
            $('input[name="net_total"]').val(net_total.toFixed(2));
            // alert(net_total);
            $('input[name="paid_amount"]').val($('input[name="net_total"]').val());
            $('#due_amount').val(parseFloat(0).toFixed(2));
        }

        function customer_list(customer_id = ''){
            $.ajax({
                url: "{{route('customer.list')}}",
                type: "POST",
                data: {
                    _token: _token
                },
                success: function(data) {
                    $('#sale_store_form #customer_id').html('');
                    $('#sale_store_form #customer_id').html(data);
                    $('#sale_store_form #customer_id.selectpicker').selectpicker('refresh');
                    if(customer_id){
                        $('#sale_store_form #customer_id').val(customer_id);
                        $('#sale_store_form #customer_id.selectpicker').selectpicker('refresh');
                        customer_previous_balance(customer_id);
                    }
                },
                error: function(xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function customer_previous_balance(customer_id){
            //customer balance
            $.get('{{ url("customer/group-data") }}/' + customer_id, function(data) {
                customer_group_rate = (data / 100);
            });
            $.get('{{ url("customer/previous-balance") }}/' + customer_id, function(data) {
                $('#previous_due').val(parseFloat(data).toFixed(2));
            });
        }

        load_products();

        function load_products(page = 1){
            var prescription_order_id = $('#prescription_order_id option:selected').val();

            $.ajax({
                url: "{{url('prescription-order/product-list')}}",
                type: "POST",
                data: {
                    page: page,
                    prescription_order_id: prescription_order_id,
                    _token: _token
                },
                beforeSend: function() {
                    $('#product-section').html('');
                    $('#product_loading').removeClass('d-none');
                },
                complete: function() {
                    $('#product_loading').addClass('d-none');
                },
                success: function(data) {
                    console.log(data);
                    $('#product-section').html(data);
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        }
        function store_data(){
            var rownumber = $('table#product_table tbody tr:last').index();
            if (rownumber < 0) {
                notification("error","Please insert product to order table!")
            }else{
                let form = document.getElementById('sale_store_form');
                let formData = new FormData(form);
                let url = "{{route('pos.store')}}";
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
                        $('#sale_store_form').find('.is-invalid').removeClass('is-invalid');
                        $('#sale_store_form').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function (key, value) {
                                var key = key.split('.').join('_');
                                $('#sale_store_form input#' + key).addClass('is-invalid');
                                $('#sale_store_form textarea#' + key).addClass('is-invalid');
                                $('#sale_store_form select#' + key).parent().addClass('is-invalid');
                                $('#sale_store_form #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                //alert(data.id);

                                window.location.href="sale/pos-invoice/"+data.id;
                            }
                        }

                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }

        }

        function hold_data(){
            var rownumber = $('table#product_table tbody tr:last').index();
            if (rownumber < 0) {
                notification("error","Please insert product to order table!")
            }else{
                let form = document.getElementById('sale_store_form');
                let formData = new FormData(form);
                let url = "{{route('sale.hold')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function(){
                        $('#hold-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function(){
                        $('#hold-btn').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        $('#sale_store_form').find('.is-invalid').removeClass('is-invalid');
                        $('#sale_store_form').find('.error').remove();
                        notification(data.status, data.message);
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }

        }

    </script>
@endpush
