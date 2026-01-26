@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css"/>
    {{--<style>--}}
    {{--    table#dataTable{min-width:1700px !important;}--}}
    {{--</style>--}}
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        @if (permission('product-import-access'))
                            <a href="javascript:void(0);" onclick="showExcelModal('Upload Excel Sheet','Save')" class="btn btn-success btn-sm font-weight-bolder"><i
                                    class="fas fa-plus-circle"></i> Upload Excel Sheet</a>
                            <a class="btn btn-info btn-sm font-weight-bolder" href="{{asset('images/products_sample.csv')}}" title="" download><i class="fas fa-download"></i>
                                Download CSV Sample</a>
                        @endif
                        @if (permission('product-add'))
                            <a href="{{ route('product.add') }}" class="btn btn-primary btn-sm font-weight-bolder mr-2"><i class="fas fa-plus-circle"></i> Add New</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.textbox labelName="Product Name" name="name" col="col-md-3"/>
                            <x-form.textbox labelName="Generic Name" name="generic_name" col="col-md-3"/>
                            <x-form.selectbox labelName="Company Name" name="brand_id" col="col-md-3" class="selectpicker">
                                @if (!$brands->isEmpty())
                                    @foreach ($brands as $b_key => $brand)
                                        <option value="{{ $b_key }}">{{ $brand }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Category" name="category_id" col="col-md-3" class="selectpicker">
                                @if (!$categories->isEmpty())
                                    @foreach ($categories as $c_key =>  $category)
                                        <option value="{{ $c_key }}">{{ $category }}</option>
                                    @endforeach
                                @endif
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Status" name="status" col="col-md-3" class="selectpicker">
                                @foreach (STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                                <option value="image_null">No Image</option>
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
                            <div class="col-sm-12 table-responsive" style="min-height: 300px;">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all"
                                                       onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                        <th>Sl</th>
                                        <th>Image</th>
                                        <th>Product Type</th>
                                        <th>Name</th>
                                        <th>Generic Name</th>
                                        <th>Company Name</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Action</th>
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
    @include('product::modal-view-base')
    @include('sale::log-modal')
    @include('product::update_category_modal')
@endsection
@push('scripts')
    <script>
        let table, inputTimeout;

        $(document).ready(function () {
            table = $('#dataTable').DataTable({
                "processing": true, //Feature control the processing indicator
                "serverSide": true, //Feature control DataTable server side processing mode
                "order": [], //Initial no order
                "responsive": false, //Make table responsive in mobile device
                "bInfo": true, //TO show the total number of data
                "bFilter": false, //For datatable default search box show/hide
                "lengthMenu": [
                    [5, 10, 15, 25, 50, 100, 1000],
                    [5, 10, 15, 25, 50, 100, 1000]
                ],
                "pageLength": 25, //number of data show per page
                "language": {
                    processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                    emptyTable: '<strong class="text-danger">No Data Found</strong>',
                    infoEmpty: '',
                    zeroRecords: '<strong class="text-danger">No Data Found</strong>'
                },
                "ajax": {
                    "url": "{{route('product.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.name = $("#form-filter #name").val();
                        data.generic_name = $("#form-filter #generic_name").val();
                        data.brand_id = $("#form-filter #brand_id").val();
                        data.category_id = $("#form-filter #category_id").val();
                        data.status = $("#form-filter #status").val();
                        data.sort_table = $("#form-filter #sort_table").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
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
                    {
                        'className': 'btn btn-info btn-sm d-none text-white status_btn',
                        'text': '<i class="fa fa-plus-circle"></i> Active',
                        action: function () {
                            bulkStatusSubmit(1);
                        }
                    },
                    {
                        'className': 'btn btn-danger btn-sm d-none text-white status_btn',
                        'text': '<i class="fa fa-plus-circle"></i> Inactive',
                        action: function () {
                            bulkStatusSubmit(2);
                        }
                    },
                    {
                        'className': 'btn btn-info btn-sm d-none text-white update_btn',
                        'text': '<i class="fa fa-plus-circle"></i> Update Category',
                        action: function () {
                            bulkCategoryChange();
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

            setupInputTableReload('#form-filter #name', 'input', 500);
            setupInputTableReload('#form-filter #generic_name', 'input', 500);
            setupTableReloadOnChange('#form-filter #brand_id');
            setupTableReloadOnChange('#form-filter #category_id');
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

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('product.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('product.change.statuss') }}";
                change_status(id, url, table, row, name, status);
            });

            $(document).on('click', '.show-image', function () {
                let id = $(this).data('id');
                let imagePath = $(this).data('image_path');

                if (id) {
                    $.ajax({
                        url: "{{ route('product.image.show') }}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        success: function (data) {
                            $('#exampleModalLong #view-data').html('');
                            $('#exampleModalLong #view-data').html(data);
                            $('#exampleModalLong').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('input', '#name', function () {
                clearTimeout(inputTimeout);
                inputTimeout = setTimeout(function () {
                    table.ajax.reload();
                }, 500);
            });

            $(document).on('input', '#generic_name', function () {
                clearTimeout(inputTimeout);
                inputTimeout = setTimeout(function () {
                    table.ajax.reload();
                }, 500);
            });

            $(document).on('click', '.view_log', function () {
                let id = $(this).data('id');
                $.ajax({
                    url: "{{ route('product.log') }}",
                    type: "POST",
                    data: {id: id, _token: _token},
                    success: function (data) {
                        $('#log_modal .modal-title').html('Product Log');
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

            $(document).on('click', '#add-btn', function () {
                let form = document.getElementById('update_category_form');
                let formData = new FormData(form);
                let url = "{{ route('product.update.bulk.category') }}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        $('#add-btn').prop('disabled', true);
                        $('#add-btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...');
                    },
                    complete: function () {
                        $('#add-btn').prop('disabled', false);
                        $('#add-btn').html('Update');
                    },
                    success: function (data) {
                        if (data.status === 'error') {
                            notification(data.status, data.message);
                        } else {
                            $('#update_category_modal').modal('hide');
                            notification(data.status, data.message);
                            $('.status_btn').addClass('d-none');
                            $('.update_btn').addClass('d-none');
                            table.ajax.reload();
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });
        });

        function bulkStatusSubmit(status_value) {
            let ids = [];
            let rows;
            $('.select_data:checked').each(function () {
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if (ids.length === 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                $.ajax({
                    url: "{{ route('product.bulk.status.change') }}",
                    type: "POST",
                    data: {status: status_value, product_ids: ids, _token: _token},
                    success: function (data) {
                        showNotification();
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        }

        function bulkCategoryChange() {
            let ids = [];
            let rows;
            $('.select_data:checked').each(function () {
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if (ids.length === 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                $('#update_category_form #selected_ids').val(ids);
                $('#update_category_modal').modal({
                    keyboard: false,
                    backdrop: 'true',
                });
                $('#update_category_modal .modal-title').html('<span>Update Categories</span>');
            }
        }

        function showNotification() {
            notification('success', 'Bulk Status Changed');
            $('#bulkPrintRow').addClass('d-none');
            table.ajax.reload();
        }
    </script>
@endpush
