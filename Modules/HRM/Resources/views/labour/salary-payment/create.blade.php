@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')

    <div class="d-flex flex-column-fluid" style="background: #fff;">
        <div class="container-fluid" >
            <form id="store_or_update_form" method="post">
                @csrf
                <div class="card card-custom" style="margin-top: -24px;border-top: 4px solid #00c0ef;">
                    <div class="card-body" style="border: 2px solid #eaf2f2;">
                        <!--begin: Datatable-->
                        <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="storeData()"><i class="fas fa-money-bill"></i>{{__('file.PAYMENT SUBMIT')}}</button>
                                        <a href="{{route('labourSalaryPayment')}}"><button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('file.BACK')}}</button></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-custom gutter-b" >
                    <h3 style="padding: 19px 9px;text-align: center;">Payment Month: {{date('F-Y', strtotime($payment_month))}}</h3>
                    <div class="col-sm-12">
                        <div class="row">
                            <x-form.selectbox labelName="{{__('file.Payment Method')}}" name="payment_type" required="required" col="col-md-6" class="selectpicker">
                                <option value="1">{{ __('Bank Account') }}</option>
                                <option value="2">{{ __('bKash') }}</option>
                                <option value="3">{{ __('Nagad') }}</option>
                                <option value="4">{{ __('Hand Cash') }}</option>
                            </x-form.selectbox>
                            <div class="col-md-6 form-group required">
                                <label for="note">{{__('file.Note')}}</label>
                                <div class="input-group" >
                                    <input type="text" class="form-control"  name="note" id="note" required="required"  value="" placeholder="Payment Note">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12" style="margin-top: -2%;">
                                <table id="dataTable" class="table table-bordered table-hover">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th class="text-center" rowspan="5">{{__('SL')}}</th>
                                            <th class="text-center" rowspan="5">{{__('Labour ID')}}</th>
                                            <th class="text-center" rowspan="5">{{__('Labour Name')}}</th>
                                            <th class="text-center" colspan="7">{{__('Total Gross Salary')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Total')}}</th>
                                            <th class="text-center" colspan="4">{{__('Total Deductions')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Total')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Net Payable')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Bonus')}}</th>
                                            <th class="text-center" rowspan="3">{{__('Payment')}}</th>
                                        </tr>
                                        <tr>
                                            <th rowspan="2">Basic Wages</th>
                                            <th rowspan="2">Daily Wages</th>
                                            <th rowspan="2">Wages Per Hour</th>
                                            <th rowspan="2">Working Days</th>
                                            <th colspan="2">Overtime</th>
                                            <th rowspan="2">Others</th>
                                            <th colspan="2">Absence</th>
                                            <th rowspan="2">Wages Advance</th>
                                            <th rowspan="2">Others</th>
                                        </tr>
                                        <tr>
                                            <th>Hour</th>
                                            <th>Taka</th>
                                            <th>Hour</th>
                                            <th>Taka</th>
                                        </tr>
                                        <tr>
                                            <th>1</th>
                                            <th>2</th>
                                            <th>3</th>
                                            <th>4</th>
                                            <th>5</th>
                                            <th>6</th>
                                            <th>7</th>
                                            <th>8</th>
                                            <th>9</th>
                                            <th>10</th>
                                            <th>11</th>
                                            <th>12</th>
                                            <th>13</th>
                                            <th>14</th>
                                            <th>15</th>
                                            <th>16</th>
                                        </tr>
                                        <tr>
                                            <th></th>
                                            <th>(1/{{$total_days}})</th>
                                            <th>(2/8)</th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($salary as $key=>$row)
                                        @php
                                            $allowances         = \Modules\HRM\Entities\Allowances::where('type',2)->where('status',1)->where('allowances_month',$payment_month)->where('employee_id',$row->id)->get();
                                            $allowance          = $allowances->SUM('allowances_amount');
                                            $deducts            = \Modules\HRM\Entities\Deduction::where('type',2)->where('status',1)->where('deduction_month',$payment_month)->where('employee_id',$row->id)->get();
                                            $deduct             = $deducts->SUM('deduction_amount');
                                            $salary_advances    = \Modules\HRM\Entities\LabourAdvance::where('advance_month',$payment_month)->where('status',1)->where('labour_id',$row->id)->get();
                                            $salary_advance     = $salary_advances->SUM('advance_amount');
                                            $bonus_amount       = $bonus ? ($row->basic_salary /100) * $bonus : 0;
                                        @endphp
                                        <tr>
                                            <input type="hidden" name="labour_id[]" value="{{$row->id}}">
                                            <input type="hidden" name="basic_salary[]" value="{{$row->basic_salary}}">

                                            <input type="hidden" name="total_working_days[]" value="">
                                            <input type="hidden" name="avg_overtime_hour[]" value="">
                                            <input type="hidden" name="overtime_amount[]" value="">

                                            <input type="hidden" name="total_gross_salary[]" value="{{$row->basic_salary + $allowance}}">
                                            <input type="hidden" name="avg_absence_hour[]" value="">
                                            <input type="hidden" name="absence_amount[]" value="">

                                            <input type="hidden" name="salary_advance[]" value="{{$salary_advance}}">
                                            <input type="hidden" name="other_deduction[]" value="{{$deduct}}">
                                            <input type="hidden" name="total_deduction[]" value="{{$deduct}}">
                                            <input type="hidden" name="net_payable[]" value="{{(($row->basic_salary + $allowance) - $deduct)- $salary_advance}}">
                                            <input type="hidden" name="bonus[]" value="{{$bonus_amount}}">
                                            <input type="hidden" name="payable_salary[]" value="{{((($row->basic_salary + $allowance) - $deduct) + $bonus_amount )- $salary_advance}}">
                                            <input type="hidden" name="payment_month[]" value="{{$payment_month}}">



                                            <td>{{$key+1}}</td>
                                            <td>{{$row->name}}</td>
                                            <td>{{$row->labour_id}}</td>
                                            <td class="text-right">{{$row->basic_salary}}/-TK</td>
                                            <td class="text-right">{{ number_format($row->basic_salary / $total_days,2)}}/-TK</td>
                                            <td class="text-right">{{ number_format(($row->basic_salary / $total_days)/8,2)}}/-TK</td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td>{{$allowance}}/-TK</td>
                                            <td>{{$row->basic_salary + $allowance}}/-TK</td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right">{{$salary_advance}}/-TK</td>
                                            <td>{{$deduct}}/-TK</td>
                                            <td>{{$deduct}}/-TK</td>
                                            <td>{{(($row->basic_salary + $allowance) - $deduct)- $salary_advance}}/-TK</td>
                                            <td>{{$bonus_amount}}/-TK</td>
                                            <td style="width: 10%;">
                                                <input type="text" class="form-control payment_amount number-only text-right" name="payment_amount[]" value="{{((($row->basic_salary + $allowance) - $deduct) + $bonus_amount )- $salary_advance}}">
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end: Datatable-->
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        var table;
        let i = 1;
        function _(x){
            return document.getElementById(x);
        }

        function storeData(){
            let form     = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            let url      = "{{route('labourSalaryPayment.store.or.update')}}";
            $.ajax({
                url           : url,
                type          : "POST",
                data          : formData,
                dataType      : "JSON",
                contentType   : false,
                processData   : false,
                cache         : false,
                beforeSend    : function(){
                    $('#save-btn').addClass('spinner spinner-white spinner-right');
                },
                complete      : function(){
                    $('#save-btn').removeClass('spinner spinner-white spinner-right');
                },
                success       : function (data) {
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
                        if (data.status == 'success') { window.location.replace("{{ route('labourSalaryPayment') }}"); }
                    }
                },
                error        : function (xhr, ajaxOption, thrownError) { console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
            });
        }
    </script>
{{--    Only INPUT Number and floting Number --}}
    <script>
        $(function(){
            $('.number-only').keypress(function(e) {
                if(isNaN(this.value+""+String.fromCharCode(e.charCode))) return false;
            })
                .on("cut copy paste",function(e){
                    e.preventDefault();
                });
        });
    </script>
@endpush
