@extends('layouts.app')

@section('title', $page_title)

@push('styles')
<link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css" />

    <style>

        .image-container {
            position: relative;
            display: inline-block; /* To make the container inline with the text */
        }

        .image-container img {
            display: block; /* Ensure the image takes up its container's width */
            max-width: 100%; /* Ensure the image doesn't overflow the container */
            opacity: 0.6;
        }

        .image-container i {
            position: absolute;
            top: 50%; /* Center vertically */
            left: 50%; /* Center horizontally */
            transform: translate(-50%, -50%); /* Center both vertically and horizontally */
            color: #007bff; /* Set the color of the icon */
            font-size: 24px; /* Adjust the font size as needed */
        }

    </style>
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
{{--                <div class="card-toolbar">--}}
{{--                    <!--begin::Button-->--}}
{{--                    @if (permission('unit-add'))--}}
{{--                    <a href="javascript:void(0);" onclick="showFormModal('Add New Prescription Order','Save')" class="btn btn-primary btn-sm font-weight-bolder">--}}
{{--                        <i class="fas fa-plus-circle"></i> Add New</a>--}}
{{--                        @endif--}}
{{--                    <!--end::Button-->--}}
{{--                </div>--}}
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">

            <div class="card-header flex-wrap py-5">
                <form method="POST" id="form-filter" class="col-md-12 px-0">
                    <div class="row">
                        <x-form.textbox labelName="Mobile No." name="mobile_no" col="col-md-3"/>


                        <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                            <option value="latest">Latest</option>
                            <option value="oldest">Oldest</option>
                            <option value="pending">Pending</option>
                        </x-form.selectbox>

                        <div class="col-md-2">
                            <div style="margin-top:28px;">
                                <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button"
                                        data-toggle="tooltip" data-theme="dark" title="Reset">
                                    <i class="fas fa-undo-alt"></i></button>

                                <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2" type="button"
                                        data-toggle="tooltip" data-theme="dark" title="Search">
                                    <i class="fas fa-search"></i></button>
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
                                        <th>Prescription</th>
                                        <th>Customer Name</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Status</th>
{{--                                        <th>Created By</th>--}}
{{--                                        <th>Modified By</th>--}}
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="largeModal" tabindex="-1" role="dialog" aria-labelledby="basicModal"
                     aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-body">
                                <!-- carousel -->
                                <div id='carouselExampleIndicators' class='carousel slide' data-ride='carousel'>
                                    <ol class='carousel-indicators'>
                                        <li data-target='#carouselExampleIndicators' data-slide-to='0'
                                            class='active'>
                                        </li>
                                        <li data-target='#carouselExampleIndicators' data-slide-to='1'></li>
                                    </ol>
                                    <div class='carousel-inner ' id="imageList">
                                       <div class='carousel-item active'>
                                            <img class='img-size' src='https://images.unsplash.com/photo-1603366615917-1fa6dad5c4fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8c29saWQlMjBibGFja3xlbnwwfHwwfHx8MA%3D%3D&auto=format&fit=crop&w=2000&q=60%202000w' alt='First slide' />
                                          </div>
                                    </div>

                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default text-center" data-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>


                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>
@include('sale::prescription-order.modal')
@include('sale::prescription-order.includes.status-modal')

@endsection

@push('scripts')
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
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
                "url": "{{route('prescription-order.datatable.data')}}",
                "type": "POST",
                "data": function (data) {
                    data.sort_table = $("#form-filter #sort_table").val();
                    data.mobile_no    = $("#form-filter #mobile_no").val();
                    data._token   = _token;
                }
            },
            "columnDefs": [{
                "targets": [0,1,2,3,4,5,6],
                "orderable": false,
                "className": "text-center"
            }, ]

        });

        $('#btn-filter').click(function () {
            table.ajax.reload();
        });
