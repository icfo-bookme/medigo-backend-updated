@extends('layouts.app')
@section('title', $page_title)
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        @if (permission('customer-add'))
                            <a href="javascript:void(0);" onclick="showFormModal('Add New Customer','Save')" class="btn btn-primary btn-sm font-weight-bolder mr-2"><i
                                    class="fas fa-plus-circle"></i> Add New</a>
                            <a href="{{ route('customer.bulk.import') }}" class="btn btn-info btn-sm font-weight-bolder"><i
                                    class="fas fa-file-import"></i> Bulk Import</a>
                        @endif
                    </div>
                    <form method="POST" id="form-filter" class="col-md-12 px-0 mt-4">
                        <div class="row">
                            <x-form.textbox labelName="Search Here" name="search_text" col="col-md-3" placeholder="Search By Name, Phone no"/>
                            <x-form.selectbox labelName="Sort Table" name="sort_table" col="col-md-3" class="selectpicker">
                                <option value="latest">Latest</option>
                                <option value="oldest">Oldest</option>
                            </x-form.selectbox>
                            <div class="col-md-0">
                                <div style="margin-top:28px;">
                                    <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button" data-toggle="tooltip" data-theme="dark" title="Reset"><i
                                            class="fas fa-undo-alt"></i></button>
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
                                        <th>
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="select_all"
                                                       onchange="select_all()">
                                                <label class="custom-control-label" for="select_all"></label>
                                            </div>
                                        </th>
                                        <th>Sl</th>
                                        <th>Customer Name</th>
                                        <th>Mobile No.</th>
                                        <th>Address</th>
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
    @include('customer::modal')
    @include('customer::customer_point')
    @include('customer::notification_modal')
    @include('sale::order.view-order')
