@extends('layouts.app')

@section('title', $page_title)
@section('content')

    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <!--begin::Notice-->
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        @csrf
                        <div class="row">
                            <div class="col-md-6" id="">
                                <input type="hidden" name="update_id" id="update_id" class="form-control" value="{{ $data->id }}">

                                <input type="hidden" name="employee_id" id="employee_id" class="form-control" value="{{ $data->employee_id }}">

                                <label class="col-from-label">{{ 'Employee' }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" value="{{ $data->employee->name }}" readonly>
                            </div>
                            <div class="col-md-6" id="">
                                <label class="col-from-label">{{ 'Due' }} <span class="text-danger">*</span></label>

                                <input type="text" name="due" id="due" class="form-control" value="{{ $due }}" readonly>
                            </div>

                            <div class="col-md-6" id="">
                                <label class="col-from-label">{{ 'Paid' }} <span class="text-danger">*</span></label>

                                <input type="text" name="paid_amount" id="paid_amount" class="form-control" value="{{ $data->paid_amount }}">
                            </div>

                            <div class="col-md-6" id="">
                                <label class="col-from-label">{{ 'Payment Method' }} <span class="text-danger">*</span></label>

                                <Select class="form-control selectpicker" name="payment_method" id="payment_method" onchange="account_list(this.value)">
                                    <option value="">Select Method</option>
                                    @foreach (SALE_PAYMENT_METHOD as $lkey => $value)
                                        <option value="{{ $lkey }}" @if ($lkey == $data->payment_method) selected="selected" @endif>{{ $value }}</option>
                                    @endforeach
                                </Select>
                            </div>
                            <div class="col-md-6" id="">
                                <label class="col-from-label">{{ 'Account' }} <span class="text-danger">*</span></label>

                                <Select class="form-control selectpicker fcs" name="account_id" id="account_id">
                                </Select>
                            </div>
                            <div class="col-md-6" id="">
                                <button type="button" class="btn btn-primary btn-sm mt-7" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var payment_method = "{{ $data->payment_method }}";
        var account_id = "{{ $data->account_id }}";

        $(document).ready(function() {
            $('#payment_method').trigger('change');
            setTimeout(function() {
                $('#account_id').val(account_id);
                $('#account_id').selectpicker('refresh');
            }, 5000);
        });

        function account_list(payment_method) {

            $.ajax({
                url: "{{ route('account.list') }}",
                type: "POST",
                data: {
                    payment_method: payment_method,
                    _token: _token
                },
                success: function(data) {
                    $('#account_id').empty().html(data);
                    $('#account_id').selectpicker('refresh');
                },
                error: function(xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function store_data() {

            let form = document.getElementById('form-filter');
            let formData = new FormData(form);
            let url = "{{ route('employee.salary.payment.update') }}";
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "JSON",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function() {
                    $('#save-btn').addClass('spinner spinner-white spinner-right');
                },
                complete: function() {
                    $('#save-btn').removeClass('spinner spinner-white spinner-right');
                },
                success: function(data) {
                    $('#form-filter').find('.is-invalid').removeClass('is-invalid');
                    $('#form-filter').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function(key, value) {
                            var key = key.split('.').join('_');
                            $('#form-filter input#' + key).addClass('is-invalid');
                            $('#form-filter textarea#' + key).addClass('is-invalid');
                            $('#form-filter select#' + key).parent().addClass('is-invalid');
                            $('#form-filter #' + key).parent().append(
                                '<small class="error text-danger">' + value + '</small>');
                        });
                        $('html, body').animate({
                            scrollTop: ($('.is-invalid').offset().top - 300)
                        }, 1000);
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            window.location.replace("{{ route('employee.salary.payment') }}");
                        }
                    }
                },
                error: function(xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    </script>
@endpush
