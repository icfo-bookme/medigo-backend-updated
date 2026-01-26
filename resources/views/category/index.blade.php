@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css"/>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        @if (permission('product-category-add') )
                            <a href="{{route('category.category.create')}}" class="btn btn-primary btn-sm font-weight-bolder"><i class="fas fa-plus-circle"></i> Add New</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.textbox labelName="Name" name="name" col="col-md-4"/>
                            <x-form.selectbox labelName="Status" name="status" col="col-md-4" class="selectpicker">
                                @foreach (STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>
                            <div class="col-md-0">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button" data-toggle="tooltip" data-theme="dark"
                                            title="Reset"><i class="fas fa-undo-alt"></i></button>
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
                                        <th width="0.2%">Sl</th>
                                        <th width="0.7%">Image</th>
                                        <th width="2%">Name</th>
                                        <th width="0.6%">Parent</th>
                                        <th width="1.5%">Serial</th>
                                        <th width="2%">Info</th>
                                        <th width="0.5%">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('category.modal')
@endsection
@push('scripts')
    <script src="{{asset('plugins/custom/datatables/datatables.bundle.js')}}" type="text/javascript"></script>
    <script>
        let table, inputTimeout;
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
                    emptyTable: '<strong class="text-danger text-=center">No Data Found</strong>',
                    infoEmpty: '',
                    zeroRecords: '<strong class="text-danger">No Data Found</strong>'
                },
                "ajax": {
                    "url": "{{route('category.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.name = $("#form-filter #name").val();
                        data.status = $("#form-filter #status").val();
                        data.sort_table = $("#form-filter #sort_table").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [
                    {
                        "targets": @if (permission('product-category-bulk-delete')) [0, 1, 2, 3, 4, 5, 6] @else [0, 1, 2, 3, 4, 5] @endif,
                        "orderable": false,
                        "className": "text-center"
                    }
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
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
                        'columns': ':gt(0)',
                        "exportOptions": {
                            columns: @if (permission('product-category-bulk-delete')) [1, 2, 3, 4, 5] @else [0, 1, 2, 3, 4] @endif
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
                            columns: @if (permission('product-category-bulk-delete')) [1, 2, 3, 4, 5] @else [0, 1, 2, 3, 4] @endif
                        }
                    },
                    {
                        "extend": 'excel',
                        'text': 'Excel',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "exportOptions": {
                            columns: @if (permission('product-category-bulk-delete')) [1, 2, 3, 4, 5] @else [0, 1, 2, 3, 4] @endif
                        }
                    },
                    {
                        "extend": 'pdf',
                        'text': 'PDF',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "orientation": "portrait", //portrait
                        "pageSize": "A4", //A3,A5,A6,legal,letter
                        "exportOptions": {
                            columns: @if (permission('product-category-bulk-delete')) [1, 2, 3, 4, 5] @else [0, 1, 2, 3, 4] @endif
                        }
                    },
                        @if (permission('product-category-bulk-delete'))
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

            setupInputTableReload('#form-filter #name', 'input', 500);
            setupTableReloadOnChange('#form-filter #status');
            setupTableReloadOnChange('#form-filter #sort_table');

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });

            $(document).on('click', '#save-btn', function () {
                let form = document.getElementById('store_or_update_form');
                let formData = new FormData(form);
                let url = "{{route('category.store.or.update')}}";
                let id = $('#update_id').val();
                let method;
                if (id) {
                    method = 'update';
                } else {
                    method = 'add';
                }
                store_or_update_data(table, method, url, formData);
            });

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('category.delete') }}";
                delete_data(id, url, table, row, name);
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
                    let url = "{{route('category.bulk.delete')}}";
                    bulk_delete(ids, url, table, rows);
                }
            }

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('category.change.status') }}";
                change_status(id, url, table, row, name, status);
            });
        });

        function updateCategorySerial(serial, categoryId) {
            let url = "{{ route('category.update.serial') }}";
            $.ajax({
                url: url,
                type: "POST",
                data: {
                    _token: _token,
                    category_id: categoryId,
                    nav_serial: serial
                },
                success: function (response) {
                    if (response.status === 'success') {
                        notification(response.status, response.message);
                        table.ajax.reload();
                    } else {
                        notification(response.status, response.message);
                        table.ajax.reload();
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', error);
                    notification('error', 'An error occurred while updating serial.');
                }
            });
        }
    </script>
@endpush
