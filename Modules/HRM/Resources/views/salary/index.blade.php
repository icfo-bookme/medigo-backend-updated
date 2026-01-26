@extends('layouts.app')

@section('title', $page_title)
@push('styles')
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <form action="{{ url('/salary/add-employee-salary/go') }}" method="post" class="col-md-12 px-0">
                        @csrf
                        <div class="row">
                            <div class="col-md-2"></div>
                            <x-form.selectbox labelName="{{__('Employee Salary')}}" name="employee_id" id="employee_id" col="col-md-5" class="selectpicker">
                                @foreach ($employee as $row)
                                    <option value="{{ $row->id }}">{{ $row->name }}--{{ $row->employee_id }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-3">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button type="submit" class="btn btn-info btn-flat"><i class="icon fa fa-arrow-right"></i> {{ __('Assign Salary') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.selectbox labelName="{{__('Employee')}}" name="employee_id" id="employee_id" col="col-md-6" class="selectpicker">
                                @foreach ($salary_emp as $row)
                                    <option value="{{ $row->employee->id }}">{{ $row->employee->name }}--{{ $row->employee->employee_id }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-6">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">
                                            <i class="fas fa-undo-alt"></i></button>

                                        <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}">
                                            <i class="fas fa-search"></i>
                                        </button>
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
                                        <th>{{__('file.Employee ID')}}</th>
                                        <th>{{__('file.Designation')}}</th>
                                        <th>{{__('file.Employee Type')}}</th>
                                        <th>{{__('file.Total Allowance')}}</th>
                                        <th>{{__('file.Total Deduction')}}</th>
                                        <th>{{__('file.Gross Salary')}}</th>
                                        <th>{{__('file.Net Payable')}}</th>
                                        <th>{{__('file.Date')}}</th>
                                        <th>{{__('file.Month')}}</th>
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
                    "url": "{{route('salary.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.employee_id = $("#form-filter #employee_id").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11],
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

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let depart_code = $(this).data('depart_code');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('salary.delete') }}";
                delete_data(id, url, table, row, name, depart_code);
            });

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('salary.change.status') }}";
                change_status(id, url, table, row, name, status);
            });
        });
    </script>
@endpush
