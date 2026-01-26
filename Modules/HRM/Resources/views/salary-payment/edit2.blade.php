@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')

    <div class="d-flex flex-column-fluid" style="background: #fff;">
        <div class="container-fluid">
            <form id="store_or_update_form" method="post">
                @csrf
                <div class="card card-custom" style="margin-top: -24px;border-top: 4px solid #00c0ef;">
                    <div class="card-body" style="border: 2px solid #eaf2f2;">
                        <!--begin: Datatable-->
                        <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="modal-footer">
                                        <a href="{{ route('employee.salary.payment') }}"><button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{ __('file.BACK') }}</button></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-custom gutter-b">
                    <div class="col-sm-12">
                        <div class="row">
                            <input type="text" name="salary[{{ 0 }}][employee_id]" id="salary_{{ 0 }}_employee_id" class="form-control" value="{{ $data->employee_id }}">

                            <input type="text" name="salary[{{ 0 }}][due]" id="salary_{{ 0 }}_due" class="form-control" value="{{ 0 }}" readonly>

                            <input type="text" name="salary[{{ 0 }}][paid_amount]" id="salary_{{ 0 }}_paid_amount" class="form-control" value="0">

                            <Select class="from-control selectpicker" name="salary[{{ 0 }}][payment_method]" id="salary_{{ 0 }}_payment_method"
                                onchange="account_list(this.value, {{ 0 }})">
                                <option value="">Select Method</option>
                                @foreach (SALE_PAYMENT_METHOD as $lkey => $value)
                                    <option value="{{ $lkey }}">{{ $value }}</option>
                                @endforeach
                            </Select>

                            <Select class="from-control selectpicker fcs" name="salary[{{ 0 }}][account_id]" id="salary_{{ 0 }}_account_id">
                            </Select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        var table;
        let i = 1;

        function _(x) {
            return document.getElementById(x);
        }

        function storeData() {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            let url = "{{ route('salaryPayment.update') }}";
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
                    $('#purchase_store_form').find('.is-invalid').removeClass('is-invalid');
                    $('#purchase_store_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function(key, value) {
                            var key = key.split('.').join('_');
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            $('#store_or_update_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            window.location.replace("{{ route('salaryPayment') }}");
                        }
                    }
                },
                error: function(xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    </script>
    {{--    Only INPUT Number and floting Number --}}
    <script>
        $(function() {
            $('.number-only').keypress(function(e) {
                    if (isNaN(this.value + "" + String.fromCharCode(e.charCode))) return false;
                })
                .on("cut copy paste", function(e) {
                    e.preventDefault();
                });
        });
    </script>
@endpush
