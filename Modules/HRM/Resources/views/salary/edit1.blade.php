@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')
    <div class="d-flex flex-column-fluid" style="background: #fff;">
        <div class="container-fluid" >
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
                                                    <input type="hidden" class="form-control"  name="update_id" id="update_id"  readonly value="{{$salary->id}}" >
                                                    <div class="col-md-6 form-group required">
                                                        <label for="title">{{__('Employee Name')}}</label>
                                                        <div class="input-group required" >
                                                            <input type="hidden" class="form-control"  name="employee_id" id="employee_id" required="required" readonly value="{{$salary->employee->id}}">
                                                            <input type="text" class="form-control"   readonly value="{{$salary->employee->name}}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 form-group required">
                                                        <label for="title">{{__('Employee ID')}}</label>
                                                        <div class="input-group" >
                                                            <input type="text" class="form-control"  readonly value="{{$salary->employee->employee_id}}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="row">
                                                    <x-form.selectbox labelName="{{__('file.Employee Type')}}" name="employee_type" required="required" col="col-md-6" class="selectpicker">
                                                        <option value="1"{{$salary->employee_type == 1 ? 'selected' : ''}}>{{ __('Provision') }}</option>
                                                        <option value="2"{{$salary->employee_type == 2 ? 'selected' : ''}}>{{ __('Permanent') }}</option>
                                                        <option value="3"{{$salary->employee_type == 3 ? 'selected' : ''}}>{{ __('Full Time') }}</option>
                                                        <option value="4"{{$salary->employee_type == 4 ? 'selected' : ''}}>{{ __('Part Time') }}</option>
                                                    </x-form.selectbox>
                                                    <div class="col-md-6 form-group required">
                                                        <label for="salary">{{__('file.Basic Salary')}}</label>
                                                        <div class="input-group" >
                                                            <input type="text" class="form-control number-only"  name="basic_salary" id="basic_salary" required="required" value="{{$salary->basic_salary}}" placeholder="0.00">
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
                                                            <div class="col-md-6 form-group ">
                                                                <label for="title">{{__('file.House Rent Allowance')}}</label>
                                                                <div class="input-group" >
                                                                    <input type=text step=any class="form-control number-only"   name="house_rent_allowance" id="house_rent_allowance" placeholder="0.00" value="{{$salary->house_rent_allowance}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 form-group ">
                                                                <label for="title">{{__('Medical Allowance')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="medical_allowance" id="medical_allowance" placeholder="0.00" value="{{$salary->medical_allowance}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 form-group ">
                                                                <label for="title">{{__('Transport Allowance')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="transport_allowance" id="transport_allowance" placeholder="0.00" value="{{$salary->transport_allowance}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 form-group ">
                                                                <label for="title">{{__('Mobile Allowance')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="mobile_allowance" id="mobile_allowance" placeholder="0.00" value="{{$salary->mobile_allowance}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 form-group ">
                                                                <label for="title">{{__('Other Allowance')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="other_allowance" id="other_allowance" placeholder="0.00" value="{{$salary->other_allowance}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6 form-group ">
                                                                <label for="title">{{__('Provident Fund Contribution')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="provident_fund_contribution" id="provident_fund_contribution" placeholder="0.00" value="{{$salary->provident_fund_contribution}}">
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
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('Tax Deduction')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="tax_deduction" id="tax_deduction" placeholder="0.00" value="{{$salary->tax_deduction}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('Provident Fund Deduction')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="provident_fund_deduction" id="provident_fund_deduction" placeholder="0.00" value="{{$salary->provident_fund_deduction}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('Other Deduction')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="other_deduction" id="other_deduction" placeholder="0.00" value="{{$salary->other_deduction}}">
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
                        <div class="card card-custom gutter-b" style="border-top: 4px solid #3c8dbc;margin-top:2px;">
                            <h3 style="padding: 2px 9px;">Provident Fund</h3>
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
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('Total Provident Fund')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control number-only"  name="total_provident_fund" id="total_provident_fund" readonly placeholder="0.00" value="{{$salary->total_provident_fund}}">
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
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('file.Gross Salary')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control" readonly name="gross_salary" id="gross_salary" placeholder="0.00" value="{{$salary->gross_salary}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('file.Total Deduction')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control" readonly name="total_deduction" id="total_deduction" placeholder="0.00" value="{{$salary->total_deduction}}">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-12 form-group ">
                                                                <label for="title">{{__('file.Net Payable')}}</label>
                                                                <div class="input-group" >
                                                                    <input type="text" class="form-control" readonly  name="net_salary" id="net_salary" placeholder="0.00" value="{{$salary->net_salary}}">
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
                </div>

                <div class="card card-custom" style="margin-top: -24px;">
                    <div class="card-body" style="border: 2px solid #eaf2f2;">
                        <!--begin: Datatable-->
                        <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger btn-sm mr-3" onclick="window.location.replace('{{ route("salary") }}');"><i class="fas fa-sync-alt"></i> Close</button>
                                        <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="storeData()"><i class="fas fa-save"></i>{{__('file.Save')}}</button>
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
        function _(x){
            return document.getElementById(x);
        }

        function storeData(){
            let form          = document.getElementById('store_or_update_form');
            let formData      = new FormData(form);
            let url           = "{{route('salary.update')}}";
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
                        if (data.status == 'success') { window.location.replace("{{ route('salary') }}"); }
                    }
                },
                error        : function (xhr, ajaxOption, thrownError) { console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
            });
        }
    </script>
    <script>
        var table;
        $(document).ready(function(){
            calculation();
        });

        //For Calculation
        $(document).on("keyup", "#store_or_update_form", function() {
            calculation();
        });
        function calculation() {
            var sum = 0;
            var basic_salary                = $("#basic_salary").val();
            var house_rent_allowance        = $("#house_rent_allowance").val();
            var medical_allowance           = $("#medical_allowance").val();
            var transport_allowance         = $("#transport_allowance").val();
            var mobile_allowance            = $("#mobile_allowance").val();
            var provident_fund_contribution = $("#provident_fund_contribution").val();
            var other_allowance             = $("#other_allowance").val();
            var tax_deduction               = $("#tax_deduction").val();
            var provident_fund_deduction    = $("#provident_fund_deduction").val();
            var other_deduction             = $("#other_deduction").val();

            var gross_salary    = (+basic_salary + +house_rent_allowance + +medical_allowance + +transport_allowance + +mobile_allowance + +other_allowance);

            var total_deduction = (+tax_deduction + +provident_fund_deduction + +other_deduction);

            $("#total_provident_fund").val(+provident_fund_contribution + +provident_fund_deduction);

            $("#gross_salary").val(gross_salary);
            $("#total_deduction").val(total_deduction);
            $("#net_salary").val(+gross_salary - +total_deduction);
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
