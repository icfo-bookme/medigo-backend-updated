@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
    <style>
        .apply-btn, .cancel-btn {
            display: block !important;
        }

        .calendar-header .arrow, .calendar-header .arrow button {
            display: block !important;
        }
    </style>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.textbox labelName="Invoice No." name="invoice_no" col="col-md-3"/>

                            <div class="form-group col-md-3">
                                <label for="name">Choose Your Date</label>
                                <div class="input-group">
                                    <input type="text" class="form-control daterangepicker-filed">
                                    <input type="hidden" id="start_date" name="start_date">
                                    <input type="hidden" id="end_date" name="end_date">
                                </div>
                            </div>

                            <div class="col-md-0">
                                <div style="margin-top:28px;">
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon" type="button" data-toggle="tooltip" data-theme="dark"
                                            title="file.Search"><i class="fas fa-search"></i></button>
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon ml-2" type="button" data-toggle="tooltip" data-theme="dark"
                                            title="file.Reset"><i class="fas fa-undo-alt"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Invoice No.</th>
                                        <th>Customer Name</th>
                                        <th>Total Item Qty</th>
                                        <th>Order Tax</th>
                                        <th>Order Discount</th>
                                        <th>Shipping Cost</th>
                                        <th>Net Total</th>
                                        <th>Grand Total</th>
                                        <th>Paid Amount</th>
                                        <th>Date</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                    <tr class="bg-primary">
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: right !important;font-weight:bold;">Total</th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th style="text-align: center !important;font-weight:bold;"></th>
                                        <th></th>
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
@endsection
@push('scripts')
    <script src="{{asset('js/jquery.printarea.js')}}"></script>
    <script src="js/moment.js"></script>
    <script src="js/knockout-3.4.2.js"></script>
    <script src="js/daterangepicker.min.js"></script>
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>

    <script>
        let table, inputTimeout, dateChangeTimeout;

        $(document).ready(function () {
            //QR Code Print
            $(document).on('click', '#print-invoice', function () {
                var mode = 'iframe'; // popup
                var close = mode == "popup";
                var options = {
                    mode: mode,
                    popClose: close
                };
                $("#invoice").printArea(options);
            });

            $('.daterangepicker-filed').daterangepicker({
                callback: function (startDate, endDate, period) {
                    var start_date = startDate.format('YYYY-MM-DD');
                    var end_date = endDate.format('YYYY-MM-DD');
                    var title = start_date + ' To ' + end_date;
                    $(this).val(title);
                    $('input[name="start_date"]').val(start_date).trigger('change');
                    $('input[name="end_date"]').val(end_date).trigger('change');
                }
            });

            table = $('#dataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [],
                "responsive": true,
                "bInfo": true,
                "bFilter": false,
                "lengthMenu": [
                    [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
                    [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
                ],
                "pageLength": 25,
                "language": {
                    processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                    emptyTable: '<strong class="text-danger">No Data Found</strong>',
                    infoEmpty: '',
                    zeroRecords: '<strong class="text-danger">No Data Found</strong>'
                },
                "ajax": {
                    "url": "{{route('sales.report.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.invoice_no = $("#form-filter #invoice_no").val();
                        data.start_date = $("#form-filter #start_date").val();
                        data.end_date = $("#form-filter #end_date").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    "className": "text-center"
                }],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
                "buttons": [
                    {
                        'extend': 'colvis', 'className': 'btn btn-secondary btn-sm text-white', 'text': 'Column', 'columns': ':gt(0)'
                    },
                    {
                        "extend": 'print',
                        'text': '{{'Print'}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title":
                            "{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}{{ $page_title }} List @if(config('settings.contact_no'))<p><b>Contact No.: </b>{{ config('settings.contact_no') }} @if(config('settings.email'))<b>, Email: </b>{{ config('settings.email') }}@endif</p>@endif  <p>PRODUCT SALES REPORT</p>",
                        "orientation": "landscape",
                        "pageSize": "A4",
                        "exportOptions": {
                            columns: function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        customize: function (win) {
                            $(win.document.body).addClass('bg-white');
                            $(win.document.body).find('table thead').css({'background': '#034d97'});
                            $(win.document.body).find('table tfoot tr').css({'background-color': '#034d97'});
                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('h1').css('font-size', '15px');
                            $(win.document.body).find('table').css('font-size', 'inherit');
                        },
                        footer: true
                    },
                    {
                        "extend": 'csv',
                        'text': '{{'CSV'}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": 'Customers Ledger From ' + $('#form-filter #start_date').val() + ' To ' + $('#form-filter #end_date').val(),
                        "exportOptions": {
                            columns: function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        footer: true
                    },
                    {
                        "extend": 'excel',
                        'text': '{{'Excel'}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": 'Customers Ledger From ' + $('#form-filter #start_date').val() + ' To ' + $('#form-filter #end_date').val(),
                        "exportOptions": {
                            columns: function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        footer: true
                    },
                    {
                        "extend": 'pdf',
                        'text': '{{'PDF'}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": 'Customers Ledger From ' + $('#form-filter #start_date').val() + ' To ' + $('#form-filter #end_date').val(),
                        "orientation": "portrait",
                        "pageSize": "A4",
                        "exportOptions": {
                            columns: function (index, data, node) {
                                return table.column(index).visible();
                            }
                        },
                        footer: true,
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 7;
                            doc.styles.tableFooter.fontSize = 7;
                            doc.pageMargins = [5, 5, 5, 5];
                        }
                    },
                ],
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    for (let index = 4; index <= 9; index++) {
                        total = api
                            .column(index)
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        pageTotal = api
                            .column(index, {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        $(api.column(index).footer()).html('= ' + number_format(pageTotal));
                    }
                }
            });

            function setupInputTableReload(selector, event, timeout) {
                $(document).on(event, selector, function () {
                    clearTimeout(inputTimeout);
                    inputTimeout = setTimeout(function () {
                        table.ajax.reload();
                    }, timeout);
                });
            }

            function setupTableReloadOnChange(selectors) {
                $(document).on('change', selectors, function () {
                    clearTimeout(dateChangeTimeout);
                    dateChangeTimeout = setTimeout(function() {
                        table.ajax.reload();
                    }, 300); // Adjust timeout as needed
                });
            }

            setupInputTableReload('#form-filter #invoice_no', 'input', 300);
            setupTableReloadOnChange('#start_date, #end_date');

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('input[name="start_date"]').val('');
                $('input[name="end_date"]').val('');
                $('#report_data').empty();
                table.ajax.reload();
            });
        });
    </script>
@endpush
