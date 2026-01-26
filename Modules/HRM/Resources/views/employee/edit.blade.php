@extends('layouts.app')

@section('title', $page_title)
@push('styles')
    <style>
        .card {
            border-radius: 10px;
            box-shadow: 0 5px 10px 0 rgba(0, 0, 0, 0.3);
            background-color: #ffffff;
            padding: 10px 30px 40px;
        }

        .drop_box {
            margin: 10px 0;
            padding: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border: 3px dotted #a3a3a3;
            border-radius: 5px;
        }

        .drop_box h4 {
            font-size: 16px;
            font-weight: 400;
            color: #2e2e2e;
        }

        .drop_box p {
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #a3a3a3;
        }

        .btn {
            text-decoration: none;
            background-color: #005af0;
            color: #ffffff;
            padding: 10px 20px;
            transition: 0.3s;
        }

        .btn:hover {
            text-decoration: none;
            background-color: #ffffff;
            color: #005af0;
        }

        .form input {
            margin: 10px 0;
            width: 100%;
            background-color: #e2e2e2;
            padding: 12px 20px;
            border-radius: 4px;
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
                        @if (permission('employee-add'))
                            <a href="{{route('employee')}}" class="btn btn-primary btn-sm font-weight-bolder">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        @endif
                        <!--end::Button-->
                    </div>
                </div>
            </div>
            <!--end::Notice-->
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form id="store_or_update_form" method="post">
                            <div class="row">
                                <div class="col-sm-12">
                                    @csrf
                                    <div class="card card-custom gutter-b"
                                         style="border-top: 4px solid #f39c12;margin-top:2px;">
                                        <h3 style="padding: 2px 9px;">Personal & Academic Information</h3>
                                    </div>
                                    <div class="card card-custom" style="margin-top: -24px;">
                                        <div class="card-body" style="border: 2px solid #eaf2f2;">
                                            <div id="kt_datatable_wrapper"
                                                 class="dataTables_wrapper dt-bootstrap4 no-footer">
                                                <div class="row">
                                                    <div class="col-sm-12">
                                                        <div class="modal-body">
                                                            <div class="row">
                                                                <div class="col-sm-12">
                                                                    <div class="row">
                                                                        <div class="col-sm-6">
                                                                            <input type="hidden" name="update_id"
                                                                                   id="update_id"
                                                                                   value="{{$employee->id}}"/>
                                                                            <input type="hidden" name="old_resume"
                                                                                   id="old_resume"
                                                                                   value="{{$employee->resume}}"/>
                                                                            <x-form.textbox
                                                                                labelName="{{__('file.Name')}}"
                                                                                name="name" required="required"
                                                                                col="col-md-12" placeholder="Enter name"
                                                                                value="{{$employee->name}}"/>

                                                                            <x-form.selectbox
                                                                                labelName="{{__('file.Gender')}}"
                                                                                name="gender" required="required"
                                                                                col="col-md-12" class="selectpicker">
                                                                                <option
                                                                                    value="1"{{$employee->gender == 1 ? 'selected' : ''}}>{{ __('Male') }}</option>
                                                                                <option
                                                                                    value="2"{{$employee->gender == 2 ? 'selected' : ''}}>{{ __('Female') }}</option>
                                                                            </x-form.selectbox>
                                                                            <div class="form-group col-md-12 required">
                                                                                <label
                                                                                    for="sale_date">{{__('file.Date of Birth')}}</label>
                                                                                <input type="date"
                                                                                       class="form-control date"
                                                                                       id="date_of_birth"
                                                                                       name="date_of_birth"
                                                                                       value="{{$employee->date_of_birth}}"/>
                                                                            </div>
                                                                            <x-form.selectbox
                                                                                labelName="{{__('file.Marital Status')}}"
                                                                                name="marital_status"
                                                                                required="required" col="col-md-12"
                                                                                class="selectpicker">
                                                                                <option
                                                                                    value="1"{{$employee->marital_status == 1 ? 'selected' : ''}}>{{ __('Married') }}</option>
                                                                                <option
                                                                                    value="2"{{$employee->marital_status == 2 ? 'selected' : ''}}>{{ __('Single') }}</option>
                                                                                <option
                                                                                    value="3"{{$employee->marital_status == 3 ? 'selected' : ''}}>{{ __('Divorced') }}</option>
                                                                                <option
                                                                                    value="4"{{$employee->marital_status == 4 ? 'selected' : ''}}>{{ __('Separated') }}</option>
                                                                                <option
                                                                                    value="5"{{$employee->marital_status == 5 ? 'selected' : ''}}>{{ __('Widowed') }}</option>
                                                                            </x-form.selectbox>
                                                                            <x-form.textbox
                                                                                labelName="{{__('file.Email')}}"
                                                                                name="email" required="required"
                                                                                col="col-md-12"
                                                                                placeholder="Enter Email Address"
                                                                                value="{{$employee->email}}"/>
                                                                            <x-form.textbox
                                                                                labelName="{{__('file.Contact No')}}"
                                                                                name="contact_no_one"
                                                                                required="required" col="col-md-12"
                                                                                placeholder="Enter Contact No"
                                                                                value="{{$employee->contact_no_one}}"/>
                                                                            <x-form.textbox
                                                                                labelName="{{__('file.Emergency Contact')}}"
                                                                                name="contact_no_two" col="col-md-12"
                                                                                placeholder="Enter Emergency Contact"
                                                                                value="{{$employee->contact_no_two}}"/>
                                                                            <x-form.textbox
                                                                                labelName="{{__('file.Address')}}"
                                                                                name="present_address"
                                                                                required="required" col="col-md-12"
                                                                                placeholder="Enter Address"
                                                                                value="{{$employee->present_address}}"/>
                                                                            <x-form.selectbox
                                                                                labelName="{{__('file.Blood Group')}}"
                                                                                name="blood_group" required="required"
                                                                                col="col-md-12" class="selectpicker">
                                                                                <option
                                                                                    value="A+"{{$employee->blood_group == 'A+' ? 'selected' : ''}}>{{ __('A+') }}</option>
                                                                                <option
                                                                                    value="A-"{{$employee->blood_group == 'A-'? 'selected' : ''}}>{{ __('A-') }}</option>
                                                                                <option
                                                                                    value="B+"{{$employee->blood_group == 'B+' ? 'selected' : ''}}>{{ __('B+') }}</option>
                                                                                <option
                                                                                    value="B-"{{$employee->blood_group == 'B-' ? 'selected' : ''}}>{{ __('B-') }}</option>
                                                                                <option
                                                                                    value="O+"{{$employee->blood_group == 'O+' ? 'selected' : ''}}>{{ __('O+') }}</option>
                                                                                <option
                                                                                    value="O-"{{$employee->blood_group == 'O-' ? 'selected' : ''}}>{{ __('O-') }}</option>
                                                                                <option
                                                                                    value="AB+"{{$employee->blood_group == 'AB+' ? 'selected' : ''}}>{{ __('AB+') }}</option>
                                                                                <option
                                                                                    value="AB-"{{$employee->blood_group == 'AB-' ? 'selected' : ''}}>{{ __('AB-') }}</option>
                                                                            </x-form.selectbox>

                                                                            <x-form.textbox
                                                                                labelName="{{__('file.National ID Number')}}"
                                                                                name="id_number" required="required"
                                                                                col="col-md-12"
                                                                                placeholder="Enter ID Number"
                                                                                value="{{$employee->id_number}}"/>
                                                                            <div class="form-group col-md-12 required">
                                                                                <label
                                                                                    for="joining_date">{{__('file.Joining Date')}}</label>
                                                                                <input type="date"
                                                                                       class="form-control date"
                                                                                       id="joining_date"
                                                                                       name="joining_date"
                                                                                       required="required"
                                                                                       value="{{$employee->joining_date}}"/>
                                                                            </div>
                                                                            <x-form.textbox
                                                                                labelName="{{__('file.Username')}}"
                                                                                name="username" required="required"
                                                                                col="col-md-12"
                                                                                placeholder="Enter Username"
                                                                                value="{{$employee->username}}"/>

                                                                        </div>
                                                                        <div class="col-sm-6">
                                                                            <div class="row">
                                                                                <x-form.textbox
                                                                                    labelName="{{__('file.Employee ID')}}"
                                                                                    name="employee_id"
                                                                                    required="required" col="col-md-12"
                                                                                    placeholder="Enter Employee ID"
                                                                                    value="{{$employee->employee_id}}"/>

                                                                                <x-form.selectbox
                                                                                    labelName="{{__('file.Branch')}}"
                                                                                    name="branch_id" required="required"
                                                                                    col="col-md-12"
                                                                                    class="selectpicker">
                                                                                    @foreach($branch as $row)
                                                                                        <option
                                                                                            value="{{$row->id}}"{{$row->id == $employee->branch_id ? 'selected' : ''}}>{{$row->name }}</option>
                                                                                    @endforeach
                                                                                </x-form.selectbox>
                                                                                <x-form.selectbox
                                                                                    labelName="{{__('file.Department')}}"
                                                                                    name="department_id"
                                                                                    required="required" col="col-md-12"
                                                                                    class="selectpicker">
                                                                                    @foreach ($department as $key => $row)
                                                                                        <option
                                                                                            value="{{ $row->id }}"{{$employee->department_id == $row->id ? 'selected' : ''}}>{{ $row->name }}</option>
                                                                                    @endforeach
                                                                                </x-form.selectbox>
                                                                                <x-form.selectbox
                                                                                    labelName="{{__('file.Designation')}}"
                                                                                    name="designation_id"
                                                                                    required="required" col="col-md-12"
                                                                                    class="selectpicker">
                                                                                    @foreach ($designation as $key => $row)
                                                                                        <option
                                                                                            value="{{ $row->id }}"{{$employee->designation_id == $row->id ? 'selected' : ''}}>{{ $row->name }}</option>
                                                                                    @endforeach
                                                                                </x-form.selectbox>
                                                                                <x-form.textarea
                                                                                    labelName="{{__('file.Academic Qualification')}}"
                                                                                    name="academic_qualification"
                                                                                    required="required" col="col-md-12"
                                                                                    placeholder="Enter Academic Qualification"
                                                                                    value="{{$employee->academic_qualification}}"/>
                                                                                <x-form.textarea
                                                                                    labelName="{{__('file.Professional Qualification')}}"
                                                                                    name="professional_qualification"
                                                                                    required="required" col="col-md-12"
                                                                                    placeholder="Enter Professional Qualification"
                                                                                    value="{{$employee->professional_qualification}}"/>
                                                                                <x-form.textarea
                                                                                    labelName="{{__('file.Experience')}}"
                                                                                    name="experience"
                                                                                    required="experience"
                                                                                    col="col-md-12"
                                                                                    placeholder="Enter Experience"
                                                                                    value="{{$employee->experience}}"/>
                                                                                <x-form.textbox
                                                                                    labelName="{{__('file.Previous Workplace')}}"
                                                                                    name="previous_workplace"
                                                                                    required="required" col="col-md-12"
                                                                                    placeholder="Enter Previous Work Place"
                                                                                    value="{{ $employee->previous_workplace }}"/>
                                                                                <div class="col-sm-12">
                                                                                    <div class="card">
                                                                                        <h3>Upload Resume</h3>
                                                                                        <div class="drop_box">
                                                                                            <header>
                                                                                                <h4>Select File
                                                                                                    here</h4>
                                                                                            </header>
                                                                                            <p>Files Supported: PDF,
                                                                                                doc</p>
                                                                                            <input type="file"
                                                                                                   accept=".doc,.pdf"
                                                                                                   name="resume"
                                                                                                   id="fileID"
                                                                                                   style="margin-left: 36%;">
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
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger btn-sm mr-3"
                                                    onclick="window.location.replace('{{ route("employee") }}');"><i
                                                    class="fas fa-sync-alt"></i> Close
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn"
                                                    onclick="storeData()"><i class="fas fa-save"></i>{{__('file.Save')}}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!--end: Datatable-->
        </div>
    </div>
    <!--end::Card-->
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
            let url = "{{route('employee.update')}}";
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
                            window.location.replace("{{ route('employee') }}");
                        }
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }
    </script>
@endpush
