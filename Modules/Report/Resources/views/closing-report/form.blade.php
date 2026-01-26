@extends('layouts.app')

@section('title', $page_title)

@push('styles')
    <link rel="stylesheet" href="css/jquery-ui.css"/>
    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css"/>
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
                        @if (permission('closing-add'))
                            <a href="javascript:void(0);" onclick="showFormModal('Add New Cash Platform','Save')"
                               class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-plus-circle"></i> New Cash Platform</a>
                        @endif
                    </div>
                </div>
            </div>
            <!--end::Notice-->
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form id="closing_balance_form" method="post">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="date">Closing Date</label>
                                    <input type="date" class="form-control"
                                           name="date" id="date" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="title">Title/Label</label>
                                    <input type="text" class="form-control"
                                           name="title" id="title">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="closing_amount">Closing Amount</label>
                                    <input type="text" class="form-control"
                                           name="closing_amount" id="closing_amount" readonly
                                           value="{{ $cash_data->cash_in_amount - $cash_data->cash_out_amount }}">
                                </div>
                                <div class="col-md-12">
                                    <div class="card card-custom card-border">
                                        <div class="card-header">
                                            <div class="card-title">
                                                <h3 class="card-label">Cash : <span
                                                        id="cashBalance">{{ $cash_data->cash_in_amount - $cash_data->cash_out_amount }}</span>
                                                    <span
                                                        id="cashStatus"></span></h3>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-bordered table-hover">
                                                <thead class="bg-primary">
                                                <tr>
                                                    <th class="text-center">Cash Platform</th>
                                                    <th class="text-center">Amount</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($heads as $key => $value)
                                                    <tr>
                                                        <td class="">{{ $value->label_name }}
                                                            <input type="hidden"
                                                                   class="form-control text_1 text-right"
                                                                   name="closing[{{$key}}][closing_head_id]"
                                                                   id="closing_{{$key}}_closing_head_id"
                                                                   value="{{ $value->id }}">
                                                        </td>
                                                        <td><input type="text"
                                                                   class="form-control text_1 text-right amount"
                                                                   name="closing[{{$key}}][amount]"
                                                                   id="closing_{{$key}}_amount"
                                                                   onkeyup="cashCalculator()" value="0"></td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-danger delete-data"
                                                                    data-id="{{ $value->id }}"><i
                                                                    class="fas fa-trash"></i></button>
                                                            &nbsp;
                                                            <button type="button" class="btn btn-success edit-head"
                                                                    data-id="{{ $value->id }}"
                                                                    data-name="{{ $value->label_name }}"><i
                                                                    class="fas fa-edit"></i></button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot>
                                                <tr>
                                                    <td colspan="2" class="text-right"><b>Grand Total</b></td>
                                                    <td class=""><input type="text"
                                                                        class="form-control bg-primary text-white total_money text-right"
                                                                        readonly="" id="grndtotal" name="grndtotal">
                                                    </td>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group col-md-12">
                                    <label for="note">Note / Description</label>
                                    <textarea name="note" class="form-control" id="note" cols="15" rows="5"></textarea>
                                </div>

                                <div class="form-group col-md-12 text-center pt-5">
                                    <button type="button" class="btn btn-danger btn-sm mr-3"><i
                                            class="fas fa-sync-alt"></i> Reset
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn"
                                            onclick="store_data()"><i class="fas fa-save"></i> Save
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--end: Datatable-->
                </div>
            </div>
            <!--end::Card-->
        </div>
    </div>
    @include('report::closing-report.modal')
@endsection

@push('scripts')
    <script>
        @if(Auth::user()->warehouse_id)
        closing_data('{{ Auth::user()->warehouse_id }}');
        @endif
        function closing_data(warehouse_id) {
            $.ajax({
                url: "{{ route('closing.data') }}",
                type: "POST",
                data: {_token: _token, warehouse_id: warehouse_id},
                dataType: "JSON",
                success: function (data) {
                    $('#last_day_closing').val(parseFloat(data.last_day_closing ? data.last_day_closing : 0).toFixed(2));
                    $('#cash_in').val(parseFloat(data.cash_in ? data.cash_in : 0).toFixed(2));
                    $('#cash_out').val(parseFloat(data.cash_out ? data.cash_out : 0).toFixed(2));
                    $('#balance').val(parseFloat(data.cash_in_hand ? data.cash_in_hand : 0).toFixed(2));
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function store_data() {
            let totalAmount = Number($('#grndtotal').val());
            let closing_amount = Number($('#closing_amount').val());
            if (totalAmount == closing_amount) {
                let form = document.getElementById('closing_balance_form');
                let formData = new FormData(form);
                let url = "{{route('closing.store')}}";
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
                        $('#closing_balance_form').find('.is-invalid').removeClass('is-invalid');
                        $('#closing_balance_form').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function (key, value) {
                                var key = key.split('.').join('_');
                                $('#closing_balance_form input#' + key).addClass('is-invalid');
                                $('#closing_balance_form textarea#' + key).addClass('is-invalid');
                                $('#closing_balance_form select#' + key).parent().addClass('is-invalid');
                                $('#closing_balance_form #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                window.location.replace("{{ route('closing.report') }}");
                            }
                        }

                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
            else {
                notification('error', 'Amount Do not match. Please try again');
            }
        }

        function storeHead() {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            let url = "{{route('closing.store.head')}}";
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
                            var key = key.split('.').join('_');
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            $('#store_or_update_form #' + key).parent().append(
                                '<small class="error text-danger">' + value + '</small>');
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            window.location.replace("{{ route('closing') }}");

                        }
                    }

                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        $(document).on('click', '.edit-head', function () {
            let id = $(this).data('id');
            let name = $(this).data('name');
            if (id && name) {
                $('#store_or_update_form #update_id').val(id);
                $('#store_or_update_form #label_name').val(name);

                $('#store_or_update_modal').modal({
                    keyboard: false,
                    backdrop: 'static',
                });
                $('#store_or_update_modal .modal-title').html(
                    '<i class="fas fa-edit text-white"></i> <span>Edit ' + name + '</span>');
                $('#store_or_update_modal #save-btn').text('Update');
            }
        });

        $(document).on('click', '.delete-data', function () {
            let id = $(this).data('id');
            if (id) {
                $.ajax({
                    url: "{{route('closing.head.delete')}}",
                    type: "POST",
                    data: {id: id, _token: _token},
                    dataType: "JSON",
                    success: function (data) {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            window.location.replace("{{ route('closing') }}");
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        });

        function cashCalculator() {
            let totlAmount = 0;
            $('.amount').each(function () {
                totlAmount += Number($(this).val());
            });

            $('#grndtotal').val(totlAmount);
            cashStatus();
        }

        function cashStatus() {
            let totalAmount = Number($('#grndtotal').val());
            let closing_amount = Number($('#closing_amount').val());
            let status = 'Not Matched';
            $('#cashStatus').addClass('text-danger');
            $('#cashStatus').removeClass('text-success');
            if (closing_amount == totalAmount) {
                status = 'Matched';
                $('#cashStatus').addClass('text-success');
                $('#cashStatus').removeClass('text-danger');
            }
            $('#cashStatus').text(status);
        }

    </script>
@endpush
