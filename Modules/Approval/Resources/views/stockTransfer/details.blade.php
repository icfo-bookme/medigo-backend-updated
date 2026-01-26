@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i>{{'Print'}}</button>
                        <a href="{{ route('stock.transfer.approve') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> {{'Back'}}</a>
                    </div>
                </div>
            </div>
            <div class="card card-custom" style="padding-bottom: 100px !important;">
                <div class="card-body" style="padding-bottom: 100px !important;">
                    <div class="col-md-12 col-lg-12"  style="width: 100%;">
                        <div id="invoice">
                            <style>
                                body,html {
                                    background: #fff !important;
                                    -webkit-print-color-adjust: exact !important;
                                }
                                .invoice {
                                    background: #fff !important;
                                }
                                .invoice header {
                                    padding: 10px 0;
                                    margin-bottom: 20px;
                                    border-bottom: 1px solid #036;
                                }
                                .invoice .company-details {
                                    text-align: right
                                }
                                .invoice .company-details .name {
                                    margin-top: 0;
                                    margin-bottom: 0;
                                }
                                .invoice .contacts {
                                    margin-bottom: 20px;
                                }
                                .invoice .invoice-to {
                                    text-align: left;
                                }
                                .invoice .invoice-to .to {
                                    margin-top: 0;
                                    margin-bottom: 0;
                                }
                                .invoice .invoice-details {
                                    text-align: right;
                                }
                                .invoice .invoice-details .invoice-id {
                                    margin-top: 0;
                                    color: #036;
                                }
                                .invoice main {
                                    padding-bottom: 50px
                                }
                                .invoice main .thanks {
                                    margin-top: -100px;
                                    font-size: 2em;
                                    margin-bottom: 50px;
                                }
                                .invoice main .notices {
                                    padding-left: 6px;
                                    border-left: 6px solid #036;
                                }
                                .invoice table {
                                    width: 100%;
                                    border-collapse: collapse;
                                    border-spacing: 0;
                                    margin-bottom: 20px;
                                }
                                .invoice table th {
                                    background: white;
                                    color: black;
                                    padding: 15px;
                                    border : 2px solid black;
                                }
                                .invoice table td {
                                    padding: 15px;
                                    border-bottom: 1px solid #fff
                                }
                                .invoice table th {
                                    white-space: nowrap;
                                }
                                .invoice table td h3 {
                                    margin: 0;
                                    color: #036;
                                }
                                .invoice table .qty {
                                    text-align: center;
                                }
                                .invoice table .price,
                                .invoice table .discount,
                                .invoice table .tax,
                                .invoice table .total {
                                    text-align: right;
                                }
                                .invoice table .no {
                                    color: black;
                                    background: white;
                                    border : 2px solid black !important;
                                }
                                .invoice table .total {
                                    background: white;
                                    color: black;
                                    border : 2px solid black !important;
                                }
                                .invoice table tbody tr:last-child td {
                                    border: none
                                }
                                .invoice table tfoot td {
                                    background: 0 0;
                                    border-bottom: none;
                                    white-space: nowrap;
                                    text-align: right;
                                    padding: 10px 20px;
                                    border-top: 1px solid #aaa;
                                    font-weight: bold;
                                }
                                .invoice table tfoot tr:first-child td {
                                    border-top: none
                                }
                                /* .invoice table tfoot tr:last-child td {
                                    color: #036;
                                    border-top: 1px solid #036
                                } */
                                .invoice table tfoot tr td:first-child {
                                    border: none
                                }
                                .invoice footer {
                                    width: 100%;
                                    text-align: center;
                                    color: #777;
                                    border-top: 1px solid #aaa;
                                    padding: 8px 0
                                }
                                .invoice a {
                                    content: none !important;
                                    text-decoration: none !important;
                                    color: #036 !important;
                                }
                                .page-header,
                                .page-header-space {
                                    height: 100px;
                                }
                                .page-footer,
                                .page-footer-space {
                                    height: 20px;

                                }
                                .page-footer {
                                    position: fixed;
                                    bottom: 0;
                                    width: 100%;
                                    text-align: center;
                                    color: #777;
                                    border-top: 1px solid #aaa;
                                    padding: 8px 0
                                }
                                .page-header {
                                    position: fixed;
                                    top: 0mm;
                                    width: 100%;
                                    border-bottom: 1px solid black;
                                }
                                .page {
                                    page-break-after: always;
                                }
                                .dashed-border{
                                    width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                }
                                @media screen {
                                    .no_screen {display: none;}
                                    .no_print {display: block;}
                                    thead {display: table-header-group;}
                                    tfoot {display: table-footer-group;}
                                    button {display: none;}
                                    body {margin: 0;}
                                }
                                @media print {
                                    body,
                                    html {
                                        -webkit-print-color-adjust: exact !important;
                                        font-family: sans-serif;
                                        /*margin-bottom: 100px !important;*/
                                    }
                                    .m-0 {
                                        margin: 0 !important;
                                    }
                                    h1,
                                    h2,
                                    h3,
                                    h4,
                                    h5,
                                    h6 {
                                        margin: 0 !important;
                                    }
                                    .no_screen {
                                        display: block !important;
                                    }
                                    .no_print {
                                        display: none;
                                    }
                                    a {
                                        content: none !important;
                                        text-decoration: none !important;
                                        color: #036 !important;
                                    }
                                    .text-center {
                                        text-align: center !important;
                                    }
                                    .text-left {
                                        text-align: left !important;
                                    }
                                    .text-right {
                                        text-align: right !important;
                                    }
                                    .float-left {
                                        float: left !important;
                                    }
                                    .float-right {
                                        float: right !important;
                                    }
                                    .text-bold {
                                        font-weight: bold !important;
                                    }
                                    .invoice {
                                        /* font-size: 11px!important; */
                                        overflow: hidden !important;
                                        background: #fff !important;
                                        margin-bottom: 100px !important;
                                    }
                                    .invoice footer {
                                        position: absolute;
                                        bottom: 0;
                                        left: 0;
                                    }
                                    .hidden-print {
                                        display: none !important;
                                    }
                                    .dashed-border{
                                        width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                    }
                                }
                                @page {
                                    margin: 5mm 5mm;
                                }
                            </style>
                            <div class="invoice overflow-auto">
                                <div>
                                    <table>
                                        <tr>
                                            <td width="20%"><img src="{{ asset('storage/'.LOGO_PATH.config('settings.logo'))}}" style="max-width: 200px;max-height:100px;" alt="Logo" /></td>
                                            <td width="80%" class="text-right">
                                                <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                                @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>{{'Contact No'}}.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>{{'Email'}}: </b>{{ config('settings.email') }}@endif</p>@endif
                                                <p style="font-weight: normal;margin:0;"><b>{{'Date'}}: </b>{{ date('d-M-Y') }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                    <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div><br/>
                                    <table class="invoice">
                                        <thead>
                                        <tr class="text-center">
                                            <th>{{'Transfer Date'}}</th>
                                            <th>{{'Invoice No'}}</th>
                                            <th>{{'Transfer Warehouse'}}</th>
                                            <th>{{'Receive Warehouse'}}</th>
                                            <th>{{'Total Quantity'}}</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr class="text-center">
                                            <td class="no"><button class="btn btn-primary btn-block">{{$details->transfer_date}}</button></td>
                                            <td>{{$details->invoice_no}}</td>
                                            <td>{{$details->transferWarehouse->name}}</td>
                                            <td>{{$details->receiveWarehouse->name}}</td>
                                            <td class="no"><button class="btn btn-primary btn-block">{{$details->total_qty}}</button></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                    <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div><br/>
                                    @if(isset($details) && !$details->stockTransferWarehouseProductList->isEmpty())
                                        <table class="invoice">
                                            <thead>
                                            <tr class="text-center">
                                                <th>{{'Product Name'}}</th>
                                                <th>{{'Product Code'}}</th>
                                                <th>{{'Unit'}}</th>
                                                <th>{{'Quantity'}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($details->stockTransferWarehouseProductList as $value)
                                                <tr class="text-center">
                                                    <td class="no">{{$value->product->name}}</td>
                                                    <td class="no">{{$value->product->code}}</td>
                                                    <td class="no">{{$value->product->unit->unit_code}}</td>
                                                    <td class="no">{{$value->qty}}</td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                    <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                                    <table class="invoice">
                                        <tbody>
                                        <tr>
                                            <td class="text-left">
                                                <div class="font-size-10 float-left">
                                                    <p style="margin:0;padding:0;">{{ $details->createdBy->username }}<br> {{ date('d-M-Y h:i:s A',strtotime($details->transfer_date)) }}</p>
                                                    <p class="dashed-border"></p>
                                                    <p style="margin:0;padding:0;text-transform: capitalize;font-weight:normal;">Created By</p>
                                                </div>
                                            </td>
                                            <td class="text-right">
                                                <div class="font-size-10 float-right">
                                                    <p style="margin:0;padding:0;">{{ $details->approvedBy->username }}<br> {{ date('d-M-Y h:i:s A',strtotime($details->transfer_date)) }}</p>
                                                    <p class="dashed-border"></p>
                                                    <p style="margin:0;padding:0;text-transform: capitalize;font-weight:normal;">Approved By</p>
                                                </div>
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{asset('js/jquery.printarea.js')}}"></script>
    <script>
        $(document).ready(function () {
            $(document).on('click','#print-invoice',function(){
                var mode = 'iframe'; // popup
                var close = mode == "popup";
                var options = {
                    mode: mode,
                    popClose: close
                };
                $("#invoice").printArea(options);
            });
        });
    </script>
@endpush
