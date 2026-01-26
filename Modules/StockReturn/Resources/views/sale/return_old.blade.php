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
                        <a href="{{ route('sale') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form action="" id="sale_return_form" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" id="update_id" name="sale_id" value="{{$sale->id}}"/>
                            <div class="row">
                                <div class="form-group col-md-4 required">
                                    <label for="memo_no">Invoice No.</label>
                                    <input type="text" class="form-control bg-primary text-white" id="invoice_no" name="invoice_no" value="{{$invoiceNo}}" readonly/>
                                </div>
                                <div class="form-group col-md-4 required">
                                    <label for="return_date">Return Date</label>
                                    <input type="date" class="form-control date bg-primary text-white" id="return_date" name="return_date" value="{{date("Y-m-d")}}"/>
                                </div>
                                <div class="form-group col-md-4 required">
                                    <label for="customer_id">Customer</label>
                                    <select class="form-control bg-primary text-white" id="customer_id" name="customer_id" disabled>
                                        <option value="{{$sale->customer_id}}">{{ $sale->customer->company_name.' ('.$sale->customer->name.') ' }}</option>
                                    </select>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <div class="">
                                            <table class="table table-bordered quarterSelectTable" id="saleTable">
                                                <thead class="bg-primary text-center">
                                                <th width="13%">Warehouse</th>
                                                <th width="12%">Product</th>
                                                <th width="5%">Unit</th>
                                                <th width="10%">Price</th>
                                                <th width="10%">Sale Qty</th>
                                                <th width="10%">Returned Qty</th>
                                                <th width="10%">Return Qty</th>
                                                <th width="10%">Total Price</th>
                                                </thead>
                                                <tbody>
                                                @foreach($sale->saleProductList as $key => $item)
                                                    {{--                                                    @php--}}
                                                    {{--                                                        $availableQty = \Modules\Stock\Entities\WarehouseProduct::where(['warehouse_id' => $item->warehouse_id,'product_id' => $item->product_id])->first();--}}
                                                    {{--                                                    @endphp--}}
                                                    <tr class="text-center">
                                                        <td>
                                                            <select class="form-control warehouseProduct bg-primary text-white" id="sale_{{$key}}_warehouse_id"
                                                                    name="sale[{{$key}}][warehouse_id]">
                                                                <option value="{{$sale->warehouse->id }}">{{$sale->warehouse->name}}</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select class="form-control productDetails bg-primary text-white" id="sale_{{$key}}_product_id"
                                                                    name="sale[{{$key}}][product_id]">
                                                                <option value="{{$item->product->id}}">{{$item->product->name}}</option>
                                                            </select>
                                                        </td>
                                                        <td><input type="text" class="form-control bg-primary text-white" id="sale_{{$key}}_unit_name"
                                                                   name="sale[{{$key}}][unit_name]" value="{{$item->unit->unit_name}}" readonly/></td>
                                                        <td><input type="text" class="form-control price bg-primary text-white" id="sale_{{$key}}_price"
                                                                   name="sale[{{$key}}][price]" value="{{$item->net_unit_price}}" readonly/></td>
                                                        <td><input type="text" class="form-control bg-primary text-white" id="sale_{{$key}}_sale_qty" value="{{$item->qty}}"
                                                                   readonly/></td>
                                                        <td><input type="text" class="form-control bg-primary text-white" id="sale_{{$key}}_returned_qty"
                                                                   value="{{$item->return_qty}}" readonly/></td>
                                                        <td><input type="text" class="form-control return_qty" id="sale_{{$key}}_return_qty" data-price="sale_{{$key}}_price"
                                                                   data-sale_qty="sale_{{$key}}_sale_qty" data-returned_qty="sale_{{$key}}_returned_qty"
                                                                   data-sub_total="sale_{{$key}}_sub_total" name="sale[{{$key}}][return_qty]"/></td>
                                                        <td><input type="text" class="form-control sub_total bg-primary text-white" id="sale_{{$key}}_sub_total"
                                                                   name="sale[{{$key}}][sub_total]" readonly/></td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot class="bg-primary text-white text-center">
                                                <th colspan="6"></th>
                                                <th id="total_return_qty_view">0</th>
                                                <th id="total_return_sub_total_view">0</th>
                                                <input type="hidden" id="total_return_qty" name="total_return_qty"/>
                                                <input type="hidden" id="total_return_sub_total" name="total_return_sub_total"/>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-12 text-center pt-5">
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="returnData()"><i class="fas fa-save"></i>Return
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
        function _(x) {
            return document.getElementById(x);
        }

        $(document).on('input', '.return_qty', function () {
            let price = parseFloat(_($(this).data('price')).value);
            let saleQty = parseFloat(_($(this).data('sale_qty')).value);
            let returnedQty = _($(this).data('returned_qty')).value;
            let value = $(this).val();
            if (saleQty >= +returnedQty + +value) {
                _($(this).data('sub_total')).value = price * value;
            } else {
                $(this).val('');
                _($(this).data('sub_total')).value = '';
                notification('error', 'Quantity Can\'t Be Greater Then Sale Quantity');
            }
            calculation();
        });

        function calculation() {
            let subTotal = 0;
            let returnQty = 0;
            $('.sub_total').each(function () {
                if ($(this).val() == '') {
                    subTotal += +0;
                } else {
                    subTotal += +$(this).val();
                }
            });
            $('.return_qty').each(function () {
                if ($(this).val() == '') {
                    returnQty += +0;
                } else {
                    returnQty += +$(this).val();
                }
            });
            _('total_return_qty_view').innerText = returnQty;
            _('total_return_sub_total_view').innerText = subTotal;
            _('total_return_qty').value = returnQty;
            _('total_return_sub_total').value = subTotal;
        }

        function returnData() {
            let form = document.getElementById('sale_return_form');
            let formData = new FormData(form);
            let url = "{{route('sale.return.store')}}";
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
                    $('#sale_return_form').find('.is-invalid').removeClass('is-invalid');
                    $('#sale_return_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#sale_return_form input#' + key).addClass('is-invalid');
                            $('#sale_return_form textarea#' + key).addClass('is-invalid');
                            $('#sale_return_form select#' + key).parent().addClass('is-invalid');
                            $('#sale_return_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
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
    </script>
@endpush

