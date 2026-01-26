@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
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
                        @if (permission('holiday-add'))
                            <a href="javascript:void(0);" onclick="showFormModal('Add Events/Holidays','Save')"
                               class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Add New</a>
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
                            <x-form.textbox labelName="Title" name="title" col="col-md-4"/>
                            <div class="form-group col-md-4 required">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" name="from_date" id="from_date"
                                       value="" />
                            </div>
                            <div class="form-group col-md-4 required">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" id="to_date"
                                       value="" />
                            </div>
                            <x-form.selectbox labelName="Type" name="type" col="col-md-4" class="selectpicker">
                                @foreach (TYPE_STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-4">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset"
                                                class="btn btn-danger btn-sm btn-elevate btn-icon float-right"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="Reset">
                                            <i class="fas fa-undo-alt"></i></button>

                                        <button id="btn-filter"
                                                class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="Search">
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
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Title</th>
                                        <th>Type</th>
                                        <th>description</th>
                                        <th>Event Date</th>
                                        <th>Duration</th>
                                        <th>Created By</th>
                                        <th>Created At</th>
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
    @include('hrm::holiday.modal')
    @include('hrm::holiday.view-modal')
@endsection

@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
    <script>
        var table;
        $(document).ready(function () {

            table = $('#dataTable').DataTable({
                "processing": true, //Feature control the processing indicator
                "serverSide": true, //Feature control DataTable server side processing mode
                "order": [], //Initial no order
                "responsive": true, //Make table responsive in mobile device
                "bInfo": true, //TO show the total number of data
                "bFilter": false, //For datatable default search box show/hide
                "ordering": false, //For datatable default search box show/hide
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
                    "url": "{{route('holiday.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.title = $("#form-filter #title").val();
                        data.from_date = $("#form-filter #from_date").val();
                        data.to_date = $("#form-filter #to_date").val();
                        data.type = $("#form-filter #type").find(':selected').val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0,1,2,3,4,5,6,7,8],
                    "orderable": false,
                    "className": "text-center"
                },
                ]
            });

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
                let url = "{{route('holiday.store')}}";
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
                let url = "{{ route('holiday.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.view-data', function () {
                let id = $(this).data('id');
                let name  = $(this).data('name');
                if (id) {
                    $.ajax({
                        url: "{{route('holiday.view')}}",
                        type: "POST",
                        data: { id: id,_token: _token},
                        success: function (data) {
                            $('#view_modal #view-data').html('');
                            $('#view_modal #view-data').html(data);
                            $('#view_modal').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
                            $('#view_modal .modal-title').html(
                                '<i class="fas fa-eye text-white"></i> <span> ' + name + ' Details</span>');
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });
        });
    </script>
@endpush
