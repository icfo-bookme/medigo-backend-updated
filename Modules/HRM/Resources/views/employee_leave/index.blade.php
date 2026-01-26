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
                        @if (permission('leave-add'))
                            <a href="javascript:void(0);"
                               onclick="showFormModal('{{__('file.Employee Leave Application')}}','{{__('file.Save')}}')"
                               class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> {{__('file.Add New')}}
                            </a>
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
                            <div class="form-group col-md-4 required">
                                <label for="from_date">From Date</label>
                                <input type="date" class="form-control" name="from_date" id="from_date"
                                       value=""/>
                            </div>
                            <div class="form-group col-md-4 required">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" id="to_date"
                                       value=""/>
                            </div>
                            <x-form.selectbox labelName="Status" name="type" col="col-md-4" class="selectpicker">
                                @foreach (LEAVE_STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="{{__('Employee')}}" name="employee_id" id="employee_id"
                                              col="col-md-4" class="selectpicker">
                                @foreach ($employees as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}--{{ $row->employee_id }}</option>
                                @endforeach
                            </x-form.selectbox>

                            <x-form.selectbox labelName="Leave Category" name="leave_category_id" col="col-md-4"
                                              class="selectpicker">
                                @foreach ($leaveCategories as $value)
                                    <option value="{{ $value->id }}">{{ $value->leave_category }}</option>
                                @endforeach
                            </x-form.selectbox>

                            <div class="col-md-4">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset"
                                                class="btn btn-danger btn-sm btn-elevate btn-icon float-right"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">
                                            <i class="fas fa-undo-alt"></i></button>

                                        <button id="btn-filter"
                                                class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}">
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
                                        <th>{{__('file.SL')}}</th>
                                        <th>{{__('file.Employee Name')}}</th>
                                        <th>{{__('file.Leave Category')}}</th>
                                        <th>{{__('file.Leave Period')}}</th>
                                        <th>{{__('file.Duration (Days)')}}</th>
                                        <th>{{__('file.Leave Type')}}</th>
                                        <th>{{__('file.Created By')}}</th>
                                        <th>{{__('file.Updated By')}}</th>
                                        <th>{{__('file.Approved By')}}</th>
                                        <th>{{__('file.Status')}}</th>
                                        <th>{{__('file.Action')}}</th>
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
    @include('hrm::employee_leave.modal')
    @include('hrm::employee_leave.view-modal')
@endsection

@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js"></script>
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
                    "url": "{{route('leave.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.employee_id = $("#form-filter #employee_id").val();
                        data.from_date = $("#form-filter #from_date").val();
                        data.to_date = $("#form-filter #to_date").val();
                        data.type = $("#form-filter #type").val();
                        data.leave_category_id = $("#form-filter #leave_category_id").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    "orderable": false,
                    "className": "text-center"
                },
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
            });

            $('#btn-filter').click(function () {
                table.ajax.reload();
            });

            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                table.ajax.reload();
            });

            $(document).on('click', '#save-btn', function () {
                let form = document.getElementById('store_or_update_form');
                let formData = new FormData(form);
                let url = "{{route('leave.store.or.update')}}";
                let id = $('#update_id').val();
                let method;
                if (id) {
                    method = 'update';
                } else {
                    method = 'add';
                }
                store_or_update_data(table, method, url, formData);
            });

            $(document).on('click', '.edit-data', function () {
                let id = $(this).data('id');
                $('#store_or_update_form')[0].reset();
                $('#store_or_update_form .select').val('');
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (id) {
                    $.ajax({
                        url: "{{route('leave.edit')}}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            if (data.status == 'error') {
                                notification(data.status, data.message)
                            } else {
                                $('#store_or_update_form #update_id').val(data.id);
                                $('#store_or_update_form #leave_category_id').val(data.leave_category_id);
                                $('#store_or_update_form #employee_id').val(data.employee_id);
                                $('#store_or_update_form #start_date').val(data.start_date);
                                $('#store_or_update_form #end_date').val(data.end_date);
                                $('#store_or_update_form #notes').val(data.notes);
                                $('#store_or_update_form #status').val(data.status);
                                $('#store_or_update_form #duration').val(data.status);
                                $('#store_or_update_form #is_paid').val(data.is_paid);

                                $('#store_or_update_form .selectpicker').selectpicker('refresh');
                                $('#store_or_update_modal').modal({
                                    keyboard: false,
                                    backdrop: 'true',
                                });
                                $('#store_or_update_modal .modal-title').html(
                                    '<i class="fas fa-edit text-white"></i> <span>{{__('file.Edit')}} ' + '</span>');
                                $('#store_or_update_modal #save-btn').text('{{__('file.Update')}}');
                            }
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $('.date-change').on('change', function () {
                let startDate = $('#start_date').val();
                let endDate = $('#end_date').val();


                if (startDate && endDate) {
                    let startDateTime = new Date(startDate);
                    let endDateTime = new Date(endDate);

                    // Calculate the duration in days
                    let timeDiff = Math.abs(endDateTime - startDateTime);
                    let daysDuration = timeDiff / (1000 * 60 * 60 * 24); // Convert milliseconds to days

                    daysDuration = daysDuration + 1;
                    $('#duration').val(daysDuration.toFixed(2));
                } else {
                    $('#duration').val('0.00');
                }
            });

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('leave.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.view-data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                if (id) {
                    $.ajax({
                        url: "{{route('leave.view')}}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        success: function (data) {
                            $('#view_modal #view-data').html('');
                            $('#view_modal #view-data').html(data);
                            $('#view_modal').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
                            $('#view_modal .modal-title').html(
                                '<i class="fas fa-eye text-white"></i> <span> ' + name + 'Leave Details</span>');
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
