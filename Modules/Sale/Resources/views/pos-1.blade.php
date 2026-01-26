@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link rel="stylesheet" href="css/jquery-ui.css" />
    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css" />
    <style>
        .small-btn {
            width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
        }

        .small-btn i {
            font-size: 10px !important;
        }

        table td {
            vertical-align: middle !important;

        }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">

            <!--begin::Card-->
            <div class="card card-custom" style="background: none !important;">
                <div class="card-body p-0">
                    <div class="row">

                        <div class="col-md-7">
                            <form action="" id="sale_store_form" method="post" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" class="form-control" name="invoice_no" id="invoice_no" value="{{ $invoice_no }}"  />

                                <div class="card card-custom card-border">
                                    <div class="card-body" style="padding: 2rem 10px !important;min-height:100vh;">
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="customer_id" style="width: 100%;margin-bottom: 0.1rem;">
                                                    <span class="float-left">Date<b class="text-danger">*</b></span>
                                                </label>
                                                <input type="date" class="form-control" name="sale-date" id="" >

                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="customer_id" style="width: 100%;margin-bottom: 0.1rem;">
                                                    <span class="float-left">Customer<b class="text-danger">*</b></span>
                                                    @if(permission('customer-add'))<span class="float-right text-primary" style="cursor: pointer;"
                                                                                         onclick="showFormModal('Add New Customer','Save')"><i class="fas fa-plus text-primary"></i> New</span>
                                                    @endif
                                                </label>
                                                <select class="form-control selectpicker" name="customer_id" id="customer_id" data-live-search="true" ></select>

                                            </div>

                                            <div class="form-group col-md-12">
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-primary" id="basic-addon1"><i class="fas fa-barcode text-white"></i></span>
                                                    </div>

                                                    <input type="text" class="form-control" name="product_code_name" id="product_code_name" placeholder="Scan/Search by product name/code">

                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12 px-0">
                                            <table class="table table-bordered" id="product_table">
                                                <thead class="bg-primary">
                                                <th>Name</th>
                                                <th class="text-center">Unit</th>
                                                <th class="text-center">Price</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-right">Subtotal</th>
                                                <th></th>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot>
                                                <tr class="bg-primary">
                                                    <th colspan="3" class="font-weight-bolder">Total</th>
                                                    <th id="total-qty" class="text-center font-weight-bolder">0</th>
                                                    <th id="total" class="text-right font-weight-bolder">0.00</th>
                                                    <th></th>
                                                </tr>

                                                <tr>
                                                    <td colspan="5" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Shipping Cost: </td>
                                                    <td colspan="2">
                                                        <input type="text" class="form-control text-right" name="shipping_cost" id="shipping_cost" placeholder="0.00">
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="5" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Grand Total: </td>
                                                    <td colspan="2">
                                                        <input type="text" class="form-control text-right bg-secondary" name="grand_total" id="grand_total" placeholder="0.00" readonly>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td colspan="5" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Net Total: </td>
                                                    <td colspan="2">
                                                        <input type="text" class="form-control text-right bg-secondary" name="net_total" id="net_total" placeholder="0.00" readonly>

                                                    </td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-5">
                            <div class="card card-custom card-border" style="background: none;">
                                <div class="card-body px-0" style="min-height:100vh;">
                                    <div class="col-md-12 px-0">
                                        <div class="row">
                                            <div class="form-group col-md-6 px-1">
                                                <label for="brand_id">Brand</label>
                                                <select class="form-control selectpicker" name="brand_id" id="brand_id" onchange="load_products()" data-live-search="true">
                                                    <option value="0">Select Brand</option>
                                                    @if (!$brands->isEmpty())
                                                        @foreach ($brands as $brand)
                                                            <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="form-group col-md-6 px-1">
                                                <label for="category_id">Category</label>
                                                <select class="form-control selectpicker" name="category_id" id="category_id" onchange="load_products()" data-live-search="true">
                                                    <option value="0">Select Category</option>
                                                    @if (!$categories->isEmpty())
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row" id="product-section" style="position: relative;">
                                            <table class="table table-bordered product-table bg-white">
                                                <tbody>
                                                @include('sale::pos-product-list')
                                                </tbody>
                                            </table>
                                            <div id="product_loading" class="col-md-12 d-none" style="height: 100%;background: white;position: absolute;top:0;left:0;">
                                                <div class="col-md-12  text-center" style="padding-top: 45%;">
                                                    <i class="fa fa-spinner fa-spin fa-3x fa-fw text-primary" aria-hidden="true" style="font-size: 80px;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>

    @include('customer::modal')
@endsection

