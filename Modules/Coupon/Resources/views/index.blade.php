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
                        @if (permission('coupon-add'))
                            <a href="javascript:void(0);" onclick="showFormModal('Add New Coupon','Save')" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Add New</a>
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
                            <x-form.selectbox labelName="Coupon" name="coupon_id" required="required" col="col-md-4"
                                              class="selectpicker">
                                @foreach ($coupons as $c_key => $c_value)
                                    <option value="{{ $c_key }}">{{ $c_value }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Coupon Type" name="coupon_type" required="required" col="col-md-3"
                                              class="selectpicker">
                                <option value="1">General Coupon</option>
                                <option value="2">Category Coupon</option>
                                <option value="3">Customer Coupon</option>
                            </x-form.selectbox>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>
                            <div class="col-md-0">
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
                                    <thead class="bg-primary text-center">
                                    <tr>
                                        <th width="0.5%">Sl</th>
                                        <th width="1%">Coupon</th>
                                        <th width="1%">Value</th>
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
    @include('coupon::modal')
@endsection

@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
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
                    "url": "{{route('coupon.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.coupon_id = $('#form-filter #coupon_id').val();
                        data.coupon_type = $('#form-filter #coupon_type').val();
                        data.sort_table = $("#form-filter #sort_table").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 4, 5],
                    "className": "text-center"
                },
                    {
                        "targets": [0, 1, 2, 3, 4, 5],
                        "orderable": false,
                        "className": "text-center"
                    }
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",
                "buttons": [
                    {
                        'extend': 'colvis', 'className': 'btn btn-secondary btn-sm text-white', 'text': 'Column', 'columns': ':gt(0)'
                    },
                    {
                        "extend": 'print',
                        'text': '{{__('Print')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "orientation": "landscape", //portrait
                        "pageSize": "legal", //A3,A5,A6,legal,letter
                        customize: function (win) {
                            $(win.document.body).addClass('bg-white');
                            $(win.document.body).find('table thead').css({'background': '#034d97'});
                            $(win.document.body).find('table tfoot tr').css({'background-color': '#034d97'});
                            $(win.document.body).find('h1').css('text-align', 'center');
                            $(win.document.body).find('h1').css('font-size', '15px');
                            $(win.document.body).find('table').css('font-size', 'inherit');
                        },
                    },
                    {
                        "extend": 'csv',
                        'text': '{{__('CSV')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    },
                    {
                        "extend": 'excel',
                        'text': '{{__('Excel')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    },
                    {
                        "extend": 'pdf',
                        'text': '{{__('PDF')}}',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                        "orientation": "landscape", //portrait
                        "pageSize": "legal", //A3,A5,A6,legal,letter

                        customize: function (doc) {
                            doc.defaultStyle.fontSize = 7; //<-- set fontsize to 16 instead of 10
                            doc.styles.tableHeader.fontSize = 7;
                            doc.pageMargins = [5, 5, 5, 5];
                        }
                    },
                ],
            });

            function setupTableReloadOnChange(selector) {
                $(document).on('change', selector, function () {
                    table.ajax.reload();
                });
            }

            setupTableReloadOnChange('#form-filter #coupon_id');
            setupTableReloadOnChange('#form-filter #coupon_type');
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
                let url = "{{route('coupon.store.or.update')}}";
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
                        data: {id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            $('#store_or_update_form #update_id').val(data.id);
                            $('#store_or_update_form #name').val(data.name);
                            $('#store_or_update_form #coupon_type').val(data.coupon_type);
                            $('#store_or_update_form #type').val(data.type);
                            $('#store_or_update_form #value').val(data.value);
                            $('#store_or_update_form #coupon_value_limit').val(data.coupon_value_limit);
                            $('#store_or_update_form #start_date').val(data.start_date);
                            $('#store_or_update_form #end_date').val(data.end_date);
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
                let id = $(this).data('id');
                let name = $(this).data('name');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('coupon.delete') }}";
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
                            data: {id: id, _token: _token},
                            dataType: "JSON",
                        }).done(function (response) {
                            if (response.status == "success") {
                                Swal.fire("Deleted", response.message, "success").then(function () {
                                    table.row(row).remove().draw(false);
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


            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let status = $(this).data('status');
                let name = $(this).data('name');
                let url = "{{ route('coupon.change.status') }}";
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
    </script>
@endpush
