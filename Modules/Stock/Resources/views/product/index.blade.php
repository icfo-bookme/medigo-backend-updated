@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/daterangepicker.min.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        table#dataTable {
            overflow-x: auto;
            overflow-y: auto;
            max-height: 400px;
        }
    </style>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row ">
                            <x-form.selectbox labelName="Products" name="product_id" col="col-md-3" class="selectpicker">
                                <option value="" selected>Select Please</option>
                                @foreach ($products as $p_key => $product)
                                    <option value="{{ $p_key }}">{{ $product }}</option>
                                @endforeach
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Company" name="company_id" col="col-md-3" class="selectpicker">
                                <option value="" selected>Select Please</option>
                                @foreach ($companies as $c_key => $company)
                                    <option value="{{ $c_key }}">{{ $company }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>
                            <div class="col-md-0">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button" data-toggle="tooltip" data-theme="dark" title="Reset"><i
                                            class="fas fa-undo-alt"></i></button>
                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2" type="button" data-toggle="tooltip" data-theme="dark"
                                            title="Search"><i class="fas fa-search"></i></button>
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
                                        <th>SL</th>
                                        <th>Product Name</th>
                                        <th>Product Code</th>
                                        <th>Company name</th>
                                        <th>Generic</th>
                                        <th>Net Unit Cost</th>
                                        <th>Stock Qty</th>
                                        <th>Stock Value</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                    <tr class="bg-primary">
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th style="text-align: right !important;font-weight:bold;color:white;">{{'Total'}}</th>
                                        <th style="text-align: center !important;font-weight:bold;color:white;"></th>
                                        <th style="text-align: center !important;font-weight:bold;color:white;"></th>
                                        <th style="text-align: center !important;font-weight:bold;color:white;"></th>
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
    <script src="{{asset('plugins/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
    <script src="{{asset('js/moment.js')}}"></script>
    <script src="{{asset('js/knockout-3.4.2.js')}}"></script>
    <script src="{{asset('js/daterangepicker.min.js')}}"></script>
    <script>
        let table;
        $(document).ready(function () {
            table = $('#dataTable').DataTable({
                "processing": true,
                "serverSide": true,
                "order": [],
                "responsive": true,
                "bInfo": true,
                "bFilter": false,
                "scrollY": "400px",
                "lengthMenu": [
                    [5, 10, 15, 25, 50, 100, 1000],
                    [5, 10, 15, 25, 50, 100, 1000]
                ],
                "pageLength": 25,
                "language": {
                    processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                    emptyTable: '<strong class="text-danger">{{'No Data Found'}}</strong>',
                    infoEmpty: '',
                    zeroRecords: '<strong class="text-danger">{{'No Data Found'}}</strong>'
                },
                "ajax": {
                    "url": "{{route('stock.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.product_id = $("#form-filter #product_id option:selected").val();
                        data.company_id = $("#form-filter #company_id").val();
                        data.sort_table = $("#form-filter #sort_table").val();

                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "orderable": false,
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7],
                    "className": "text-center"
                }],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

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

                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(), data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over all pages
                    for (let index = 5; index <= 7; index++) {
                        // Total over this page
                        total = api
                            .column(index)
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Total over this page
                        pageTotal = api
                            .column(index, {page: 'current'})
                            .data()
                            .reduce(function (a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);

                        // Update footer
                        $(api.column(index).footer()).html('= ' + number_format(pageTotal));
                        // $(api.column( index ).footer()).html('= '+total+' Tk');
                    }
                }
            });

            function setupTableReloadOnChange(selector) {
                $(document).on('change', selector, function () {
                    table.ajax.reload();
                });
            }

            setupTableReloadOnChange('#form-filter #product_id');
            setupTableReloadOnChange('#form-filter #company_id');
            setupTableReloadOnChange('#form-filter #sort_table');


            $('#btn-filter').click(function () {
                table.ajax.reload();
            });
            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });
        });
    </script>
@endpush