@push('scripts')
    <script src="js/jquery-ui.js"></script>
    <script src="js/moment.js"></script>
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
                var warehouse_id = $('#warehouse_id option:selected').val();
                var customer_id = $('#customer_id option:selected').val();
                var temp_data = $('#product_code_name').val();
              /*  if (!warehouse_id) {
                    $('#product_code_name').val(temp_data.substring(0, temp_data.length - 1));
                    notification('error', 'Please select showroom');
                }else if (!customer_id) {
                    $('#product_code_name').val(temp_data.substring(0, temp_data.length - 1));
                    notification('error', 'Please select customer');
                }*/
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
                // minLength: 3,
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
            $(document).on('click', '.edit-product', function() {
                rowindex = $(this).closest('tr').index();
                edit();
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
                if(rowindex == 0)
                {
                    $('#change_amount,#paid_amount,#net_total,#grand_total,#shipping_cost,#adjustment,#order_discount,#order_tax').val((0).toFixed(2));
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
            });


            $('input[name="shipping_cost"]').on('input', function() {
                calculateGrandTotal();
            });



            $('#paid_amount').on('input', function() {
                var payable_amount = parseFloat($('input[name="net_total"]').val());
                var paid_amount = parseFloat($('#paid_amount').val());

                if (paid_amount > payable_amount) {
                    $('#change_amount').val((paid_amount - payable_amount).toFixed(2));
                    //     $('#paid_amount').val(payable_amount.toFixed(2));
                    //     notification('error', 'Paid amount cannot be less than payable amount');
                }else{
                    $('#change_amount').val((0).toFixed(2));
                }


            });
            //Customer Form Data Save Code
            $(document).on('click', '#save-btn', function() {
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
                        $('#save-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function() {
                        $('#save-btn').removeClass('spinner spinner-white spinner-right');
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
                                customer_list(data.id);
                            }
                        }

                    },
                    error: function(xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });

            $('#payment_method').on('change',function(){
                if($(this).val() != 1){
                    $('.reference_no').removeClass('d-none');
                }else{
                    $('.reference_no').addClass('d-none');
                }
            });
        });

        function product_search_click(code) {
            var warehouse_id = $('#warehouse_id option:selected').val();
            var customer_id = $('#customer_id option:selected').val();
            // if (!warehouse_id) {
            //     notification('error', 'Please select showroom');
            // } else if (!customer_id) {
            //     notification('error', 'Please select customer');
            // } else {
            //     product_search(code);
            // }

        }

        function product_search(code) {
            $.ajax({
                url: '{{ route("sale.product.search") }}',
                type: 'POST',
                data: {
                    code: code,
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
                        cols += `<td><span class="edit-product text-primary mb-0" data-toggle="modal"
                        data-target="#editModal" style="cursor:pointer;">` + data.name + `</span><br><b>${data.code}</b><input type="hidden" name="products[` + count + `][name]" value="` + data.name + `"></td>`;
                        cols += `<td class=" text-right">
                                           <select class="form-control">
                                                <option value="">aa</option>
                                                <option value="">bb</option>
                                                <option value="">cc</option>
                                            </select>
                                    </td>`;
                        cols += `<td><input type="text" class="form-control text-right net_unit_price" name="products[` + count + `][net_unit_price]" id="products_` + count + `_net_unit_price"></td>`;

                        cols += `<td><input type="text" class="form-control qty text-center" name="products[` + count + `][qty]" id="products_` + count + `_qty" value="1"></td>`;

                        cols += `<td class="sub-total text-right"></td>`;
                        cols += `<td class="text-center"><i class="fas fa-trash remove-product text-danger" style="cursor:pointer;"></i></td>`;


                        cols += `<input type="hidden" class="product-id" name="products[` + count + `][id]"  value="` + data.id + `">`;
                        cols += `<input type="hidden" class="product-name" name="products[` + count + `][name]" value="` + data.name + `">`;
                        cols += `<input type="hidden" class="product-code" name="products[` + count + `][code]" value="` + data.code + `">`;
                        cols += `<input type="hidden" class="stock-qty" name="products[` + count + `][stock_qty]" value="` + data.qty + `">`;
                        cols += `<input type="hidden" class="product-unit" name="products[` + count + `][unit]" value="` + temp_unit_name[0] + `">`;
                        cols += `<input type="hidden" class="discount-value" name="products[` + count + `][discount]">`;
                        cols += `<input type="hidden" class="discount-rate" name="products[` + count + `][discount_rate]" value="${data.discount_rate}">`;
                        cols += `<input type="hidden" class="subtotal-value" name="products[` + count + `][subtotal]">`;

                        newRow.append(cols);
                        $('#product_table tbody').append(newRow);

                        product_price.push(parseFloat(data.price));
                        product_qty.push(data.qty);
                        product_discount.push(data.discount);
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
            // var row_product_code = $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(2)').text();
            // var pos = product_code.indexOf(row_product_code);
            var operator = unit_operator[rowindex].split(',');
            var operation_value = unit_operation_value[rowindex].split(',');


            if (!flag) {
                $('#editModal').modal('hide');
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.qty').val(sale_qty);
            }
            calculateProductData(sale_qty, input);

        }



        function calculateProductData(quantity, input = 2) {
            unitConversion();

            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(5)').text((product_discount[rowindex] * quantity).toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.discount-value').val((product_discount[rowindex] * quantity).toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-rate').val(tax_rate[rowindex].toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.unit-name').text(unit_name[rowindex].slice(0, unit_name[rowindex].indexOf(",")));

            if (tax_method[rowindex] == 1) {
                var net_unit_price = row_product_price - product_discount[rowindex];
                var tax = net_unit_price * quantity * (tax_rate[rowindex] / 100);
                var sub_total = (net_unit_price * quantity) + tax;
            } else {
                var sub_total_unit = row_product_price - product_discount[rowindex];
                var net_unit_price = (100 / (100 + tax_rate[rowindex])) * sub_total_unit;
                var tax = (sub_total_unit - net_unit_price) * quantity;
                var sub_total = sub_total_unit * quantity;
            }

            // $('#product_table tbody tr:nth-child('+(rowindex + 1)+')').find('td:nth-child(5)').text(net_unit_price.toFixed(2));
            if (input == 2) {
                $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.net_unit_price').val(row_product_price.toFixed(2));
            }
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(6)').text(tax.toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('.tax-value').val(tax.toFixed(2));
            $('#product_table tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(7)').text(sub_total.toFixed(2));
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

            var total_point = parseFloat($('#point').val()) || 0;
            var t_point     = parseFloat($('#t_point').val()) || 0;
            var use_point   = parseFloat($('#use_point').val()) || 0;
            var point_money = parseFloat($('#point_money').val()) || 0;

            if (!order_discount) {
                order_discount = 0.00;
            }
            if (!shipping_cost) {
                shipping_cost = 0.00;
            }
            if (!order_discount_per) {
                total_discount = order_discount;
                $('#order_discount_per').val(1);
            } else {
                total_discount = (subtotal * order_discount) / 100;
                $('#order_discount_per').val(2);
            }
            if (!adjustment) {
                adjustment = 0.00;
            }
            if (!adjustment_per) {
                total_adjustment = adjustment;
                $('#adjustment_per').val(1);
            } else {
                total_adjustment = -(adjustment);
                $('#adjustment_per').val(2);
            }

            //console.log(total_adjustment+' '+subtotal);
            var total_p         = t_point - use_point;
            var total_p_money   = use_point * point_money;
            item = ++item + '(' + total_qty + ')';
            order_tax = (subtotal - total_discount) * (order_tax / 100);
            var grand_total = (subtotal + order_tax + shipping_cost - total_p_money) - total_discount;
            var net_total = grand_total + total_adjustment;


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

            $('input[name="point"]').val(total_p.toFixed(2));
            if(total_point < use_point){
                $('#use_point').val(0);
                $('#point').val(t_point);
                notification('error', '{{__('Use Point cannot be bigger than Available Point')}}');
            }
            // alert(toral_p);

            $('input[name="paid_amount"]').val($('input[name="net_total"]').val());
            var paid_amount = $('#paid_amount').val() ? parseFloat($('#paid_amount').val()) : 0;

            if (paid_amount > net_total) {
                $('#change_amount').val((paid_amount - net_total).toFixed(2));
            }else{
                $('#change_amount').val((0).toFixed(2));
            }
        }

        function account_list(payment_method) {
            $.ajax({
                url: "{{route('account.list')}}",
                type: "POST",
                data: { payment_method: payment_method,_token: _token},
                success: function (data) {
                    $('#sale_store_form #account_id').empty().html(data);
                    $('#sale_store_form #account_id.selectpicker').selectpicker('refresh');
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function load_products(page = 1) {
            var brand_id = $('#brand_id option:selected').val();
            var category_id = $('#category_id option:selected').val();

            $.ajax({
                url: "{{url('pos-product-list')}}",
                type: "POST",
                data: {
                    page: page,
                    brand_id: brand_id,
                    category_id: category_id,
                    _token: _token
                },
                beforeSend: function() {
                    $('.product-table tbody').html('');
                    $('#product_loading').removeClass('d-none');
                },
                complete: function() {
                    $('#product_loading').addClass('d-none');
                },
                success: function(data) {
                    $('.product-table tbody').html(data);
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
                let url = "{{route('sale.store')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function(){
                        $('#pos-save-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function(){
                        $('#pos-save-btn').removeClass('spinner spinner-white spinner-right');
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
                                window.location.replace("{{ url('sale/pos-invoice') }}/"+data.id);
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
                notification("error","{{__('file.Please insert product to order table!')}}")
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
                        window.location.replace("{{ url('sale') }}");
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        }

    </script>
@endpush
