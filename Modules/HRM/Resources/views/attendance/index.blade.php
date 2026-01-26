@extends('layouts.app')

@section('title', $page_title)
@push('styles')
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
                        @if (permission('attendance-add'))
                            <a href="{{route('attendance')}}" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Daily Attendance
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
                                       value="" />
                            </div>
                            <div class="form-group col-md-4 required">
                                <label for="to_date">To Date</label>
                                <input type="date" class="form-control" name="to_date" id="to_date"
                                       value="" />
                            </div>
                            <x-form.selectbox labelName="{{__('Shift')}}" name="shift_id" id="shift_id"
                                              col="col-md-4" class="selectpicker">
                                @foreach ($shifts as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="{{__('Employee')}}" name="employee_id" id="employee_id"
                                              col="col-md-4" class="selectpicker">
                                @foreach ($employees as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}--{{ $row->employee_id }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Status" name="status" col="col-md-4" class="selectpicker">
                                @foreach (DAILY_ATTENDENCE as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
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
                                        <th>{{__('file.Employee')}}</th>
                                        <th>{{__('file.Shift')}}</th>
                                        <th>{{__('file.Check In')}}</th>
                                        <th>{{__('file.Check Out')}}</th>
                                        <th>{{__('file.Working Hour')}}</th>
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
    @include('hrm::attendance.view')
@endsection

@push('scripts')
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
                    "url": "{{route('empAttendance.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.employee_id = $("#form-filter #employee_id").val();
                        data.from_date = $("#form-filter #from_date").val();
                        data.to_date = $("#form-filter #to_date").val();
                        data.shift_id = $("#form-filter #shift_id").val();
                        data.status = $("#form-filter #status").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8],
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

            $(document).on('click', '.view-data', function () {
                let id = $(this).data('id');
                let name  = $(this).data('name');
                if (id) {
                    $.ajax({
                        url: "{{route('empAttendance.view')}}",
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
                                '<i class="fas fa-eye text-white"></i> <span> ' + name + 'Attendance Details</span>');
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
