@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')

    <div class="d-flex flex-column-fluid" style="background: #fff;">
        <div class="container-fluid">
            <form id="store_or_update_form" method="post">
                @csrf
                <div class="card card-custom gutter-b" style="border-top: 4px solid #00c0ef;margin-top: -18px;">
                    <h3 style="padding: 2px 9px;">Salary Details</h3>
                </div>
                <div class="card card-custom" style="margin-top: -24px;">
                    <div class="card-body" style="border: 2px solid #eaf2f2;">
                        <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="row">
                                                    <div class="col-md-6 form-group required">
                                                        <input type="hidden" class="form-control" name="update_id"
                                                               id="update_id" readonly>
                                                        <input type="hidden" class="form-control" name="employee_id"
                                                               id="employee_id" readonly value="{{$employee->id}}">
                                                        <input type="hidden" class="form-control" name="joining_month"
                                                               id="joining_month" readonly
                                                               value="{{$employee->joining_month}}">
                                                        <label for="title">{{__('Employee Name')}}</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" readonly
                                                                   value="{{$employee->name}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group required">
                                                        <label for="title">{{__('Employee ID')}}</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control" readonly
                                                                   value="{{$employee->employee_id}}">
                                                        </div>
                                                    </div>
                                                    <x-form.selectbox labelName="{{__('file.Employee Type')}}"
                                                                      name="employee_type" required="required"
                                                                      col="col-md-6" class="selectpicker">
                                                        <option value="1">{{ __('Provision') }}</option>
                                                        <option value="2">{{ __('Permanent') }}</option>
                                                        <option value="3">{{ __('Full Time') }}</option>
                                                        <option value="4">{{ __('Part Time') }}</option>
                                                    </x-form.selectbox>
                                                    <x-form.selectbox labelName="{{__('file.Shift')}}" name="shift_id"
                                                                      required="required" col="col-md-6"
                                                                      class="selectpicker">
                                                        @foreach($shifts as $shift)
                                                            <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                                                        @endforeach
                                                    </x-form.selectbox>
                                                    <div class="col-md-6 form-group required">
                                                        <label for="salary">{{__('file.Basic Salary')}}</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control number-only salary"
                                                                   name="basic_salary" id="basic_salary"
                                                                   required="required" value="" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group required">
                                                        <label
                                                            for="overtime_rate">{{__('file.Overtime Rate(Hourly)')}}</label>
                                                        <div class="input-group">
                                                            <input type="text" class="form-control number-only"
                                                                   name="overtime_rate" id="overtime_rate"
                                                                   required="required" value="" placeholder="0.00">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group required">
                                                        <label>Weekly Holidays</label>
                                                        <br>
                                                        <label for="day7">
                                                            <input type="checkbox" id="day7" name="weekly_holidays[]"
                                                                   checked value="Friday"> Friday
                                                        </label> &nbsp; &nbsp;
                                                        <label for="day1">
                                                            <input type="checkbox" id="day1" name="weekly_holidays[]"
                                                                   value="Saturday"> Saturday
                                                        </label> &nbsp; &nbsp;
                                                        <label for="day2">
                                                            <input type="checkbox" id="day2" name="weekly_holidays[]"
                                                                   value="Sunday"> Sunday
                                                        </label> &nbsp; &nbsp;
                                                        <label for="day3">
                                                            <input type="checkbox" id="day3" name="weekly_holidays[]"
                                                                   value="Monday"> Monday
                                                        </label> &nbsp; &nbsp;
                                                        <label for="day4">
                                                            <input type="checkbox" id="day4" name="weekly_holidays[]"
                                                                   value="Tuesday"> Tuesday
                                                        </label> &nbsp; &nbsp;
                                                        <label for="day5">
                                                            <input type="checkbox" id="day5" name="weekly_holidays[]"
                                                                   value="Wednesday"> Wednesday
                                                        </label> &nbsp; &nbsp;
                                                        <label for="day6">
                                                            <input type="checkbox" id="day6" name="weekly_holidays[]"
                                                                   value="Thursday"> Thursday
                                                        </label> &nbsp; &nbsp;

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="card card-custom gutter-b" style="border-top: 4px solid #00a65a;margin-top:2px;">
                            <h3 style="padding: 2px 9px;">Allowances</h3>
                        </div>
                        <div class="card card-custom" style="margin-top: -24px;">
                            <div class="card-body" style="border: 2px solid #eaf2f2;">
                                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="row">
                                                            @foreach($allowances as $key => $allowance)
                                                                <div class="col-md-6 form-group required">
                                                                    <label
                                                                        for="allowance_{{ $key }}_head_value">{{ $allowance->head }}</label>
                                                                    <div class="input-group">
                                                                        <input type=hidden step=any class="form-control"
                                                                               name="allowance[{{ $key }}][head_id]"
                                                                               id="allowance_{{ $key }}_head_id"
                                                                               value="{{ $allowance->id }}">
                                                                        <input type=text step=any
                                                                               class="form-control number-only allowance salary"
                                                                               name="allowance[{{ $key }}][head_value]"
                                                                               id="allowance_{{ $key }}_head_value"
                                                                               placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="card card-custom gutter-b" style="border-top: 4px solid #f39c12;margin-top:2px;">
                            <h3 style="padding: 2px 9px;">Deductions</h3>
                        </div>
                        <div class="card card-custom" style="margin-top: -24px;">
                            <div class="card-body" style="border: 2px solid #eaf2f2;">
                                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="row">
                                                            @foreach($deductions as  $key => $deduction)
                                                                <div class="col-md-6 form-group ">
                                                                    <label
                                                                        for="deductions_{{ $key }}_head_value">{{ $deduction->head }}</label>
                                                                    <div class="input-group">
                                                                        <input type="hidden"
                                                                               class="form-control number-only"
                                                                               name="deductions[{{ $key }}][head_id]"
                                                                               id="deductions_{{ $key }}_head_id"
                                                                               value="{{ $deduction->id }}">
                                                                        <input type="text"
                                                                               class="form-control number-only deduction salary"
                                                                               name="deductions[{{ $key }}][head_value]"
                                                                               id="deductions_{{ $key }}_head_value"
                                                                               placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-sm-6" style="margin-top: 0px;">
                        <div class="card card-custom gutter-b" style="border-top: 4px solid #dd4b39;margin-top:2px;">
                            <h3 style="padding: 2px 9px;">Total Salary Details</h3>
                        </div>
                        <div class="card card-custom" style="margin-top: -24px;">
                            <div class="card-body" style="border: 2px solid #eaf2f2;">
                                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="row">
                                                            <div class="col-md-12 form-group required">
                                                                <label for="title">{{__('file.Gross Salary')}}</label>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" readonly
                                                                           name="gross_salary" id="gross_salary"
                                                                           placeholder="0.00">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group required">
                                                                <label
                                                                    for="title">{{__('file.Total Deduction')}}</label>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" readonly
                                                                           name="total_deduction" id="total_deduction"
                                                                           placeholder="0.00">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group required">
                                                                <label for="title">{{__('file.Net Payable')}}</label>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" readonly
                                                                           name="net_salary" id="net_salary"
                                                                           placeholder="0.00">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group required">
                                                                <label for="paid_leave">{{__('file.Total Paid Leave')}}</label>
                                                                <div class="input-group">
                                                                    <input type="text" class="form-control" readonly
                                                                           name="paid_leave" id="paid_leave"
                                                                           placeholder="0.00">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6" style="margin-top: 0px;">
                        <div class="card card-custom gutter-b" style="border-top: 4px solid #00c0ef;margin-top:2px;">
                            <h3 style="padding: 2px 9px;">Assigned Leaves/Paid Leave Details</h3>
                        </div>
                        <div class="card card-custom" style="margin-top: -24px;">
                            <div class="card-body" style="border: 2px solid #eaf2f2;">
                                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="row">
                                                            @foreach($leaveCategories as $key => $category)
                                                                <div class="col-md-6 form-group required">
                                                                    <label
                                                                        for="leave_{{ $key }}_number_of_days">{{ $category->leave_category }}</label>
                                                                    <div class="input-group">
                                                                        <input type="hidden" class="form-control" name="leave[{{ $key }}][category_id]"
                                                                               id="leave_{{ $key }}_category_id" value="{{ $category->id }}" placeholder="0.00">
                                                                        <input type="text" class="form-control number-only leave-count" name="leave[{{ $key }}][number_of_days]"
                                                                               id="leave_{{ $key }}_number_of_days" value="0" placeholder="0.00">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-custom" style="margin-top: -24px;">
                    <div class="card-body" style="border: 2px solid #eaf2f2;">
                        <!--begin: Datatable-->
                        <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger btn-sm mr-3"
                                                onclick="window.location.replace('{{ route("salary") }}');"><i
                                                class="fas fa-sync-alt"></i> Close
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn"
                                                onclick="storeData()"><i class="fas fa-save"></i>{{__('file.Save')}}
                                        </button>
                                    </div>
                                </div>
                            </div>
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
            let url = "{{route('salary.store.or.update')}}";
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
                    $('#purchase_store_form').find('.is-invalid').removeClass('is-invalid');
                    $('#purchase_store_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            $('#store_or_update_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            window.location.replace("{{ route('salary') }}");
                        }
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    </script>
    <script>
        var table;
        $(document).ready(function () {
            calculation();
        });

        //For Calculation
        $(document).on("keyup", ".salary", function () {
            calculation();
        });
        //for leave
        $(document).on("keyup", ".leave-count", function () {
            calculation();
        });

        function calculation() {
            var sum = 0;
            var basic_salary = Number($("#basic_salary").val());
            var allowance = 0;
            var deduction = 0;
            var totalLeaves = 0;
            $('.allowance').each(function () {
                allowance += Number($(this).val());
            });

            $('.deduction').each(function () {
                deduction += Number($(this).val());
            });
            $('.leave-count').each(function () {
                totalLeaves += Number($(this).val());
            });
            var gross_salary = basic_salary + allowance;

            var total_deduction = deduction;

            $("#paid_leave").val(totalLeaves);
            $("#gross_salary").val(gross_salary);
            $("#total_deduction").val(total_deduction);
            $("#net_salary").val(+gross_salary - +total_deduction);
        }
    </script>
    {{--    Only INPUT Number and floting Number --}}
    <script>
        $(function () {
            $('.number-only').keypress(function (e) {
                if (isNaN(this.value + "" + String.fromCharCode(e.charCode))) return false;
            })
                .on("cut copy paste", function (e) {
                    e.preventDefault();
                });
        });
    </script>
@endpush