@endsection
@push('scripts')
    <script src="js/spartan-multi-image-picker.min.js"></script>
    <script>
        let table, inputTimeout;
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
                onExtensionErr: function (index, file) {
                    console.log(index, file, 'extension err');
                    Swal.fire({
                        type: 'error',
                        title: 'Error',
                        text: 'Please upload only png, jpg, jpeg, svg, webp format image!',
                        icon: 'warning',
                    });
                },
            });
            /** End :: Product Image **/

            table = $('#dataTable').DataTable({
                "processing": true, //Feature control the processing indicator
                "serverSide": true, //Feature control DataTable server side processing mode
                "order": [], //Initial no order
                "responsive": true, //Make table responsive in mobile device
                "bInfo": true, //TO show the total number of data
                "bFilter": false, //For datatable default search box show/hide
                "lengthMenu": [
                    [5, 10, 15, 25, 50, 100, 1000],
                    [5, 10, 15, 25, 50, 100, 1000]
                ],
                "pageLength": 25, //number of data show per page
                "language": {
                    processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                    emptyTable: '<strong class="text-danger">No Data Found</strong>',
                    infoEmpty: '',
                    zeroRecords: '<strong class="text-danger">No Data Found</strong>'
                },
                "ajax": {
                    "url": "{{ route('customer.datatable.data') }}",
                    "type": "POST",
                    "data": function (data) {
                        data.search_text = $('#form-filter #search_text').val();
                        data.sort_table = $('#form-filter #sort_table').val();

                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6],
                    "orderable": false,
                    "className": "text-center"
                }],
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
                    {
                        'className': 'btn btn-info btn-sm text-white send_noti_btn d-none',
                        'text': '<i class="fas fa-paper-plane"></i> Send Notification',
                        action: function() {
                            openNotificationModal();
                        }
                    }
                ],
            });

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

            setupInputTableReload('#form-filter #search_text', 'input', 300);
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
                let url = "{{route('customer.store.or.update')}}";
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
                        url: "{{route('customer.edit')}}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            if (data.status === 'error') {
                                notification(data.status, data.message)
                            } else {
                                $('#store_or_update_form #update_id').val(data.id);
                                $('#store_or_update_form #name').val(data.name);
                                $('#store_or_update_form #phone').val(data.phone);
                                $('#store_or_update_form #email').val(data.email);
                                $('#store_or_update_form #country').val(data.country);
                                $('#store_or_update_form #district').val(data.district);
                                $('#store_or_update_form #city').val(data.city);
                                $('#store_or_update_form #thana').val(data.thana);
                                $('#store_or_update_form #area').val(data.area);
                                $('#store_or_update_form #information').val(data.information);
                                // $('#store_or_update_form .selectpicker').selectpicker('refresh');
                                $('#store_or_update_modal').modal({
                                    keyboard: false,
                                    backdrop: 'true',
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

            $(document).on('click', '.set_point', function () {
                let id = $(this).data('id');
                $('#customer_point_form')[0].reset();
                $('#customer_point_form').find('.is-invalid').removeClass('is-invalid');
                $('#customer_point_form').find('.error').remove();
                if (id) {
                    $.ajax({
                        url: "{{ route('get.customer.point') }}",
                        type: "GET",
                        data: {customer_id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            if (data.status === 'error') {
                                notification(data.status, data.message);
                            } else {
                                // Populate form fields with customer_point data
                                let point = data.customer_point;

                                $('#customer_point_form #customer_id').val(data.customer_id);
                                $('#available_point').val(point?.available_point ?? '');
                                $('#conversion_rate').val(point?.conversion_rate ?? '');
                                $('#min_use_point').val(point?.min_use_point ?? '');

                                // Update the modal title with customer_name
                                $('#customer_point_modal .modal-title').html(
                                    '<i class="fas fa-coins text-white"></i> <span>Set ' + data.customer_name + ' Point</span>'
                                );

                                // Show the modal
                                $('#customer_point_modal').modal({
                                    keyboard: false,
                                    backdrop: 'true',
                                });
                            }
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.error(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '#customer_point_form #set-point', function () {
                let form = document.getElementById('customer_point_form');
                let formData = new FormData(form);
                let url = "{{ route('set.customer.point') }}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        $('#set-point').prop('disabled', true);
                        $('#set-point').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
                        $('#set-point').prop('disabled', false);
                        $('#set-point').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        if (data.status === 'error') {
                            $.each(data.errors, function (key, value) {
                                $('#customer_point_form #' + key).addClass('is-invalid');
                                $('#customer_point_form #' + key).parent().append('<div class="error invalid-feedback">' + value + '</div>');
                            });
                            $('#set-point').prop('disabled', false);
                        } else {
                            $('#customer_point_modal').modal('hide');
                            notification(data.status, data.message);
                            table.ajax.reload();
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
                let url = "{{ route('customer.delete') }}";
                delete_data(id, url, table, row, name);
            });

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('customer.change.status') }}";
                change_status(id, url, table, row, name, status);
            });

            $(document).on('click', '.view_order', function () {
                let customer_id = $(this).data('customer_id');
                if (customer_id) {
                    $.ajax({
                        url: "{{route('customer.view.order')}}",
                        type: "POST",
                        data: {customer_id: customer_id, _token: _token},
                        success: function (data) {
                            $('#order_list tbody').empty();
                            $.each(data.data, function (index, order) {
                                let order_discount = order.total_discount ? order.total_discount : 0;
                                let paid_amount = order.paid_amount ? order.paid_amount : 0;
                                var row = '<tr class="text-center">' +
                                    '<td>' + (index + 1) + '</td>' +
                                    '<td>' + data.delivery_status[order.delivery_status] + '</td>' +
                                    '<td>' + order.invoice_no + '</td>' +
                                    '<td>' + order.total_price + '</td>' +
                                    '<td>' + order_discount + '</td>' +
                                    '<td>' + order.grand_total + '</td>' +
                                    '<td>' + paid_amount + '</td>' +
                                    '<td>' + order.sale_date + '</td>' +
                                    '</tr>';

                                $('#order_list tbody').append(row);
                            });
                            $('#order_modal').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
                            $('#order_modal .modal-title').html(
                                '<i class="fas fa-history text-white"></i> <span>View ' + customer_phone_no + ' Order History</span>');
                        },
                        error: function (xhr, ajaxOption, thrownError) {
                            console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                        }
                    });
                }
            });

            $(document).on('click', '#send-noti-btn', function () {
                let form = document.getElementById('notification_form');
                let formData = new FormData(form);
                let url = "{{ route('customer.send.push.notification') }}";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: formData,
                    dataType: "JSON",
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        $('#send-noti-btn').prop('disabled', true);
                        $('#send-noti-btn').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
                        $('#send-noti-btn').prop('disabled', false);
                        $('#send-noti-btn').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            notification(data.status, data.message);
                            table.ajax.reload();
                            $('#notification_modal').modal('hide');
                        } else {
                            notification(data.status, data.message);
                        }
                    },
                    error: function(xhr) {
                        $('#save-btn').prop('disabled', false).removeClass('spinner spinner-white spinner-right');
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            let allErrors = 'The given data was invalid. Please check the errors below:\n';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                allErrors += value[0] + '\n';
                                let element = $('#store_or_update_form').find('#' + key);

                                // Remove any existing error messages before adding new ones
                                element.removeClass('is-invalid'); // Remove previous invalid class
                                element.parent().find('.error').remove(); // Remove previous error messages

                                // Add the new error class and error message
                                element.addClass('is-invalid');
                                element.parent().append( '<small class="error text-danger">' + value[0] + '</small>');
                            });
                            notification('error', allErrors);
                        } else {
                            notification('error', xhr.responseJSON.message || 'An unexpected error occurred.');
                            console.error(xhr.responseText);
                        }
                    }
                });
            });
        });
        function multi_delete() {
            let ids = [];
            let rows;
            $('.select_data:checked').each(function () {
                ids.push($(this).val());
                rows = table.rows($('.select_data:checked').parents('tr'));
            });
            if (ids.length === 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                let url = "{{route('customer.bulk.delete')}}";
                bulk_delete(ids, url, table, rows);
            }
        }

        function openNotificationModal() {
            let ids = [];
            $('.select_data:checked').each(function () {
                ids.push($(this).val());
            });
            if (ids.length === 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                let customer_ids = ids;
                $('#notification_form #selected_ids').val(customer_ids);
                $('#notification_modal').modal({
                    keyboard: false,
                    backdrop: 'true',
                });
                $('#notification_modal .modal-title').html('<i class="fas fa-bell text-white"></i> <span>Send Push Notification</span>');
            }
        }

        function sendNotification() {
            let ids = [];
            $('.select_data:checked').each(function () {
                ids.push($(this).val());
            });
            if (ids.length === 0) {
                Swal.fire({
                    type: 'error',
                    title: 'Error',
                    text: 'Please checked at least one row of table!',
                    icon: 'warning',
                });
            } else {
                {{--let url = "{{ route('customer.send.notification') }}";--}}
                let url = "";
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {ids: ids, _token: _token},
                    success: function (data) {
                        notification(data.status, data.message);
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        }
    </script>
@endpush
