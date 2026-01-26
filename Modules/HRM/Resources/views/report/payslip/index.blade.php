@extends('layouts.app')

@section('title', $page_title)
@push('styles')
<style>

</style>
@endpush
@section('content')

<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <x-form.selectbox labelName="{{__('file.Employee')}}" name="employee_id" id="employee_id" col="col-md-4" class="selectpicker">
                            @foreach ($employee as $row)
                                <option value="{{ $row->employee->id }}">{{ $row->employee->name }}--{{ $row->employee->employee_id }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.selectbox labelName="{{__('file.Salary Payment Month')}}" name="payment_month" id="payment_month" col="col-md-4" class="selectpicker">
                            @foreach ($salary_pay as $row)
                                <option value="{{ $row->payment_month}}">{{ date('F-Y', strtotime($row->payment_month))}}</option>
                            @endforeach
                        </x-form.selectbox>
                        <div class="col-md-4">
                            <div style="margin-top:28px;">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"
                                    data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">
                                    <i class="fas fa-undo-alt"></i></button>

                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"
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
                                        <th>{{__('file.Employee ID')}}</th>
                                        <th>{{__('file.Salary Payment Month')}}</th>
                                        <th>{{__('file.Basic Salary')}}</th>
                                        <th>{{__('file.Total Allowance')}}</th>
                                        <th>{{__('file.Gross Salary')}}</th>
                                        <th>{{__('file.Total Deduction')}}</th>
                                        <th>{{__('file.Provident Fund')}}</th>
                                        <th>{{__('file.Bonus')}}</th>
                                        <th>{{__('file.Net Payable')}}</th>
                                        <th>{{__('file.Payment Amount')}}</th>
                                        <th>{{__('file.Payment Status')}}</th>
                                        <th>{{__('file.Payment Note')}}</th>
                                        <th>{{__('file.Payment Date')}}</th>
                                        <th>{{__('file.Created By')}}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
{{--                                <tfoot class="bg-primary">--}}
{{--                                    <tr>--}}
{{--                                        <th colspan="4">Total</th>--}}
{{--                                        <th>0000</th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                        <th></th>--}}
{{--                                    </tr>--}}
{{--                                </tfoot>--}}
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
<script>
    var table;
    $(document).ready(function(){

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
            "pageLength": 10000, //number of data show per page
            "language": {
                processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                emptyTable: '<strong class="text-danger">No Data Found</strong>',
                infoEmpty: '',
                zeroRecords: '<strong class="text-danger">No Data Found</strong>'
            },
            "ajax": {
                "url": "{{route('payslip.datatable.data')}}",
                "type": "POST",
                "data": function (data) {
                    data.employee_id    = $("#form-filter #employee_id").val();
                    data.payment_month  = $("#form-filter #payment_month").val();
                    data._token     = _token;
                }
            },
            "columnDefs": [{
                "targets"  : [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15],
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
    });
    // Month datepicker
    var dp=$("#datepicker").datepicker( {
        format: "yyyy-mm",
        startView: "months",
        minViewMode: "months"
    });
    </script>

@endpush
