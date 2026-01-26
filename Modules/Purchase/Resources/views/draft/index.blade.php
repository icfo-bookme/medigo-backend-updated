@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i
                                class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        @if (permission('draft-add'))
                            <a href="javascript:void(0);"
                               onclick="showFormModal('{{__('Add New Draft')}}','{{__('file.Save')}}')"
                               class="btn btn-primary btn-sm font-weight-bolder add_data"><i
                                    class="fas fa-plus-circle"></i> {{__('file.Add New')}}</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row">
                            <x-form.textbox labelName="{{__('Draft No')}}" name="draft_no"
                                            col="col-md-4"/>
                            <x-form.selectbox labelName="{{__('file.Status')}}" name="status" col="col-md-4"
                                              class="selectpicker">
                                @foreach (DRAFT_STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-4">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset"
                                                class="btn btn-danger btn-sm btn-elevate btn-icon mr-2 float-left"
                                                type="button" data-toggle="tooltip" data-theme="dark"
                                                title="{{__('file.Reset')}}"><i class="fas fa-undo-alt"></i></button>
                                        <button id="btn-filter"
                                                class="btn btn-primary btn-sm btn-elevate btn-icon float-left"
                                                type="button" data-toggle="tooltip" data-theme="dark"
                                                title="{{__('file.Search')}}"><i class="fas fa-search"></i></button>
                                    </div>
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
                                        <th>{{__('SL')}}</th>
                                        <th>{{__('Draft No')}}</th>
                                        <th>{{__('Amount')}}</th>
                                        <th>{{__('Status')}}</th>
                                        <th>{{__('Created By')}}</th>
                                        <th>{{__('Modified By')}}</th>
                                        <th>{{__('Action')}}</th>
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
    @include('purchase::draft.modal')
    @include('purchase::draft.statusModal')
@endsection
@push('scripts')
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
                    "url": "{{route('draft.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.draft_no = $("#form-filter #draft_no").val();
                        data.status = $("#form-filter #status").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 3, 4, 5, 6],
                    "orderable": false,
                    "className": "text-center"
                },
                    {
                        "targets": [2],
                        "orderable": false,
                        "className": "text-right"
                    }
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
            });
            $('#btn-filter').click(function () {
                table.ajax.reload();
            });
            $('#btn-reset').click(function () {
                $('#form-filter')[0].reset();
                $('#form-filter .selectpicker').selectpicker('refresh');
                table.ajax.reload();
            });
            $(document).on('click', '.add_data', function () {
                let draftNo = new Date().getTime("en-US", {timeZone: "Asia/Dhaka"});
                draft_no = 'DFT-' + draftNo;
                $('#store_or_update_form #draft_no').val(draft_no);
            });

            $(document).on('click', '#save-btn', function () {
                let form = document.getElementById('store_or_update_form');
                let formData = new FormData(form);
                let url = "{{route('draft.store.or.update')}}";
                let id = $('#update_id').val();
                let method;
                if (id) {
                    method = 'update';
                } else {
                    method = 'add';
                }
                store_or_update_data(table, method, url, formData);
            });

            $(document).on('click', '.edit_data', function () {
                $('#store_or_update_form #update_id').val($(this).data('id'));
                $('#store_or_update_form #draft_no').val($(this).data('name'));
                $('#store_or_update_form #amount').val($(this).data('amount'));
                $('#store_or_update_form #description').val($(this).data('description'));
                $('#store_or_update_modal').modal({
                    keyboard: false,
                    backdrop: 'static',
                });
                $('#store_or_update_modal .modal-title').html('<i class="fas fa-edit text-white"></i> <span>{{__('file.Edit')}}' + $(this).data('draft_no') + '</span>');
                $('#store_or_update_modal #save-btn').text('{{__('file.Update')}}');
            });

            $(document).on('click', '.status-change', function () {
                $('#status_update_form #draft_id').val($(this).data('id'));
                $('#status_update_form #status').val($(this).data('status'));
                $('#status_update_form #status').selectpicker('refresh');
                $('#change_status_modal').modal({
                    keyboard: false,
                    backdrop: 'static',
                });
                $('#change_status_modal .modal-title').html('<i class="fas fa-edit text-white"></i> <span>Change Status</span>');
                $('#status_update_form #savebtn').text('{{__('file.Update')}}');
            });

            $(document).on('click', '#savebtn', function () {
                let form = document.getElementById('status_update_form');
                let formData = new FormData(form);
                let url = "{{route('draft.change.status')}}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    success: function (data) {
                        $('#status_update_form').find('.is-invalid').removeClass('is-invalid');
                        $('#status_update_form').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function (key, value) {
                                var key = key.split('.').join('_');
                                $('#status_update_form input#' + key).addClass('is-invalid');
                                $('#status_update_form textarea#' + key).addClass('is-invalid');
                                $('#status_update_form select#' + key).parent().addClass('is-invalid');
                                $('#status_update_form #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                table.ajax.reload();
                                $('#change_status_modal').modal('hide');
                            }
                        }

                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('draft.delete') }}";
                delete_data(id, url, table, row, name);
            });
        });
    </script>
@endpush
