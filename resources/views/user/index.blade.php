@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link href="plugins/custom/datatables/datatables.bundle.css" rel="stylesheet" type="text/css"/>
    <style>
        table#dataTable {
            width: 1500px !important;
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
                    <div class="card-toolbar">
                        <!--begin::Button-->
                        @if (permission('user-add'))
                            <a href="javascript:void(0);" onclick="showUserFormModal('Add New Employee','Save')" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> Add New
                            </a>
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
                            <x-form.textbox labelName="Search Here" name="search_text" col="col-md-3" placeholder="Search here"/>
                            <x-form.selectbox labelName="Role" name="role_id" col="col-md-3" class="selectpicker">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->role_name }}</option>
                                @endforeach
                            </x-form.selectbox>
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
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12 table-responsive" style="min-height: 300px;">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                    <tr>
                                        <th>Sl</th>
                                        <th>Avatar</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Showroom</th>
                                        <th>Phone No.</th>
                                        <th>Email</th>
                                        <th>Gender</th>
                                        <th>Status</th>
                                        <th>Created By</th>
                                        <th>Modified By</th>
                                        <th>Created At</th>
                                        <th>Updated At</th>
                                        <th>Action</th>
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
    @include('user.modal')
    @include('user.view')
@endsection

@push('scripts')
    <script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>
    <script>
        var table, inputTimeout;
        $(document).ready(function () {

            table = $('#dataTable').DataTable({
                "processing": true, //Feature control the processing indicator
                "serverSide": true, //Feature control DataTable server side processing mode
                "order": [], //Initial no order
                "responsive": false, //Make table responsive in mobile device
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
                    "url": "{{route('user.datatable.data')}}",
                    "type": "POST",
                    "data": function (data) {
                        data.search_text = $("#form-filter #search_text").val();
                        data.role_id = $("#form-filter #role_id").val();
                        data.sort_table = $("#form-filter #sort_table").val();
                        data._token = _token;
                    }
                },
                "columnDefs": [{
                    "targets": [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14],
                    "orderable": false,
                    "className": "text-center"
                },
                ],
                "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6' <'float-right'B>>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'<'float-right'p>>>",

                "buttons": [
                    {
                        'extend': 'colvis',
                        'className': 'btn btn-secondary btn-sm text-white',
                        'text': 'Column',
                        'columns': ':gt(0)'
                    },
                    {
                        "extend": 'print',
                        'text': 'Print',
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
                        'text': 'CSV',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    },
                    {
                        "extend": 'excel',
                        'text': 'Excel',
                        'className': 'btn btn-secondary btn-sm text-white',
                        "title": "{{ $page_title }} List",
                        "filename": "{{ strtolower(str_replace(' ','-',$page_title)) }}-list",
                    },
                    {
                        "extend": 'pdf',
                        'text': 'PDF',
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
            setupTableReloadOnChange('#form-filter #role_id');
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
                let url = "{{route('user.store.or.update')}}";
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
                                if (key == 'password' || key == 'password_confirmation') {
                                    $('#store_or_update_form #' + key).parents('.form-group').append(
                                        '<small class="error text-danger">' + value + '</small>');
                                } else {
                                    $('#store_or_update_form #' + key).parent().append(
                                        '<small class="error text-danger">' + value + '</small>');
                                }


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
                $('#store_or_update_form .select').val('');
                $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                $('#store_or_update_form').find('.error').remove();
                if (id) {
                    $.ajax({
                        url: "{{route('user.edit')}}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        dataType: "JSON",
                        success: function (data) {
                            if (data.status == 'error') {
                                notification(data.status, data.message)
                            } else {
                                setShowroom(data.role_id);
                                $('#store_or_update_form #update_id').val(data.id);
                                $('#store_or_update_form #name').val(data.name);
                                $('#store_or_update_form #username').val(data.username);
                                $('#store_or_update_form #phone').val(data.phone);
                                $('#store_or_update_form #email').val(data.email);
                                $('#store_or_update_form #gender').val(data.gender);
                                $('#store_or_update_form #role_id').val(data.role_id);
                                if (data.warehouse_id) {
                                    $('#store_or_update_form #warehouse_id').val(data.warehouse_id);
                                } else {
                                    $('#store_or_update_form #warehouse_id').val('');
                                }

                                $('#store_or_update_form .selectpicker').selectpicker('refresh');
                                $('#password, #password_confirmation').parents('.form-group').removeClass('required');
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

            $(document).on('click', '.view_data', function () {
                let id = $(this).data('id');
                if (id) {
                    $.ajax({
                        url: "{{route('user.view')}}",
                        type: "POST",
                        data: {id: id, _token: _token},
                        success: function (data) {
                            $('#view_modal #view-data').html('');
                            $('#view_modal #view-data').html(data);
                            $('#view_modal').modal({
                                keyboard: false,
                                backdrop: 'true',
                            });
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
                let url = "{{ route('user.delete') }}";
                delete_data(id, url, table, row, name);
            });

            function multi_delete() {
                let ids = [];
                let rows;
                $('.select_data:checked').each(function () {
                    ids.push($(this).val());
                    rows = table.rows($('.select_data:checked').parents('tr'));
                });
                if (ids.length == 0) {
                    Swal.fire({
                        type: 'error',
                        title: 'Error',
                        text: 'Please checked at least one row of table!',
                        icon: 'warning',
                    });
                } else {
                    let url = "{{route('user.bulk.delete')}}";
                    bulk_delete(ids, url, table, rows);
                }
            }

            $(document).on('click', '.change_status', function () {
                let id = $(this).data('id');
                let name = $(this).data('name');
                let status = $(this).data('status');
                let row = table.row($(this).parent('tr'));
                let url = "{{ route('user.change.status') }}";
                change_status(id, url, table, row, name, status);
            });

            //Password Show/Hide
            $(".toggle-password").click(function () {
                $(this).toggleClass("fa-eye fa-eye-slash");
                var input = $($(this).attr("toggle"));
                if (input.attr("type") == "password") {
                    input.attr("type", "text");
                } else {
                    input.attr("type", "password");
                }
            });
        });

        function setShowroom(value) {
            (value == 2) ? $('#store_or_update_form .showroom').addClass('d-none') : $('#store_or_update_form .showroom').removeClass('d-none');
        }

        function showUserFormModal(modal_title, btn_text) {
            $('#store_or_update_form')[0].reset();
            $('#store_or_update_form #update_id').val('');
            $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
            $('#store_or_update_form').find('.error').remove();
            $('#store_or_update_form .showroom').addClass('d-none');
            $('#password, #password_confirmation').parents('.form-group').addClass('required');
            $('#store_or_update_form .selectpicker').selectpicker('refresh');
            $('#store_or_update_modal').modal({
                keyboard: false,
                backdrop: 'true',
            });
            $('#store_or_update_modal .modal-title').html('<i class="fas fa-plus-square text-white"></i> ' + modal_title);
            $('#store_or_update_modal #save-btn').text(btn_text);
        }


        /***************************************************
         * * * Begin :: Random Password Genrate Code * * *
         **************************************************/
        const randomFunc = {
            upper: getRandomUpperCase,
            lower: getRandomLowerCase,
            number: getRandomNumber,
            symbol: getRandomSymbol
        };


        function getRandomUpperCase() {
            return String.fromCharCode(Math.floor(Math.random() * 26) + 65);
        }

        function getRandomLowerCase() {
            return String.fromCharCode(Math.floor(Math.random() * 26) + 97);
        }

        function getRandomNumber() {
            return String.fromCharCode(Math.floor(Math.random() * 10) + 48);
        }

        function getRandomSymbol() {
            var symbol = "!@#$%^&*=~?";
            return symbol[Math.floor(Math.random() * symbol.length)];
        }

        //generate event
        document.getElementById("generate_password").addEventListener('click', () => {
            const length = 8;
            const hasUpper = true;
            const hasLower = true;
            const hasNumber = true;
            const hasSymbol = true;
            let password = generatePassword(hasUpper, hasLower, hasNumber, hasSymbol, length);
            document.getElementById("password").value = password;
            document.getElementById("password_confirmation").value = password;
        });

        //Generate Password Function
        function generatePassword(upper, lower, number, symbol, length) {
            let generatedPassword = "";
            const typesCount = upper + lower + number + symbol;
            const typesArr = [{upper}, {lower}, {number}, {symbol}].filter(item => Object.values(item)[0]);
            if (typesCount === 0) {
                return '';
            }
            for (let i = 0; i < length; i += typesCount) {
                typesArr.forEach(type => {
                    const funcName = Object.keys(type)[0];
                    generatedPassword += randomFunc[funcName]();
                });
            }
            const finalPassword = generatedPassword.slice(0, length);
            return finalPassword;
        }

        /***************************************************
         * * * End :: Random Password Genrate Code * * *
         **************************************************/
    </script>
@endpush
