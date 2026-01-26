@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
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
                        @if (permission('stock-return-add'))
                            <a href="#" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Add Return</a>
                        @endif
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
                            <x-form.textbox labelName="Return No." name="return_no" col="col-md-3"/>
                            <x-form.textbox labelName="Invoice No." name="invoice_no" col="col-md-3"/>
                            <div class="col-md-6">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                            data-toggle="tooltip" data-theme="dark" title="Reset">
                                        <i class="fas fa-undo-alt"></i></button>

                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
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
                                        @if (permission('stock-return-access'))
                                            <th>
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" class="custom-control-input" id="select_all" onchange="select_all()">
                                                    <label class="custom-control-label" for="select_all"></label>
                                                </div>
                                            </th>
                                        @endif
                                        <th>Sl</th>
                                        <th>Invoice No.</th>
                                        <th>Customer Name</th>
                                        <th>Return Date</th>
                                        <th>Grand Total</th>
                                        <th>Action</th>
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
    @include('sale::log-modal')
@endsection
@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
    <script src="js/moment.js"></script>
    <script src="js/knockout-3.4.2.js"></script>
    <script src="js/daterangepicker.min.js"></script>
    <script>
        let table;
        $(document).ready(function () {
            table = $('#dataTable').DataTable({
                "processing": true, //Feature control the processing indicator
                "serverSide": true, //Feature control DataTable server side processing mode
                "order": [], //Initial no order
                "responsive": true, //Make table responsive in mobile device
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
                    "url": "{{route('stock.return.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.return_no = $("#form-filter #return_no").val();
                        data.invoice_no = $("#form-filter #invoice_no").val();
                        data.start_date = $("#form-filter #start_date").val();
                        data.end_date = $("#form-filter #end_date").val();
                        data.customer_id = $("#form-filter #customer_id option:selected").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    @if(permission('stock-return-access'))
                    "targets": [0, 1, 2, 3, 4, 6],
                    @else
                    "targets": [0, 1, 2, 3, 5],
                    @endif
                    "orderable": false,
                    "className": "text-center"
                },
                    {
                        @if(permission('stock-return-access'))
                        "targets": [5],
                        @else
                        "targets": [4],
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
                        "pageSize": "legal", //A3,A5,A6,legal,letter
                        "exportOptions": {
                            @if(permission('stock-return-access'))
                            columns: ':visible:not(:eq(0),:eq(6))'
                            @else
                            columns: ':visible:not(:eq(5))'
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
                            @if(permission('stock-return-access'))
                            columns: ':visible:not(:eq(0),:eq(6))'
                            @else
                            columns: ':visible:not(:eq(5))'
                            @endif
                        },
                    },
                    {
                        "extend": 'excel',
                        'text': 'Excel',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "exportOptions": {
                            @if(permission('stock-return-access'))
                            columns: ':visible:not(:eq(0),:eq(6))'
                            @else
                            columns: ':visible:not(:eq(5))'
                            @endif
                        },
                    },
                    {
                        "extend": 'pdf',
                        'text': 'PDF',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "orientation": "landscape", //portrait
                        "pageSize": "legal", //A3,A5,A6,legal,letter
                        "exportOptions": {
                            @if(permission('stock-return-access'))
                            columns: ':visible:not(:eq(0),:eq(6))'
                            @else
                            columns: ':visible:not(:eq(5))'
                            @endif
                        },
                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 7;
                            doc.pageMargins = [5, 5, 5, 5];
                        }
                    },
                        @if (permission('stock-return-access'))
                    {
                        'className': 'btn btn-danger btn-sm delete_btn d-none text-white',
                        'text': 'Delete',
                        action: function (e, dt, node, config) {
                            multi_delete();
                        }
                    }
                    @endif
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
                let id    = $(this).data('id');
                let name  = $(this).data('name');
                let row   = table.row($(this).parent('tr'));
                let url   = "{{ route('stock.return.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.view_log', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('stock.return.log') }}",
                    type: "POST",
                    data: {id: id, _token: _token},
                    success: function (data) {
                        $('#log_modal .modal-title').html('Stock Return Log');
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

            // Approve Refund
            $(document).on('click', '.approve-payment', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('stock.return.approve.refund') }}",
                    type: "POST",
                    data: {id: id, _token: _token},
                    success: function (data) {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            table.ajax.reload();
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });
        });

        function multi_delete(){
            let ids = [];
            let rows;
            $('.select_data:checked').each(function(){
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if(ids.length == 0){
                Swal.fire({
                    type:'error',
                    title:'Error',
                    text:'Please checked at least one row of table!',
                    icon: 'warning',
                });
            }else{
                let url = "{{route('stock.return.bulk.delete')}}";
                bulk_delete(ids,url,table,rows);
            }
        }
    </script>
@endpush