let inputTimeout;
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

        setupInputTableReload('#form-filter #mobile_no', 'input', 500);
        setupTableReloadOnChange('#form-filter #sort_table');

        $('#btn-reset').click(function () {
            $('#form-filter')[0].reset();
            $('#form-filter .selectpicker').selectpicker('refresh');
            table.ajax.reload();
        });

        $(document).on('click', '#save-btn', function () {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            let url = "{{route('prescription-order.store.or.update')}}";
            let id = $('#update_id').val();
            let method;
            if (id) {
                method = 'update';
            } else {
                method = 'add';
            }

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "JSON",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function(){
                    $('#save-btn').addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#save-btn').removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                    $('#store_or_update_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            $('#store_or_update_form #' + key).parent().append(
                                '<small class="error text-danger">' + value + '</small>');
                            });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            if (method == 'update') {
                                table.ajax.reload(null, false);
                            } else {
                                table.ajax.reload();
                            }
                            $('#store_or_update_modal').modal('hide');

                        }
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        });

        $(document).on('click', '.edit_data', function () {
            let id = $(this).data('id');
            $('#store_or_update_form')[0].reset();
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            if (id) {
                $.ajax({
                    url: "{{route('coupon.edit')}}",
                    type: "POST",
                    data: { id: id,_token: _token},
                    dataType: "JSON",
                    success: function (data) {
                        $('#store_or_update_form #update_id').val(data.id);
                        $('#store_or_update_form #name').val(data.name);
                        $('#store_or_update_form #type').val(data.type);
                        $('#store_or_update_form #value').val(data.value);
                        $('#store_or_update_form #status').val(data.status);
                        $('#store_or_update_form .selectpicker').selectpicker('refresh');

                        $('#store_or_update_modal').modal({
                            keyboard: false,
                            backdrop: 'true',
                        });
                        $('#store_or_update_modal .modal-title').html(
                            '<i class="fas fa-edit"></i> <span>Edit ' + data.unit_name + '</span>');
                        $('#store_or_update_modal #save-btn').text('Update');

                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        });

        $(document).on('click', '.delete_data', function () {
            let id    = $(this).data('id');
            let name  = $(this).data('name');
            let row   = table.row($(this).parent('tr'));
            let url   = "{{ route('prescription-order.delete') }}";
            Swal.fire({
                title: 'Are you sure to delete ' + name + ' data?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: url,
                        type: "POST",
                        data: { id: id, _token: _token},
                        dataType: "JSON",
                    }).done(function (response) {
                        if (response.status == "success") {
                            Swal.fire("Deleted", response.message, "success").then(function () {
                                table.row(row).remove().draw(false);
                            });
                        }
                        if (response.status == "error") {
                            Swal.fire('Oops...', response.message, "error");
                        }
                    }).fail(function () {
                        Swal.fire('Oops...', "Somthing went wrong with ajax!", "error");
                    });
                }
            });
        });


        //Show Status Change Modal
        $(document).on('click', '.change_status', function () {
            $('#approve_status_form #status_id').val($(this).data('id'));
            $('#approve_status_form #status').val($(this).data('status'));
            $('#approve_status_form #visa_status.selectpicker').selectpicker('refresh');
            $('#approve_status_modal').modal({
                keyboard: false,
                backdrop: 'true',
            });
            $('#approve_status_modal .modal-title').html('<span>{{__('Change Status')}}</span>');
            $('#approve_status_modal #status-btn').text('{{__('Change Status')}}');

        });

        $(document).on('click', '#status-btn', function () {
            var id = $('#approve_status_form #status_id').val();
            var status = $('#approve_status_form #status').val();
            $.ajax({
                url: "{{route('prescription-order.change.status')}}",
                type: "POST",
                data: {id: id, status: status, _token: _token},
                dataType: "JSON",
                beforeSend: function () {
                    $('#status-btn').addClass('kt-spinner kt-spinner--md kt-spinner--light');
                },
                complete: function () {
                    $('#status-btn').removeClass('kt-spinner kt-spinner--md kt-spinner--light');
                },
                success: function (data) {
                    notification(data.status, data.message);
                    if (data.status == 'success') {
                        $('#approve_status_modal').modal('hide');
                        table.ajax.reload(null, false);
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        });


        $(document).on('click', '.show-image', function() {
            let id = $(this).data('id');
            let imagePath = $(this).data('image_path');
            let filename = $(this).data('image');

            if (id) {
                            let html = '';
                            $('#imageList').empty();

                                let new_list = imagePath + '/' + filename;


                                html += '<div class="carousel-item active"><img class="img-size" src="' + new_list +
                                    '" alt="' + filename + '"  width="100%" height="100%"/></div>';

                            $('#imageList').append(html);

            }
        });



    });
    </script>
@endpush
