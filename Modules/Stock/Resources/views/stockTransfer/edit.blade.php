@extends('layouts.app')
@section('title', $page_title)
@push('styles') <link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css" /> @endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar"><a href="{{ route('stock.transfer') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i>{{'Back'}}</a></div>
                </div>
            </div>
            <div class="card card-custom" style="padding-bottom: 100px !important;">
                <div class="card-body">
                    <form id="transfer_form" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <input type="hidden" name="update_id" value="{{$edit->id}}"/>
                            <div class="col-md-4 form-group">
                                <label for="transfer_date">{{'Transfer Date'}}</label>
                                <input type="date" class="form-control" id="transfer_date" name="transfer_date" value="{{$edit->transfer_date}}"/>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="transfer_warehouse_id">{{'Transfer Warehouse'}}</label>
                                <select class="form-control transfer_warehouse_id" id="transfer_warehouse_view">
                                    <option value="">{{'Please Select'}}</option>
                                    @foreach($warehouses as $value)
                                        <option value = "{{$value->id}}" @if($value->id == $edit->transfer_warehouse_id) selected="selected" @endif>{{$value->name}}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" id="transfer_warehouse_id" name="transfer_warehouse_id" value = "{{$edit->transfer_warehouse_id}}"/>
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="receive_warehouse_id">{{'Receive Warehouse'}}</label>
                                <select class="form-control" id="receive_warehouse_id" name="receive_warehouse_id">
                                    <option value="">{{'Please Select'}}</option>
                                    @foreach($warehouses as $value)
                                        <option value = "{{$value->id}}" @if($value->id == $edit->receive_warehouse_id) selected="selected" @endif>{{$value->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 pb-5">
                                <table class="table table-bordered" id="stock-transfer-table">
                                    <thead class="bg-primary">
                                    <tr class="text-center">
                                        <th width="25%">{{'Name'}}</th>
                                        <th width="25%">{{'Unit'}}</th>
                                        <th width="25%">{{'Stock Qty'}}</th>
                                        <th width="25%">{{'Receive Qty'}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @if(isset($edit) && !$edit->stockTransferWarehouseProductList->isEmpty())
                                        @foreach($edit->stockTransferWarehouseProductList as $key => $value)
                                            @php
                                                $qty  = \Modules\Stock\Entities\WarehouseProduct::where(['warehouse_id' => $edit->transfer_warehouse_id , 'product_id' => $value->product_id])->firstOrFail();
                                            @endphp
                                            <tr class = "text-center">
                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_product_name" value="{{$value->product->name}}" readonly/><input type="hidden" id="transfer_{{$key}}_product_id" name="transfer[{{$key}}][product_id]" value = "{{$value->product_id}}"/></td>
                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_unit_name" value="{{$value->product->unit->unit_code}}" readonly/></td>
                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_stock_qty" value="{{$qty->qty}}" readonly/></td>
                                                <td><input type="text" class="form-control qty" id="transfer_{{$key}}_qty" data-stock_qty="transfer_{{$key}}_stock_qty" name="transfer[{{$key}}][qty]" value = "{{$value->qty}}" /></td>
                                            </tr>
                                        @endforeach
                                    @endif
{{--                                    @if($edit->transfer_item == 1)--}}
{{--                                        @foreach($edit->stockTransferWarehouseProductList as $key => $value)--}}
{{--                                            @php--}}
{{--                                            $qty  = \Modules\Stock\Entities\WarehouseProduct::where(['warehouse_id' => $edit->transfer_warehouse_id , 'product_id' => $value->product_id])->firstOrFail();--}}
{{--                                            @endphp--}}
{{--                                            <tr class = "text-center">--}}
{{--                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_product_name" value="{{$value->product->product_name}}" readonly/><input type="hidden" id="transfer_{{$key}}_product_id" name="transfer[{{$key}}][product_id]" value = "{{$value->product_id}}"/></td>--}}
{{--                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_unit_name" value="{{$value->product->unit->unit_code}}" readonly/></td>--}}
{{--                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_stock_qty" value="{{$qty->qty}}" readonly/></td>--}}
{{--                                                <td><input type="text" class="form-control qty" id="transfer_{{$key}}_qty" data-stock_qty="transfer_{{$key}}_stock_qty" name="transfer[{{$key}}][qty]" value = "{{$value->qty}}" /></td>--}}
{{--                                            </tr>--}}
{{--                                        @endforeach--}}
{{--                                    @else--}}
{{--                                        @foreach($edit->stockTransferWarehouseMaterialList as $key => $value)--}}
{{--                                            @php--}}
{{--                                                $qty  = \Modules\Stock\Entities\WarehouseMaterial::where(['warehouse_id' => $edit->transfer_warehouse_id , 'material_id' => $value->material_id])->firstOrFail();--}}
{{--                                            @endphp--}}
{{--                                            <tr class = "text-center">--}}
{{--                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_material_name" value="{{$value->material->material_name}}" readonly/><input type="hidden" id="transfer_{{$key}}_material_id" name="transfer[{{$key}}][material_id]" value = "{{$value->material_id}}"/></td>--}}
{{--                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_unit_name" value="{{$value->material->unit->unit_code}}" readonly/></td>--}}
{{--                                                <td><input type="text" class="form-control bg-primary text-white" id="transfer_{{$key}}_stock_qty" value="{{$qty->qty}}" readonly/></td>--}}
{{--                                                <td><input type="text" class="form-control qty" id="transfer_{{$key}}_qty" data-stock_qty="transfer_{{$key}}_stock_qty" name="transfer[{{$key}}][qty]" value = "{{$value->qty}}"/></td>--}}
{{--                                            </tr>--}}
{{--                                        @endforeach--}}
{{--                                    @endif--}}
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td></td>
                                        <td  colspan="2"><button type="button" class="text-right btn btn-primary btn-block"><b>{{'Total Quantity'}}</b></button></td>
                                        <td><button type="button" class="text-left btn btn-primary btn-block"><input type="hidden" id="total_qty" name="total_qty" value="{{$edit->total_qty}}"/> <b><span id="total_qty_show">{{$edit->total_qty}}</span></b></button></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row pt-5">
                            <div class="form-group col-md-12 text-center px-0">
                                <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="transferUpdateData()"><i class="fas fa-save"></i>{{'Submit'}}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{asset('js/bootstrap-datetimepicker.min.js')}}"></script>
    <script type="text/javascript">
        @if(!empty($edit)) transferItem(); @endif
        function _(x){
            return document.getElementById(x);
        }
        function transferItem(){
            $('#transfer_item_view').addClass( 'bg-primary text-white' );
            $('#transfer_item_view').prop( "disabled", true );
            $('#transfer_warehouse_view').addClass( 'bg-primary text-white' );
            $('#transfer_warehouse_view').prop( "disabled", true );
        }
        $(document).on('input','.qty',function(){
            let stockQty = parseFloat(_($(this).data('stock_qty')).value);
            let value    = $(this).val();
            if(stockQty >= value){
            }else{
                $(this).val('');
                notification('error','Transfer Quantity Can\'t Be Greater Then Stock Quantity');
            }
            calculation();
        })
        function calculation(){
            let qty = 0;
            $('.qty').each(function(){
                if($(this).val() == ''){
                    qty += + 0;
                }else{
                    qty += + $(this).val();
                }
            });
            _('total_qty').value          = qty;
            _('total_qty_show').innerText = qty;
        }
        function transferUpdateData(){
            let form     = _('transfer_form');
            let formData = new FormData(form);
            let url      = "{{route('stock.transfer.update')}}";
            $.ajax({
                url         : url,
                type        : "POST",
                data        : formData,
                dataType    : "JSON",
                contentType : false,
                processData : false,
                cache       : false,
                beforeSend  : function(){
                    $('#save-btn').addClass('spinner spinner-white spinner-right');
                },
                complete    : function(){
                    $('#save-btn').removeClass('spinner spinner-white spinner-right');
                },
                success     : function (data) {
                    $('#transfer_form').find('.is-invalid').removeClass('is-invalid');
                    $('#transfer_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#transfer_form input#' + key).addClass('is-invalid');
                            $('#transfer_form textarea#' + key).addClass('is-invalid');
                            $('#transfer_form select#' + key).parent().addClass('is-invalid');
                            $('#transfer_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') { window.location.replace("{{ route('stock.transfer') }}"); }
                    }
                },
                error       : function (xhr, ajaxOption, thrownError) { console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
            });
        }
    </script>
@endpush
