@extends('layouts.app')
@section('title', $page_title)
@push('styles')
@endpush

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        @if (permission('campaign-access'))
                            <a href="javascript:void(0);" onclick="showFormModal('Add New Campaign','Save')" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Add New</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        <div class="row justify-content-center">
                            <x-form.textbox labelName="Name" name="name" col="col-md-4"/>
                            <x-form.selectbox labelName="Status" name="status" col="col-md-4" class="selectpicker">
                                @foreach (STATUS as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
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
                                        <th>Sl</th>
                                        <th>Campaign Name</th>
                                        <th>Image</th>
                                        <th>Date</th>
                                        <th>Discount</th>
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
    @include('campaign::manage-campaign.modal')
@endsection
@push('scripts')
    <script src="js/spartan-multi-image-picker.min.js"></script>
    <script>
        var table;
        $(document).ready(function () {
            /** Start :: Product Image **/
            $("#image").spartanMultiImagePicker({
                fieldName: 'image',
                maxCount: 1,
                rowHeight: '150px',
                groupClassName: 'col-md-12 col-sm-12 col-xs-12',
                maxFileSize: '',
                dropFileLabel: "Drop Here",
                allowedExt: '',
            });

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
                    "url": "{{route('campaign.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.name = $("#form-filter #name").val();
                        // data.status = $("#form-filter #status").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6],
                    "orderable": false,
                    "className": "text-center"
                },
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" + "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
                "buttons": [],
            });

            let inputTimeout = 0;

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
                let url = "{{route('campaign.store.or.update')}}";
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
                let id = $(this).data('id');
                $('#store_or_update_form')[0].reset();
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (id) {
                    $.ajax({
                        url: "{{route('campaign.edit')}}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            if (data.status === 'error') {
                                notification(data.status, data.message)
                            } else {
                                $('#store_or_update_form #update_id').val(data.id);
                                $('#store_or_update_form #campaign_type').val(data.campaign_type);
                                $('#store_or_update_form #name').val(data.name);
                                $('#store_or_update_form #slug').val(data.slug);
                                $('#store_or_update_form #start_date').val(data.start_date);
                                $('#store_or_update_form #end_date').val(data.end_date);
                                $('#store_or_update_form #discount_type').val(data.discount_type);
                                $('#store_or_update_form #discount_amount').val(data.discount_amount);
                                $('#store_or_update_form .selectpicker').selectpicker('refresh');
                                // Check if the old image is available
                                if (data.image) {
                                    $('#store_or_update_form #old_image').val(data.image);
                                    $('#image img').attr('src',
                                        "{{ asset('storage/' . CAMPAIGN_IMAGE_PATH) }}" +
                                        '/' + data.image);
                                    $('#image img').css('display', 'none');
                                    $('#image .spartan_remove_row').css('display', 'block');
                                    $('#image .img_').css('display', 'block');
                                    $('.spartan_remove_row').on('click', function () {
                                        $('#store_or_update_form #old_image').val('');
                                    });
                                } else {
                                    $('#image .img_').attr('src', '');
                                }
                                $('#store_or_update_modal').modal({
                                    keyboard: false,
                                    backdrop: 'static',
                                });
                                $('#store_or_update_modal .modal-title').html(
                                    '<i class="fas fa-edit text-white"></i> <span>Edit ' + data.name + '</span>');
                                $('#store_or_update_modal #save-btn').text('Update');
                            }

                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '.delete_data', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('campaign.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('campaign.change.status') }}";
                change_status(id, url, table, row, name, status);
            });
        });

        function slugGenerator(name, slug) {
            var value = name.toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
            $('#' + slug).val(value);
        }
    </script>
@endpush
