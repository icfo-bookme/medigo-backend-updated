@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
@endpush

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <!--begin::Notice-->
            <!--end::Notice-->
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.selectbox labelName="Coupon" name="coupon_id" required="required" col="col-md-4"
                                              class="selectpicker">
                                @foreach ($coupons as $c_key => $value)
                                    <option value="{{ $c_key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Coupon Type" name="coupon_type" required="required" col="col-md-3"
                                              class="selectpicker">
                                <option value="1">General Coupon</option>
                                <option value="2">Category Coupon</option>
                                <option value="3">Customer Coupon</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>
                            <div class="col-md-0">
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
                                    <thead class="bg-primary text-center">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Coupon</th>
                                        <th>Invoice</th>
                                        <th>No of Use</th>
                                        <th>Value</th>
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
    @include('sale::invoice-modal')
@endsection
@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
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
                    "url": "{{route('coupon.report.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.coupon_id = $('#form-filter #coupon_id').val();
                        data.coupon_type = $('#form-filter #coupon_type').val();
                        data.sort_table = $("#form-filter #sort_table").val();

                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4],
                    "orderable": false,
                    "className": "text-center"
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
                        'text': '{{__('Print')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "orientation": "landscape", //portrait
                        "pageSize": "legal", //A3,A5,A6,legal,letter
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
                        'text': '{{__('CSV')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    },
                    {
                        "extend": 'excel',
                        'text': '{{__('Excel')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    },
                    {
                        "extend": 'pdf',
                        'text': '{{__('PDF')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "orientation": "landscape", //portrait
                        "pageSize": "legal", //A3,A5,A6,legal,letter

                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 7;
                            doc.pageMargins = [5, 5, 5, 5];
                        }
                    },
                ],
            });

            function setupTableReloadOnChange(selector) {
                $(document).on('change', selector, function () {
                    table.ajax.reload();
                });
            }

            setupTableReloadOnChange('#form-filter #coupon_id');
            setupTableReloadOnChange('#form-filter #coupon_type');
            setupTableReloadOnChange('#form-filter #sort_table');

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
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
