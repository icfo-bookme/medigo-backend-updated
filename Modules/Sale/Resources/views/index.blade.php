@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css"/>
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
                        <a href="{{ route('pos') }}" class="btn btn-primary btn-sm font-weight-bolder">
                            <i class="fas fa-plus-circle"></i> Add New</a>
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
                            <x-form.selectbox labelName="Customer" name="customer_id" col="col-md-3" class="selectpicker">
                                @if (!$customers->isEmpty())
                                    @foreach ($customers as $value)
                                        <option value="{{ $value->id }}">{{ $value->name.($value->mobile ? ' - '.$value->mobile : '') }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Order Source" name="order_source_id" col="col-md-3" class="selectpicker">
                                @foreach (ORDER_SOURCE as $key => $source)
                                    <option value="{{ $key }}">{{ $source  }}</option>
                                @endforeach
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Created By" name="created_by" col="col-md-3" class="selectpicker">
                                @if (!$users->isEmpty())
                                    @foreach ($users as $value)
                                        <option value="{{ $value->name }}">{{ $value->name.($value->phone ? ' - '.$value->phone : '') }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                                <option value="pending">Pending</option>
                            </x-form.selectbox>

                            <div class="col-md-2">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button"
                                            data-toggle="tooltip" data-theme="dark" title="Reset">
                                        <i class="fas fa-undo-alt"></i></button>

                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2" type="button"
                                            data-toggle="tooltip" data-theme="dark" title="Search">
                                        <i class="fas fa-search"></i></button>
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
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th width="5%">Sl</th>
                                        <th width="15%">Invoice</th>
                                        <th width="20%">Customer</th>
                                        <th width="20%">Total</th>
                                        <th width="15%">Status</th>
                                        <th width="10%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>
    @include('sale::order.modal')
    @include('sale::assign-deliveryman')
    @include('sale::invoice-modal')
    @include('sale::log-modal')
@endsection

@push('scripts')
    <script src="{{asset('js/jquery.printarea.js')}}"></script>
    <script src="js/moment.js"></script>
    <script src="js/knockout-3.4.2.js"></script>
    <script src="js/daterangepicker.min.js"></script>

    <script>

        var table;
        $(document).ready(function () {

            $('.daterangepicker-filed').daterangepicker({
                callback: function (startDate, endDate, period) {
                    var start_date = startDate.format('YYYY-MM-DD');
                    var end_date = endDate.format('YYYY-MM-DD');
                    var title = start_date + ' To ' + end_date;
                    $(this).val(title);
                    $('input[name="start_date"]').val(start_date);
                    $('input[name="end_date"]').val(end_date);
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
                    "url": "{{route('sale.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.invoice_no = $("#form-filter #invoice_no").val();
                        data.warehouse_id = $("#form-filter #warehouse_id").val();
                        data.start_date = $("#form-filter #start_date").val();
                        data.end_date = $("#form-filter #end_date").val();
                        data.customer_id = $("#form-filter #customer_id").val();
                        data.order_source_id = $("#form-filter #order_source_id").val();
                        data.created_by = $("#form-filter #created_by").val();
                        data.sort_table = $("#form-filter #sort_table").val();
                        data.order_type = 2;
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5],
                    "orderable": false,
                    "className": "text-center"
                },
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
                "buttons": [
                    {
                        "extend": 'print',
                        'text': '{{'Print'}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
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
                    }, {
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
                    }, {
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
            });

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('sale.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.view_invoice', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('sale.show.invoice') }}",
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
                    url: "{{ route('sale.log') }}",
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
                    let url = "{{route('sale.bulk.delete')}}";
                    bulk_delete(ids, url, table, rows);
                }
            }

            //Show Status Change Modal
            $(document).on('click', '.change_status', function () {
                $('#approve_status_form #status_id').val($(this).data('id'));
                $('#approve_status_form #delivery_status').val($(this).data('delivery_status'));
                $('#approve_status_form #visa_status.selectpicker').selectpicker('refresh');
                $('#approve_status_modal').modal({
                    keyboard: false,
                    backdrop: 'true',
                });
                $('#approve_status_modal .modal-title').html('<span>{{__('Change Order Status')}}</span>');
                $('#approve_status_modal #status-btn').text('{{__('Change Order Status')}}');

            });

            $(document).on('click', '#status-btn', function () {
                var status_id = $('#approve_status_form #status_id').val();
                var delivery_status = $('#approve_status_form #delivery_status').val();
                $.ajax({
                    url: "{{route('sale.order.status')}}",
                    type: "POST",
                    data: {status_id: status_id, delivery_status: delivery_status, _token: _token},
                    dataType: "JSON",
                    beforeSend: function () {
                        $('#status-btn').addClass('kt-spinner kt-spinner--md kt-spinner--light');
                    },
                    complete: function () {
                        $('#status-btn').removeClass('kt-spinner kt-spinner--md kt-spinner--light');
                    },
                    success: function (data) {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            $('#approve_status_modal').modal('hide');
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.assign_rider', function () {
                $('#store_or_update_form #update_id').val($(this).data('id'));
                $('#store_or_update_form #invoice_no').val($(this).data('invoice_no'));
                $('#store_or_update_modal').modal({
                    keyboard: false,
                    backdrop: 'true',
                });
                $('#store_or_update_modal .modal-title').html('<span>Assign Delivery Man</span>');
                $('#store_or_update_modal #save-btn').text('Assign Delivery Man');
            });

            $(document).on('click', '#store_or_update_modal #save-btn', function () {
                let id = $('#store_or_update_form #update_id').val();
                let invoice_no = $('#store_or_update_form #invoice_no').val();
                let delivery_man_id = $('#store_or_update_form #delivery_man_id').val();
                if (delivery_man_id) {
                    $.ajax({
                        url: "{{ route('sale.assignDeliveryMan') }}",
                        type: "POST",
                        data: {update_id: id, invoice_no: invoice_no, delivery_man_id: delivery_man_id, track_modal: 'Sale', _token: _token},
                        dataType: "JSON",
                        beforeSend: function () {
                            $('#save-btn').addClass('kt-spinner kt-spinner--md kt-spinner--light');
                        },
                        complete: function () {
                            $('#save-btn').removeClass('kt-spinner kt-spinner--md kt-spinner--light');
                        },
                        success: function (data) {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                $('#store_or_update_modal').modal('hide');
                                table.ajax.reload(null, false);
                            }
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                } else {
                    notification('error', 'Please select a delivery man');
                }
            });
        });
    </script>
@endpush
