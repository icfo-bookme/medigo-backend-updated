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
                    <h3 style="padding: 19px 9px;text-align: center;">Payment Month: {{date('F-Y', strtotime($salary_pay->payment_month))}}</h3>
                    <div class="col-sm-12">
                        <div class="row">
                            <x-form.selectbox labelName="{{__('file.Payment Method')}}" name="payment_type" required="required" col="col-md-6" class="selectpicker">
                                <option value="1"{{$salary_pay->payment_type == 1 ? 'selected' : ''}}>{{ __('Bank Account') }}</option>
                                <option value="2"{{$salary_pay->payment_type == 2 ? 'selected' : ''}}>{{ __('bKash') }}</option>
                                <option value="3"{{$salary_pay->payment_type == 3 ? 'selected' : ''}}>{{ __('Nagad') }}</option>
                                <option value="4"{{$salary_pay->payment_type == 4 ? 'selected' : ''}}>{{ __('Hand Cash') }}</option>
                            </x-form.selectbox>
                            <div class="col-md-6 form-group required">
                                <label for="note">{{__('file.Note')}}</label>
                                <div class="input-group" >
                                    <input type="text" class="form-control"  name="note" id="note" required="required"  value="{{$salary_pay->note}}" placeholder="Payment Note">
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
                                        <th>{{__('file.Employee Name')}}</th>
                                        <th>{{__('file.Employee ID')}}</th>
                                        <th>{{__('file.Designation')}}</th>
{{--                                        <th>{{__('file.Employee Type')}}</th>--}}
                                        <th>{{__('file.Total Allowance')}}</th>
                                        <th>{{__('file.Total Deduction')}}</th>
                                        <th>{{__('file.Gross Salary')}}</th>
                                        <th>{{__('file.Net salary')}}</th>
                                        <th>{{__('file.Payment')}}</th>
                                        <th>{{__('file.Payment Month')}}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <input type="hidden" class="form-control"  name="update_id" id="update_id" readonly value="{{$salary_pay->id}}" >
                                        <td>{{$salary_pay->employee ? $salary_pay->employee->name : ''}}</td>
                                        <td>{{$salary_pay->employee ? $salary_pay->employee->employee_id : ''}}</td>
                                        <td>{{$salary_pay->employee ? $salary_pay->employee->designation->name : ''}}</td>
{{--                                        <td>{{EMPLOYE_TYPE_STATUS[$salary_pay->employee_type]}}</td>--}}
                                        <td>
                                            <a href="{{route('salaryPayment.salary.allowance',[$salary_pay->employee_id,$salary_pay->payment_month])}}" target="_blank" title="Click And View Allowance Details">
                                                  <span class="label label-success label-pill label-inline" style="min-width:100% !important;font-size: 12px;">
                                                      {{$salary_pay->total_allowance}}/-TK
                                                  </span>
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{route('salaryPayment.salary.deduction',[$salary_pay->employee_id,$salary_pay->payment_month])}}" target="_blank" title="Click And View Deduction Details">
                                                  <span class="label label-danger label-pill label-inline" style="min-width:100% !important;font-size: 12px;">
                                                   {{$salary_pay->total_deduction}}/-TK
                                                  </span>
                                            </a>
                                        </td>
                                        <td>{{$salary_pay->net_salary + $salary_pay->total_allowance}}</td>
                                        <td>{{$salary_pay->net_salary}}</td>
                                        <td style="width: 12%;">
                                            <input type="text" class="form-control payment_amount number-only" name="payment_amount" value="{{$salary_pay->payment_amount}}">
                                        </td>
                                        <td>{{date('F-Y', strtotime($salary_pay->payment_month))}}</td>
                                    </tr>
{{--                                    @foreach($salary as $key=> $row)--}}
{{--                                        @php--}}
{{--                                            $allowances = \Modules\HRM\Entities\Allowances::where('allowances_month',$payment_month)->where('employee_id',$row->employee_id)->get();--}}
{{--                                            $allowance  = $allowances->SUM('allowances_amount');--}}
{{--                                            $deducts    = \Modules\HRM\Entities\Deduction::where('deduction_month',$payment_month)->where('employee_id',$row->employee_id)->get();--}}
{{--                                            $deduct     = $deducts->SUM('deduction_amount');--}}
{{--                                        @endphp--}}

{{--                                    @endforeach--}}
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
            let url      = "{{route('salaryPayment.update')}}";
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
