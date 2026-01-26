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
                                        <a href="{{route('salaryPayment')}}"><button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('file.BACK')}}</button></a>
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
                                        <th class="text-center">{{__('file.SL')}}</th>
                                        <th class="text-center">{{__('file.Employee Name')}}</th>
                                        <th class="text-center">{{__('file.Employee ID')}}</th>
                                        <th class="text-center">{{__('file.Designation')}}</th>
                                        <th class="text-center">{{__('file.Employee Type')}}</th>
                                        <th class="text-center">{{__('file.Basic Salary')}}</th>
                                        <th class="text-center">{{__('file.Total Allowance')}}</th>
                                        <th class="text-center">{{__('file.Gross Salary')}}</th>
                                        <th class="text-center">{{__('file.Salary Advance')}}</th>
                                        <th class="text-center">{{__('file.Total Deduction')}}</th>
                                        <th class="text-center">{{__('file.Bonus')}}</th>
                                        <th class="text-center">{{__('file.Net Payable')}}</th>
                                        <th class="text-center">{{__('file.Payment')}}</th>
                                        <th class="text-center">{{__('file.Payment Month')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($salary as $key=> $row)
                                        @php
                                            $allowances         = \Modules\HRM\Entities\Allowances::where('type',1)->where('status',1)->where('allowances_month',$payment_month)->where('employee_id',$row->employee_id)->get();
                                            $allowance          = $allowances->SUM('allowances_amount');
                                            $deducts            = \Modules\HRM\Entities\Deduction::where('type',1)->where('status',1)->where('deduction_month',$payment_month)->where('employee_id',$row->employee_id)->get();
                                            $deduct             = $deducts->SUM('deduction_amount');
                                            $salary_advances    = \Modules\HRM\Entities\Advance::where('advance_month',$payment_month)->where('status',1)->where('employee_id',$row->employee_id)->get();
                                            $salary_advance     = $salary_advances->SUM('advance_amount');
                                        @endphp
                                      <tr>
                                          <input type="hidden" name="basic_salary[]" value="{{$row->basic_salary}}">
                                          <input type="hidden" name="employee_id[]" value="{{$row->employee ? $row->employee->id : ''}}">
                                          <input type="hidden" name="provident_fund[]" value="{{$row->provident_fund_deduction }}">
                                          <input type="hidden" name="total_allowance[]" value="{{$row->house_rent_allowance + $row->medical_allowance + $row->transport_allowance + $row->mobile_allowance + $row->other_allowance + $allowance}}">
                                          <input type="hidden" name="total_deduction[]" value="{{$row->tax_deduction + $row->provident_fund_deduction + $row->other_deduction + $deduct}}">
                                          <input type="hidden" name="gross_salary[]" value="{{$row->gross_salary + $allowance}}">
                                          <input type="hidden" name="net_salary[]" value="{{((($row->gross_salary + $allowance) - ($row->total_deduction + $deduct)) + ($row->basic_salary /100) * $bonus) - $salary_advance}}">
                                          <input type="hidden" name="payment_month[]" value="{{$payment_month}}">
                                          <input type="hidden" name="bonus[]" value="{{($row->basic_salary /100) * $bonus}}">
                                          <input type="hidden" name="salary_advance[]" value="{{$salary_advance}}">

                                          <td>{{$key+1}}</td>
                                          <td>{{$row->employee ? $row->employee->name : ''}}</td>
                                          <td>{{$row->employee ? $row->employee->employee_id : ''}}</td>
                                          <td>{{$row->employee ? $row->employee->designation->name : ''}}</td>
                                          <td>{{EMPLOYE_TYPE_STATUS[$row->employee_type]}}</td>
                                          <td class="text-right">{{$row->basic_salary}}/-TK</td>
                                          <td>
                                              <a href="{{route('salaryPayment.salary.allowance',[$row->employee_id,$payment_month])}}" target="_blank" title="Click And View Allowance Details">
                                                  <span class="label label-success label-pill label-inline" style="min-width:100% !important;font-size: 12px;">
                                                      {{$row->house_rent_allowance + $row->medical_allowance + $row->transport_allowance + $row->mobile_allowance + $row->other_allowance + $allowance}}/-TK
                                                  </span>
                                              </a>
                                          </td>

                                          <td class="text-right">{{$row->gross_salary + $allowance}}/-TK</td>
                                          <td class="text-right">{{$salary_advance}}/-TK</td>
                                          <td>
                                              <a href="{{route('salaryPayment.salary.deduction',[$row->employee_id,$payment_month])}}" target="_blank" title="Click And View Deduction Details">
                                                  <span class="label label-danger label-pill label-inline" style="min-width:100% !important;font-size: 12px;">
                                                        {{$row->tax_deduction + $row->provident_fund_deduction + $row->other_deduction + $deduct}}/-TK
                                                  </span>
                                              </a>
                                          </td>
                                          <td class="text-right">{{$bonus ? ($row->basic_salary /100) * $bonus : 0}}/-TK</td>
                                          <td class="text-right">{{((($row->gross_salary + $allowance) - ($row->total_deduction + $deduct)) + ($row->basic_salary /100) * $bonus) - $salary_advance}}/-TK</td>
                                          <td style="width: 12%;">
                                              <input type="text" class="form-control payment_amount number-only text-right" name="payment_amount[]" value="{{((($row->gross_salary + $allowance) - ($row->total_deduction + $deduct)) + ($row->basic_salary /100) * $bonus) - $salary_advance}}">
                                          </td>
                                          <td>{{date('F-Y', strtotime($payment_month))}}</td>
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
            let url      = "{{route('salaryPayment.store.or.update')}}";
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
                        if (data.status == 'success') { window.location.replace("{{ route('salaryPayment') }}"); }
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
