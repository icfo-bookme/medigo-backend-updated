@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-primary btn-sm mr-3" id="print-invoice"> <i class="fas fa-print"></i>{{__('file.Print')}}</button>
                        <a href="{{route('providentFund')}}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i>{{__('file.Back')}}</a>
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
                                    /* position: relative; */
                                    background: #fff !important;
                                    /* min-height: 680px; */
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
                                    background: #034d97;
                                    color: white;
                                    padding: 10px;
                                    border : 2px solid black;
                                }
                                .invoice table td {
                                    padding: 9px;
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
                                        /* background: #fff !important; */
                                        -webkit-print-color-adjust: exact !important;
                                        font-family: sans-serif;
                                        /* font-size: 12px !important; */
                                        margin-bottom: 100px !important;
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
                                        /* page-break-after: always */
                                    }
                                    /* .invoice>div:last-child {
                                        page-break-before: always
                                    } */
                                    .hidden-print {
                                        display: none !important;
                                    }
                                    .dashed-border{
                                        width:180px;height:2px;margin:0 auto;padding:0;border-top:1px dashed #454d55 !important;
                                    }
                                }
                                @page {
                                    /* size: auto; */
                                    margin: 5mm 5mm;

                                }
                            </style>
                            <div class="invoice overflow-auto">
                                <div>
                                    <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                                    <table>
                                        <tr>
                                            <td width="20%">
                                                <div class="invoice-to">
                                                    <div class="text-grey-light text-right"><b>{{__('Customer Name')}} :</b></div>
                                                    <div class="text-grey-light text-right"><b>{{__('Customer ID')}} :</b></div>
                                                    <div class="text-grey-light text-right"><b>{{__('Customer Department')}} :</b></div>
                                                    <div class="text-grey-light text-right"><b>{{__('Customer Designation')}} :</b></div>
                                                </div>
                                            </td>
                                            <td width="25%">
                                                <div class="invoice-to">
                                                    <div class="text-grey-light text-left">{{$employee->employee->name}}</div>
                                                    <div class="text-grey-light text-left">{{$employee->employee->employee_id}}</div>
                                                    <div class="text-grey-light text-left">{{$employee->employee->department->name}}</div>
                                                    <div class="text-grey-light text-left">{{$employee->employee->designation->name}}</div>
                                                </div>
                                            </td>
                                            <td width="25%" class="text-left">
                                                <h3>{{__('All Provident Fund Report')}}</h3>
                                            </td>
                                            <td width="15%" class="text-right">
                                                <div class="m-0 date"><b>{{__('Subscription Amount')}}:</b></div>
                                            </td>
                                            <td width="15%" class="text-right">
                                                <div class="m-0 date text-left">{{$employee->provident_fund_contribution}}/-TK</div>
                                            </td>
                                        </tr>
                                    </table>
                                        <table  style="width:100% !important;" class="table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 50% !important;">{{__('Month Name')}}</th>
                                                    <th class="text-center" style="width: 50% !important;" colspan="2">{{__('Contribution Amount')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            @php
                                             $p = 0;
                                            @endphp
                                                @foreach($all_fund as $row)
                                                    <tr>
                                                        <td class="text-center" >{{date('F-Y', strtotime($row->payment_month))}}</td>
                                                        <td class="text-right" style="padding-right: 10%;">{{$row->provident_fund}}/-TK
                                                            @php
                                                            $p += $row->provident_fund;
                                                            @endphp
                                                        </th>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                            <tr class="bg-primary">
                                                <td class="text-center"  style="color: white">Total Provident Fund </td>
                                                <td class="text-right" style="padding-right: 10%;color: white">{{$p}}/-TK <br> <span>With Subscription Amount : {{$employee->provident_fund_contribution + $p}}/TK</span></td>
                                            </tr>
                                            </tfoot>
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
            //QR Code Print
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
