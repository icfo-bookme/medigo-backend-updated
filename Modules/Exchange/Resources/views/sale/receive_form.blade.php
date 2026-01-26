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
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('stock.exchange.list') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form id="exchange_delivery_form" method="POST">
                            @csrf
                            <input type="hidden" id="update_id" name="exchange_id" value="{{$exchange->id}}"/>
                            <div class="row">
                                <div class="form-group col-md-3 required">
                                    <label for="memo_no">Invoice No</label>
                                    <input type="text" class="form-control bg-primary text-white" id="invoice_no" name="invoice_no" value="{{$invoiceNo}}" readonly/>
                                </div>
                                <div class="form-group col-md-3 required">
                                    <label for="exchange_date">Sale Date</label>
                                    <input type="date" class="form-control date bg-primary text-white" id="exchange_date" name="exchange_date" value="{{$exchange->exchange_date}}" readonly/>
                                </div>
                                <div class="form-group col-md-3 required">
                                    <label for="delivery_date">Delivery Date</label>
                                    <input type="date" class="form-control date bg-primary text-white" id="receive_date" name="receive_date" value="{{date('Y-m-d')}}" readonly/>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="">
                                            <table class="table table-bordered quarterSelectTable" id="exchangeTable">
                                                <thead class="bg-primary text-center">
                                                    <th width="20%">Product</th>
                                                    <th width="8%">Unit</th>
                                                    <th width="8%">Price</th>
                                                    <th width="3%">Exchange Qty</th>
                                                    <th width="3%">Received Qty</th>
                                                    <th width="3%">Receive Qty</th>
                                                    <th width="10%">Total Price</th>
                                                </thead>
                                                <tbody>
                                                @foreach($exchange->exchange_products as $key => $item)
                                                    <tr class="text-center">
                                                        <td>
                                                            <select class="form-control productDetails bg-primary text-white"
                                                                    id="exchange_{{$key}}_product_id" name="exchange[{{$key}}][product_id]">
                                                                <option value="{{$item->product->id}}">{{$item->product->name}} -
                                                                    {{$item->old_product_code}}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" class="form-control bg-primary text-white"
                                                                   id="exchange_{{$key}}_product_code" name="exchange[{{$key}}][product_code]"
                                                                   value="{{$item->old_product_code}}" readonly/>
                                                            <input type="text" class="form-control bg-primary text-white text-center"
                                                                   id="exchange_{{$key}}_unit_name" name="exchange[{{$key}}][unit_name]"
                                                                   value="{{$item->product_variant->unit->unit_code}}" readonly/>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control price bg-primary text-white text-center"
                                                                   id="exchange_{{$key}}_price" name="exchange[{{$key}}][price]"
                                                                   value="{{$item->old_price}}" readonly/>
                                                        </td>
                                                        <td style="display:none">
                                                            <input type="text" class="form-control bg-primary text-white text-center"
                                                                   id="exchange_{{$key}}_stock_qty" name="exchange[{{$key}}][stock_qty]"
                                                                   value="{{$item->old_exchange_qty}}" readonly/>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control bg-primary text-white text-center"
                                                                   id="exchange_{{$key}}_qty" name="exchange[{{$key}}][qty]"
                                                                   value="{{$item->old_exchange_qty}}" readonly/>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control bg-primary text-white"
                                                                   id="exchange_{{$key}}_delivered_qty" value="{{$item->received_qty}}"
                                                                   readonly/>
                                                        </td>
                                                        @if($item->old_exchange_qty == $item->received_qty)
                                                            <td>All Received
                                                                <input type="hidden" class="form-control delivery_qty"
                                                                       id="exchange_{{$key}}_delivery_qty" data-price="exchange_{{$key}}_price"
                                                                       data-stock_qty="exchange_{{$key}}_stock_qty" data-qty="exchange_{{$key}}_qty"
                                                                       data-delivered_qty="exchange_{{$key}}_delivered_qty"
                                                                       data-sub_total="exchange_{{$key}}_sub_total"
                                                                       name="exchange[{{$key}}][receive_qty]" readonly/>
                                                            </td>
                                                        @else
                                                            <td>
                                                                <input type="text" class="form-control delivery_qty"
                                                                       id="exchange_{{$key}}_delivery_qty" data-price="exchange_{{$key}}_price"
                                                                       data-stock_qty="exchange_{{$key}}_stock_qty" data-qty="exchange_{{$key}}_qty"
                                                                       data-delivered_qty="exchange_{{$key}}_delivered_qty"
                                                                       data-sub_total="exchange_{{$key}}_sub_total"
                                                                       name="exchange[{{$key}}][receive_qty]"/>
                                                            </td>
                                                        @endif

                                                        <td>
                                                            <input type="text" class="form-control sub_total bg-primary text-white text-right"
                                                                   id="exchange_{{$key}}_sub_total" name="exchange[{{$key}}][sub_total]" readonly/>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot class="bg-primary text-white text-center">
                                                <th colspan="5"></th>
                                                <th id="total_delivery_qty_view">0</th>
                                                <th id="total_delivery_sub_total_view">0</th>
                                                <input type="hidden" id="total_delivery_qty" name="total_receive_qty"/>
                                                <input type="hidden" id="total_delivery_sub_total" name="total_delivery_sub_total"/>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 text-center pt-5">
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="deliveryData()"><i class="fas fa-save"></i>Received
                                    </button>
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
        $(document).on('input', '.delivery_qty', function () {
            let value = parseFloat($(this).val()) || 0;
            let price = parseFloat($('#' + $(this).data('price')).val()) || 0;
            let exchangeQty = parseFloat($('#' + $(this).data('qty')).val()) || 0;

            // Check if the entered value exceeds the exchange quantity
            if (value > exchangeQty) {
                $(this).val('');  // Clear the input field
                $('#' + $(this).data('sub_total')).val('');  // Clear the subtotal
                notification('error', 'Quantity can\'t be greater than the exchange quantity');
            } else {
                $('#' + $(this).data('sub_total')).val((price * value).toFixed(2));  // Update the subtotal with the calculated value
            }

            calculation();
        });


        function calculation() {
            let subTotal = 0;
            let deliveryQty = 0;

            $('.sub_total').each(function () {
                let value = parseFloat($(this).val()) || 0;
                subTotal += value;
            });

            $('.delivery_qty').each(function () {
                let value = parseFloat($(this).val()) || 0;
                deliveryQty += value;
            });

            // Update the UI with the calculated values
            $('#total_delivery_qty_view').text(deliveryQty.toFixed(2));
            $('#total_delivery_sub_total_view').text(subTotal.toFixed(2));
            $('#total_delivery_qty').val(deliveryQty.toFixed(2));
            $('#total_delivery_sub_total').val(subTotal.toFixed(2));
        }

        function deliveryData() {
            let form = document.getElementById('exchange_delivery_form');
            let formData = new FormData(form);
            $.ajax({
                url: "{{route('stock.exchange.receive.store')}}",
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
                    $('#exchange_delivery_form').find('.is-invalid').removeClass('is-invalid');
                    $('#exchange_delivery_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#exchange_delivery_form input#' + key).addClass('is-invalid');
                            $('#exchange_delivery_form textarea#' + key).addClass('is-invalid');
                            $('#exchange_delivery_form select#' + key).parent().addClass('is-invalid');
                            $('#exchange_delivery_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
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
    </script>
@endpush
