@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <style>
        select {
            width: 100%;
            /*min-height: 100px;*/
            border-radius: 3px;
            border: 1px solid #444;
            padding: 10px;
            color: #444444;
            font-size: 14px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
            color: #495057;
            height: 25px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background: #8950FC;
            padding: 3px 3px !important;
            color: white !important;
        }

    </style>
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
    <!-- select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>

    <!-- select2-bootstrap4-theme -->
    <link href="https://raw.githack.com/ttskch/select2-bootstrap4-theme/master/dist/select2-bootstrap4.css" rel="stylesheet">
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
                        @if (permission('coupon-add'))
                            <a href="javascript:void(0);" onclick="showFormModal('Add Coupon Categories','Save')" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Add New</a>
                        @endif
                        <!--end::Button-->
                    </div>
                </div>
            </div>
            <!--end::Notice-->
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary text-center">
                                    <tr>
                                        <th width="0.5%">Sl</th>
                                        <th width="1%">Coupon</th>
                                        <th width="1%">Category</th>
                                        <th width="3%">Date</th>
                                        <th width="1%">Status</th>
                                        <th width="1%">Action</th>
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
    @include('coupon::category-coupon.modal')
@endsection

@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        var table;
        $(document).ready(function () {
            $('.js-example-basic-multiple').each(function () {
                $(this).select2({
                    placeholder: " Select ",
                    theme: 'bootstrap4',
                    width: 'style',
                    allowClear: Boolean($(this).data('allow-clear')),
                });
            });
            $('#pieces').select2({
                tags: true
            });
            $('#show').on('click', function (e) {
                alert($('#pieces').val());
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
                    "url": "{{ route('category.coupon.datatable.data') }}",
                    "type": "POST",
                    "data": function (data) {
                        data._token = _token;
                        data.check_category = 1;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 4, 5],
                    "className": "text-center"
                },
                    {
                        "targets": [1, 2, 3, 4, 5],
                        "orderable": false
                    }
                ],
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
                let update_id = $('#update_id').val();
                let url;

                if (update_id) {
                    url = "{{ route('category.coupon.update') }}";
                } else {
                    url = "{{ route('category.coupon.store') }}";
                }

                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    processData: false,
                    cache: false,
                    beforeSend: function () {
                        $('#save-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
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
                                table.ajax.reload();
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
                        url: "{{ route('category.coupon.edit') }}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            $('#store_or_update_form #update_id').val(data.id);
                            $('#store_or_update_form #coupon_id').val(data.id);
                            $('#store_or_update_form #type').val(data.type);
                            $('#store_or_update_form #value').val(data.value);
                            $('#store_or_update_form #start_date').val(data.start_date);
                            $('#store_or_update_form #end_date').val(data.end_date);
                            $('#store_or_update_form .selectpicker').selectpicker('refresh');

                            // Pre-select categories
                            let selectedCategories = [];
                            data.categories.forEach(function (category) {
                                selectedCategories.push(category.category_id);
                            });
                            $('#store_or_update_form #category_id').val(selectedCategories);
                            $('#store_or_update_form #category_id').trigger('change');
                            $('#store_or_update_form #category_id').trigger('select2:render');

                            // $('#store_or_update_form #type').prop('disabled', true).css('background-color', '#6c757d');
                            $('#store_or_update_form #value').prop('readonly', true).addClass('bg-secondary');
                            $('#store_or_update_form #start_date').prop('readonly', true).addClass('bg-secondary');
                            $('#store_or_update_form #end_date').prop('readonly', true).addClass('bg-secondary');
                            $('#store_or_update_modal').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
                            $('#store_or_update_modal .modal-title').html(
                                '<i class="fas fa-edit"></i> <span>Edit ' + data.name + '</span>');
                            $('#store_or_update_modal #save-btn').text('Update');

                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            {{--$(document).on('click', '.delete_data', function () {--}}
            {{--    let id = $(this).data('id');--}}
            {{--    let name = $(this).data('name');--}}
            {{--    let row = table.row($(this).parent('tr'));--}}
            {{--    let url = "{{ route('category.coupon.delete') }}";--}}
            {{--    Swal.fire({--}}
            {{--        title: 'Are you sure to delete ' + name + ' data?',--}}
            {{--        text: "You won't be able to revert this!",--}}
            {{--        icon: 'warning',--}}
            {{--        showCancelButton: true,--}}
            {{--        confirmButtonColor: '#3085d6',--}}
            {{--        cancelButtonColor: '#d33',--}}
            {{--        confirmButtonText: 'Yes, delete it!'--}}
            {{--    }).then((result) => {--}}
            {{--        if (result.value) {--}}
            {{--            $.ajax({--}}
            {{--                url: url,--}}
            {{--                type: "POST",--}}
            {{--                data: {id: id, _token: _token},--}}
            {{--                dataType: "JSON",--}}
            {{--            }).done(function (response) {--}}
            {{--                if (response.status == "success") {--}}
            {{--                    Swal.fire("Deleted", response.message, "success").then(function () {--}}
            {{--                        table.row(row).remove().draw(false);--}}
            {{--                        base_unit();--}}
            {{--                    });--}}
            {{--                }--}}
            {{--                if (response.status == "error") {--}}
            {{--                    Swal.fire('Oops...', response.message, "error");--}}
            {{--                }--}}
            {{--            }).fail(function () {--}}
            {{--                Swal.fire('Oops...', "Somthing went wrong with ajax!", "error");--}}
            {{--            });--}}
            {{--        }--}}
            {{--    });--}}
            {{--});--}}

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let status = $(this).data('status');
                let name = $(this).data('name');
                let url = "{{ route('category.coupon.change.status') }}";
                Swal.fire({
                    title: 'Are you sure to change ' + name + ' status?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes!'
                }).then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: url,
                            type: "POST",
                            data: {id: id, status: status, _token: _token},
                            dataType: "JSON",
                        }).done(function (response) {
                            if (response.status == "success") {
                                Swal.fire("Status Changed", response.message, "success").then(function () {
                                    table.ajax.reload(null, false);
                                    base_unit();
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
        });

        function getCoupon(id) {
            $.ajax({
                url: "{{ route('get.coupon') }}",
                type: "GET",
                data: {id: id, _token: _token},
                dataType: "JSON",
                success: function (data) {
                    if (data.status == 'error') {
                        notification(data.status, data.message);
                    } else {
                        $('#store_or_update_form #type').val(data.type);
                        $('#store_or_update_form #value').val(data.value);
                        $('#store_or_update_form #start_date').val(data.start_date);
                        $('#store_or_update_form #end_date').val(data.end_date);
                        $('#store_or_update_form #type.selectpicker').selectpicker('refresh');

                        // $('#store_or_update_form #type').prop('disabled', true).css('background-color', '#6c757d');
                        $('#store_or_update_form #value').prop('readonly', true).addClass('bg-secondary');
                        $('#store_or_update_form #start_date').prop('readonly', true).addClass('bg-secondary');
                        $('#store_or_update_form #end_date').prop('readonly', true).addClass('bg-secondary');
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    </script>
@endpush
