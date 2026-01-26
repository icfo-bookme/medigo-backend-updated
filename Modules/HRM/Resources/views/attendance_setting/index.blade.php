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
                    @if (permission('branch-add'))
                        <a href="javascript:void(0);" onclick="showFormModal('{{__('Attendance Time Add')}}','{{__('file.Save')}}')" class="btn btn-primary btn-sm font-weight-bolder">
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
{{--            <div class="card-header flex-wrap py-5">--}}
{{--                <form method="POST" id="form-filter" class="col-md-12 px-0">--}}
{{--                    <div class="row">--}}
{{--                        <x-form.textbox labelName="{{__('file.Branch Name')}}" name="name" col="col-md-4" />--}}
{{--                        <div class="col-md-8">--}}
{{--                            <div style="margin-top:28px;">--}}
{{--                                <div style="margin-top:28px;">--}}
{{--                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon float-right" type="button"--}}
{{--                                    data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">--}}
{{--                                    <i class="fas fa-undo-alt"></i></button>--}}

{{--                                    <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-right" type="button"--}}
{{--                                    data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}">--}}
{{--                                    <i class="fas fa-search"></i></button>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </form>--}}
{{--            </div>--}}
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <table id="dataTable" class="table table-bordered table-hover">
                                <thead class="bg-primary">
                                    <tr>
                                        <th>{{__('file.SL')}}</th>
                                        <th>{{__('shift')}}</th>
                                        <th>{{__('start time')}}</th>
                                        <th>{{__('end time')}}</th>
                                        <th>{{__('Working Hour')}}</th>
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
@include('hrm::attendance_setting.modal')
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
            "pageLength": 25, //number of data show per page
            "language": {
                processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                emptyTable: '<strong class="text-danger">No Data Found</strong>',
                infoEmpty: '',
                zeroRecords: '<strong class="text-danger">No Data Found</strong>'
            },
            "ajax": {
                "url": "{{route('attendanceSetting.datatable.data')}}",
                "type": "POST",
                "data": function (data) {
                    // data.name      = $("#form-filter #name").val();
                    data._token     = _token;
                }
            },
            "columnDefs": [{
                "targets"  : [0,1,2,3,4,5,6],
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
            let form        = document.getElementById('store_or_update_form');
            let formData    = new FormData(form);
            let url         = "{{route('attendanceSetting.store.or.update')}}";
            let id          = $('#update_id').val();
            let method;
            if (id) {
                method = 'update';
            } else {
                method = 'add';
            }
            store_or_update_data(table, method, url, formData);
        });

        $(document).on('click', '.edit_data', function () {
            let id = $(this).data('id');
            $('#store_or_update_form')[0].reset();
            $('#store_or_update_form .select').val('');
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            if (id) {
                $.ajax({
                    url: "{{route('attendanceSetting.edit')}}",
                    type: "POST",
                    data: { id: id,_token: _token},
                    dataType: "JSON",
                    success: function (data) {
                        if(data.status == 'error'){
                            notification(data.status,data.message)
                        }else{
                            $('#store_or_update_form #update_id').val(data.id);
                            $('#store_or_update_form #shift').val(data.shift);
                            $('#store_or_update_form #start_time').val(data.start_time);
                            $('#store_or_update_form #end_time').val(data.end_time);
                            $('#store_or_update_form #status').val(data.status);
                            $('#store_or_update_form .selectpicker').selectpicker('refresh');
                            $('#store_or_update_modal').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
                            $('#store_or_update_modal .modal-title').html(
                                '<i class="fas fa-edit text-white"></i> <span>{{__('file.Edit')}} ' + data.name + '</span>');
                            $('#store_or_update_modal #save-btn').text('{{__('file.Update')}}');
                        }
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
