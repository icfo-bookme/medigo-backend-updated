@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css"/>
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.textbox labelName="Invoice No." name="invoice_no" col="col-md-3"/>
                            <x-form.selectbox labelName="Delivery Man" name="delivery_man_id" col="col-md-3" class="selectpicker">
                                @if (!$delivery_man->isEmpty())
                                    @foreach ($delivery_man as $value)
                                        <option value="{{ $value->id }}">{{ $value->name.($value->mobile ? ' - '.$value->mobile : '') }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>

                            <div class="col-md-0">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button"
                                            data-toggle="tooltip" data-theme="dark" title="Reset"><i class="fas fa-undo-alt"></i></button>

                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2" type="button"
                                            data-toggle="tooltip" data-theme="dark" title="Search"><i class="fas fa-search"></i></button>
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
                                        <th>Sale Status</th>
                                        <th>Delivery Man</th>
                                        <th>Customer Name</th>
                                        <th>Delivery Address</th>
                                        <th>Total Item Qty</th>
                                        <th>Grand Total</th>
                                        <th>Paid Amount</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>

                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('sale::order.modal')
    @include('sale::invoice-modal')
    {{--    @include('sale::modal')--}}
@endsection

@push('scripts')
    <script src="js/moment.js"></script>
    <script src="js/knockout-3.4.2.js"></script>
    <script src="js/daterangepicker.min.js"></script>
    <script src="{{asset('plugins/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
    <script>
        let table, inputTimeout;

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

        $(document).ready(function () {
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
                    "url": "{{ route('sale.delivery.datatable.data') }}",
                    "type": "POST",
                    "data": function (data) {
                        data.invoice_no = $("#form-filter #invoice_no").val();
                        data.delivery_man_id = $("#form-filter #delivery_man_id").val();
                        data.sort_table = $("#form-filter #sort_table").val();
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
            });

            function setupInputTableReload(selector, event, timeout) {
                $(document).on(event, selector, function () {
                    clearTimeout(inputTimeout);
                    inputTimeout = setTimeout(function () {
                        table.ajax.reload();
                    }, timeout);
                });
            }

            function setupTableReloadOnChange(selector) {
                $(document).on('change', selector, function () {
                    table.ajax.reload();
                });
            }

            setupInputTableReload('#form-filter #invoice_no', 'input', 300);
            setupTableReloadOnChange('#form-filter #delivery_man_id');
            setupTableReloadOnChange('#form-filter #sort_table');

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('input[name="start_date"]').val('');
                $('input[name="end_date"]').val('');
                $('#report_data').empty();
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
        });
    </script>
@endpush

