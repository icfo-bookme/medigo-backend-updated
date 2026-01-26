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
                        @if (permission('generate-salary-add'))
                            <a href="{{ route('generate.salary.create') }}"
                               class="btn btn-primary btn-sm font-weight-bolder"><i class="fas fa-plus-circle"></i> Add
                                New</a>
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
                            <x-form.selectbox labelName="{{__('Year')}}" name="year" id="year"
                                              col="col-md-4" class="selectpicker">
                                @foreach (salaryYears() as $key => $row)
                                    <option value="{{ $row }}">{{ $row }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="{{__('Month')}}" name="month" id="month"
                                              col="col-md-4" class="selectpicker">
                                @foreach (allMonths() as $key => $row)
                                    <option value="{{ $key }}">{{ $row }}</option>
                                @endforeach
                            </x-form.selectbox>
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
                                        <th>{{__('file.Month')}}</th>
                                        <th>{{__('file.Year')}}</th>
                                        <th>{{__('file.Working Days')}}</th>
                                        <th>{{__('file.Leave(Paid & Unpaid)')}}</th>
                                        <th>{{__('file.Working Hours')}}</th>
                                        <th>{{__('file.Attended Hours')}}</th>
                                        <th>{{__('file.Net Salary')}}</th>
                                        <th>{{__('file.Created By')}}</th>
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
    @include('hrm::generate-salary.view-modal')
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
                    "url": "{{route('generate.salary.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.employee_id = $("#form-filter #employee_id").val();
                        data.year = $("#form-filter #year").val();
                        data.month = $("#form-filter #month").val();
                        data.type = $("#form-filter #type").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
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
                let name = $(this).data('name');
                if (id) {
                    $.ajax({
                        url: "{{route('generate.salary.view')}}",
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
                                '<i class="fas fa-eye text-white"></i> <span> ' + name + 'Payslip Details</span>');
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '.delete-data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('generate.salary.delete') }}";
                delete_data(id, url, table, row, name);
            });
        });
    </script>
@endpush
