@extends('layouts.app')

@section('title', $page_title)
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>

    <link href="https://raw.githack.com/ttskch/select2-bootstrap4-theme/master/dist/select2-bootstrap4.css"
          rel="stylesheet">
    <style>
        select {
            width: 100%;
            min-height: 100px;
            border-radius: 3px;
            border: 1px solid #444;
            padding: 10px;
            color: #444444;
            font-size: 14px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
            color: #495057;
            height: 25px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background: #3699FF;
            padding: 3px 3px !important;
            color: white !important;
        }

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
@endpush
@section('content')

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <!--begin::Notice-->
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                </div>
            </div>
            <!--end::Notice-->
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-3 required">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" name="from_date" id="from_date"
                                       value="{{ date("Y-m-d") }}"/>
                            </div>
                            <div class="form-group col-md-3 required">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" id="to_date"
                                       value="{{ date("Y-m-d") }}"/>
                            </div>
                            <div class="col-md-3" id="">
                                <label class="col-from-label">{{('Employee')}} <span
                                        class="text-danger">*</span></label>
                                <select class="form-control js-example-basic-multiple" name="employee_id[]"
                                        id="employee_id" data-live-search="true" required multiple>
                                    <option value="0">All</option>
                                    @foreach ($employees as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset"
                                                class="btn btn-danger btn-sm btn-elevate btn-icon float-left mr-1"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">
                                            <i class="fas fa-undo-alt"></i></button>
                                        <button id="btn-filter"
                                                class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-left btn-search"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}">
                                            <i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <form id="salary_store_or_update">
                                    @csrf
                                    <div id="report" style="width: 100%;margin:0;padding:0;">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>
@endsection
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $('.js-example-basic-multiple').each(function () {
            $(this).select2({
                placeholder: "    Select Employee",
                theme: 'bootstrap4',
                width: 'style',
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });

        $(document).on('click', '.btn-search', function () {
            report();
        });

        function report() {
            let selectedOptions = $('#employee_id').val();
            let from_date = $('#from_date').val();
            let to_date = $('#to_date').val();
            if (selectedOptions.length > 0) {
                $.ajax({
                    url: "{{ route('attendance.summery.data') }}",
                    type: "POST",
                    data: {id: selectedOptions, from_date: from_date, to_date: to_date, _token: _token},
                    success: function (data) {
                        $('#report').empty();
                        $('#report').append(data);
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            } else {
                $('#report').empty();
                notification('error', 'Please select Employee');
            }
        }

    </script>

@endpush
