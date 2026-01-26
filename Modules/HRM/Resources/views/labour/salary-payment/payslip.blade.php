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
                        <a href="{{route('labourSalaryPayment')}}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i>{{__('file.Back')}}</a>
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
                                    <table>
                                        <tr>
                                            <td width="20%"></td>
                                            <td width="80%" class="text-right">
                                                <h2 class="name m-0" style="text-transform: uppercase;"><b>{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</b></h2>
                                                @if(config('settings.contact_no'))<p style="font-weight: normal;margin:0;"><b>{{__('file.Contact No')}}.: </b>{{ config('settings.contact_no') }}, @if(config('settings.email'))<b>{{__('file.Email')}}: </b>{{ config('settings.email') }}@endif</p>@endif
                                                <h5 style="font-weight: normal;margin:0;"><b>Corporate Office :</b> Ga 81/C Gulshan Badda Link Road,Opposite Side Off Primer Bank Ltd,Dhaka Bangladesh</h5>
                                                <p style="font-weight: normal;margin:0;"><b>{{__('file.Date')}}: </b>{{ date('d-M-Y') }}</p>
                                            </td>
                                        </tr>
                                    </table>
                                    <div style="width: 100%;height:3px;border-top:1px solid #036;border-bottom:1px solid #036;"></div>
                                    <table>
                                        <tr>
                                            <td width="20%">
                                                <div class="invoice-to">
                                                    <div class="text-grey-light text-right"><b>{{__('Labour Name')}} :</b></div>
                                                    <div class="text-grey-light text-right"><b>{{__('Labour ID')}} :</b></div>
                                                </div>
                                            </td>
                                            <td width="30%">
                                                <div class="invoice-to">
                                                    <div class="text-grey-light text-left">{{$payslip->labour->name}}</div>
                                                    <div class="text-grey-light text-left">{{$payslip->labour->labour_id}}</div>
                                                    <div class="text-grey-light text-left"></div>
                                                    <div class="text-grey-light text-left"></div>
                                                </div>
                                            </td>
                                            <td width="35%" class="text-right">
                                                <div class="m-0 date"><b>{{__('Payment Date')}}:</b></div>
{{--                                                <div class="m-0 date"><b>{{__('file.Status')}}:</b></div>--}}
                                            </td>
                                            <td width="15%" class="text-right">
                                                <div class="m-0 date text-left">{{date('Y-m-d', strtotime($payslip->created_at))}}</div>
{{--                                                <div class="m-0 date text-left"><b>{{__('file.Status')}}:</b></div>--}}
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="box" style="display: flex">
                                        <table border="0" cellspacing="0" cellpadding="0" style="width:50% !important;" class="table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center" colspan="4">{{__('file.Others Allowance')}}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $allowances = 0;
                                                @endphp
                                                @foreach($other_allowance as $row)
                                                <tr>
                                                    <td class="text-right">{{$row->allowances_name}} :</td>
                                                    <td class="text-right">{{$row->allowances_amount}}/-TK
                                                        @php
                                                            $allowances += $row->allowances_amount
                                                        @endphp
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                            </tfoot>
                                        </table>
                                        <table border="0" cellspacing="0" cellpadding="0" style="width:50% !important;" class="table-bordered">
                                            <thead>
                                            <tr>
                                                <th class="text-center"  colspan="4">{{__('file.Others Deduction')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($other_deduction as $row)
                                                <tr>
                                                    <td class="text-right" colspan="2">{{$row->deduction_name}} :</td>
                                                    <td class="text-right" colspan="2" style="padding-right: 4%;">{{$row->deduction_amount}}/-TK</td><br>
                                                </tr>
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>
                                    <table>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('BASIC SALARY')}}:</td>
                                                <td class="text-right">{{$payslip->basic_salary}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-right"></td>
                                                <td colspan="2"  class="text-right">{{__('TOTAL ALLOWANCE')}}:</td>
                                                <td class="text-right">
                                                    {{$payslip->others_allowance}}/-TK
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="4" class="text-right"></td>
                                                <td colspan="2"  class="text-right">{{__('GROSS SALARY')}}:</td>
                                                <td class="text-right">{{($payslip->total_gross_salary)}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('SALARY ADVANCE')}}:</td>
                                                <td class="text-right">- {{$payslip->salary_advance}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('TOTAL DEDUCTION')}}:</td>
                                                <td class="text-right">- {{($payslip->total_deduction)}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('BONUS')}}:</td>
                                                <td class="text-right">{{$payslip->bonus}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('NET PAYABLE')}}:</td>
                                                <td class="text-right">{{$payslip->net_payable}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('file.DUE AMOUNT')}}:</td>
                                                <td class="text-right">{{($payslip->net_payable + $payslip->bonus) - $payslip->payable_salary}}/-TK</td>
                                            </tr>
                                            <tr>
                                                <td colspan="4"></td>
                                                <td colspan="2"  class="text-right">{{__('PAYABLE')}}:</td>
                                                <td class="text-right">{{$payslip->payable_salary}}/-TK</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <table>
                                        <tr>
                                            <td>
                                                <div class="thanks"><h4>{{__('file.Thank you!')}}</h4></div>
                                                <div class="notices">
                                                    <div>{{__('file.Note')}}:</div>
                                                    <div class="notice">{{$payslip->note}}</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                    <table style="width: 100%;">
                                        <tr>
                                            <td class="text-center">
                                                <div class="font-size-10" style="width:250px;float:left;">
                                                    <p style="margin:0;padding:0;"><b class="text-uppercase"></b>
                                                        <br> </p>
                                                    <p class="dashed-border"></p>
                                                    <p style="margin:0;padding:0;">{{__('file.Received By')}}</p>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="font-size-10" style="width:250px;float:right;">
                                                    <p style="margin:0;padding:0;"><b class="text-uppercase">{{\Illuminate\Support\Facades\Auth::user()->name}}</b></p>
                                                    <p class="dashed-border"></p>
                                                    <p style="margin:0;padding:0;">{{__('file.Authorized By')}}</p>
                                                </div>
                                            </td>
                                        </tr>
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
