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
            <!--begin::Notice-->
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <!--begin::Button-->
                        {{--                    @if (permission('purchase-add'))--}}
                        <a href="{{ route('purchase.add') }}" class="btn btn-primary btn-sm font-weight-bolder">
                            <i class="fas fa-plus-circle"></i> Add New</a>
                        {{--                        @endif--}}
                        <!--end::Button-->
                    </div>
                </div>
            </div>
            <!--end::Notice-->
            <!--begin::Card-->
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
                            <x-form.selectbox labelName="Supplier" name="supplier_id" col="col-md-3" class="selectpicker">
                                @if (!$suppliers->isEmpty())
                                    @foreach ($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{  $supplier->name.' ('.$supplier->company_name.')'  }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Purchase Status" name="purchase_status" col="col-md-3" class="selectpicker">
                                @foreach (PURCHASE_STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>
                            <div class="col-md-0">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button" data-toggle="tooltip" data-theme="dark" title="Reset"><i class="fas fa-undo-alt"></i></button>
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2" type="button" data-toggle="tooltip" data-theme="dark" title="Search"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <!--begin: Datatable-->

                    <div class="row">
                        <div class="col-sm-12 table-responsive">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                <tr>
                                    @if (permission('purchase-bulk-delete'))
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                    @endif
                                    <th>Sl</th>
                                    <th>Invoice No.</th>
                                    <th>Supplier Name</th>
                                    <th>Total Item</th>
                                    <th>Grand Total</th>
                                    <th>Paid Amount</th>
                                    <th>Due Amount</th>
                                    <th>Purchase Date</th>
                                    <th>Purchase Status</th>
                                    <th>Payment Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                <tr class="bg-primary">
                                    <th colspan="4" class="text-right">Total</th>
                                    <th style="text-align: right !important;font-weight:bold;color:white;"></th>
                                    <th style="text-align: right !important;font-weight:bold;color:white;"></th>
                                    <th style="text-align: right !important;font-weight:bold;color:white;"></th>
                                    <th style="text-align: right !important;font-weight:bold;color:white;"></th>
                                    <th colspan="4"></th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>
    @include('purchase::payment.add')
    @include('purchase::invoice-modal')

    <!-- Start :: Payment List Modal -->
    <div class="modal fade" id="payment_view_modal" tabindex="-1" role="dialog" aria-labelledby="model-1"
         aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <!-- Modal Content -->
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header bg-primary">
                    <h3 class="modal-title text-white" id="model-1"></h3>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <i aria-hidden="true" class="ki ki-close text-white"></i>
                    </button>
                </div>
                <!-- /modal header -->
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered" id="payment-list">
                                <thead class="bg-primary">
                                <th class="text-center">Date</th>
                                <th class="text-right">Paid Amount</th>
                                <th class="text-center">Payment Method</th>
                                <th>Account</th>
                                <th>Reference No</th>
                                <th>Note</th>
                                <th class="text-center">Action</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- /modal body -->

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                </div>
                <!-- /modal footer -->
            </div>
            <!-- /modal content -->
        </div>
    </div>

    @include('purchase::status-modal')
    @include('purchase::log-modal')
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
                "processing": true, //Feature control the processing indicator
                "serverSide": true, //Feature control DataTable server side processing mode
                "order": [], //Initial no order
                "responsive": false, //Make table responsive in mobile device
                "bInfo": true, //TO show the total number of data
                "bFilter": false, //For datatable default search box show/hide
                "lengthMenu": [
                    [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
                    [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
                ],
                "pageLength": 25, //number of data show per page
                "language": {
                    processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                    emptyTable: '<strong class="text-danger">No Data Found</strong>',
                    infoEmpty: '',
                    zeroRecords: '<strong class="text-danger">No Data Found</strong>'
                },
                "ajax": {
                    "url": "{{route('purchase.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.invoice_no = $("#form-filter #invoice_no").val();
                        data.supplier_id = $("#form-filter #supplier_id").val();
                        data.start_date = $("#form-filter #start_date").val();
                        data.end_date = $("#form-filter #end_date").val();
                        data.purchase_status = $("#form-filter #purchase_status").val();
                        data.payment_status = $("#form-filter #payment_status").val();
                        data.sort_table = $("#form-filter #sort_table").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    @if (permission('purchase-bulk-delete'))
                    "targets": [0, 1, 2, 4, 8, 9, 10, 11],
                    @else
                    "targets": [0, 1, 3, 7, 8, 9, 10],
                    @endif
                    "orderable": false,
                    "className": "text-center"
                },
                    {
                        @if (permission('purchase-bulk-delete'))
                        "targets": [5, 6, 7],
                        @else
                        "targets": [4, 5, 6],
                        @endif
                        "orderable": false,
                        "className": "text-right"
                    },

                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
                "buttons": [
                    {
                        'extend': 'colvis', 'className': 'btn btn-secondary btn-sm text-white', 'text': 'Column', 'columns': ':gt(0)'
                    },
                    {
                        "extend": 'print',
                        'text': 'Print',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "orientation": "landscape", //portrait
                        "pageSize": "A4", //A3,A5,A6,legal,letter
                        "exportOptions": {
                            @if (permission('purchase-bulk-delete'))
                            columns: ':visible:not(:eq(0),:eq(11))'
                            @else
                            columns: ':visible:not(:eq(10))'
                            @endif
                        },
                        customize: function (win) {
                            $(win.document.body).addClass('bg-white');
                            $(win.document.body).find('table thead').css({'background': '#034d97'});
                            $(win.document.body).find('table tfoot tr').css({'background-color': '#034d97'});
                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('h1').css('font-size', '15px');
                            $(win.document.body).find('table').css('font-size', 'inherit');
                        },
                    },
                    {
                        "extend": 'csv',
                        'text': 'CSV',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "exportOptions": {
                            @if (permission('purchase-bulk-delete'))
                            columns: ':visible:not(:eq(0),:eq(11))'
                            @else
                            columns: ':visible:not(:eq(10))'
                            @endif
                        }
                    },
                    {
                        "extend": 'excel',
                        'text': 'Excel',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "exportOptions": {
                            @if (permission('purchase-bulk-delete'))
                            columns: ':visible:not(:eq(0),:eq(11))'
                            @else
                            columns: ':visible:not(:eq(10))'
                            @endif
                        }
                    },
                    {
                        "extend": 'pdf',
                        'text': 'PDF',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "orientation": "landscape", //portrait
                        "pageSize": "A4", //A3,A5,A6,legal,letter
                        "exportOptions": {
                            @if (permission('purchase-bulk-delete'))
                            columns: ':visible:not(:eq(0),:eq(11))'
                            @else
                            columns: ':visible:not(:eq(10))'
                            @endif
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 7;
                            doc.pageMargins = [5, 5, 5, 5];
                        }
                    },
                        @if (permission('purchase-bulk-delete'))
                    {
                        'className': 'btn btn-danger btn-sm delete_btn d-none text-white',
                        'text': 'Delete',
                        action: function (e, dt, node, config) {
                            multi_delete();
                        }
                    }
                    @endif
                ],
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
                    for (let index = 4; index <= 7; index++) {
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
                        $(api.column(index).footer()).html(number_format(pageTotal));
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

            setupInputTableReload('#invoice_no', 'input', 300);

            setupTableReloadOnChange('#supplier_id');
            setupTableReloadOnChange('#start_date, #end_date');
            setupTableReloadOnChange('#purchase_status');
            setupTableReloadOnChange('#sort_table');

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter #start_date').val('');
                $('#form-filter #end_date').val('');
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });

            $(document).on('input', '#invoice_no', function () {
                clearTimeout(inputTimeout);
                inputTimeout = setTimeout(function () {
                    table.ajax.reload();
                }, 300);
            });

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('purchase.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.change_status', function () {
                $('#purchase_status_form #purchase_change_status_id').val($(this).data('id'));
                $('#purchase_status_modal').modal({
                    keyboard: false,
                    backdrop: 'true',
                });
                $('#purchase_status_modal .modal-title').html('<span>Change Purchase Status</span>');
                $('#purchase_status_modal #change-status-btn').text('Change Status');
            });

            $(document).on('click', '#change-status-btn', function () {
                let id = $('#purchase_status_form #purchase_change_status_id').val();
                let status = $('#purchase_status_form #purchase_change_status').val();
                if (status != '') {
                    $.ajax({
                        url: "{{ route('purchase.change.status') }}",
                        type: "GET",
                        data: {id: id, status: status, _token: _token},
                        dataType: "JSON",
                        beforeSend: function () {
                            $('#change-status-btn').addClass('kt-spinner kt-spinner--md kt-spinner--light');
                        },
                        complete: function () {
                            $('#change-status-btn').removeClass('kt-spinner kt-spinner--md kt-spinner--light');
                        },
                        success: function (data) {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                window.location.replace("{{ route('purchase') }}");
                            }
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '.view_invoice', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('purchase.show.invoice') }}",
                    type: "POST",
                    data: {id: id, _token: _token},
                    success: function (data) {
                        $('#invoice_view_modal #invoice_data').empty().html(data);
                        $('#invoice_view_modal').modal({
                            keyboard: false,
                            backdrop: 'true',
                        });
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.view_log', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('purchase.log') }}",
                    type: "POST",
                    data: {id: id, _token: _token},
                    success: function (data) {
                        $('#log_modal #view-data').empty().html(data);
                        $('#log_modal').modal({
                            keyboard: false,
                            backdrop: 'true',
                        });
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });

            function multi_delete() {
                let ids = [];
                let rows;
                $('.select_data:checked').each(function () {
                    ids.push($(this).val());
                    rows = table.rows($('.select_data:checked').parents('tr'));
                });
                if (ids.length == 0) {
                    Swal.fire({
                        type: 'error',
                        title: 'Error',
                        text: 'Please checked at least one row of table!',
                        icon: 'warning',
                    });
                } else {
                    let url = "{{route('purchase.bulk.delete')}}";
                    bulk_delete(ids, url, table, rows);
                }
            }
        });

        function account_list(payment_method, account_id = '') {
            $.ajax({
                url: "{{route('account.list')}}",
                type: "POST",
                data: {payment_method: payment_method, _token: _token},
                success: function (data) {
                    $('#payment_form #account_id').html('');
                    $('#payment_form #account_id').html(data);
                    $('#payment_form #account_id.selectpicker').selectpicker('refresh');
                    if (account_id) {
                        $('#payment_form #account_id').val(account_id);
                        $('#payment_form #account_id.selectpicker').selectpicker('refresh');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    </script>
@endpush
