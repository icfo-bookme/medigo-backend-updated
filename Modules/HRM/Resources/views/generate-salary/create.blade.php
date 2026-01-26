@extends('layouts.app')

@section('title', $page_title)
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>

    <link href="https://raw.githack.com/ttskch/select2-bootstrap4-theme/master/dist/select2-bootstrap4.css"
          rel="stylesheet">
    <style>
        select {
            width: 100%;
            min-height: 100px;
            border-radius: 3px;
            border: 1px solid #444;
            padding: 10px;
            color: #444444;
            font-size: 14px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
            color: #495057;
            height: 25px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background: #3699FF;
            padding: 3px 3px !important;
            color: white !important;
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
                </div>
            </div>
            <!--end::Notice-->
            <!--begin::Card-->
            <div class="card card-custom">
                <div class="card-header flex-wrap py-5">
                    <form method="POST" id="form-filter" class="col-md-12 px-0">
                        @csrf
                        <div class="row">
                            <x-form.selectbox labelName="{{__('Year')}}" name="year" id="year"
                                              col="col-md-3" class="selectpicker">
                                @foreach (salaryYears() as $key => $row)
                                    <option value="{{ $row }}">{{ $row }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <x-form.selectbox labelName="{{__('Month')}}" name="month" id="month"
                                              col="col-md-3" class="selectpicker">
                                @foreach (allMonths() as $key => $row)
                                    <option value="{{ $key }}">{{ $row }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-3" id="">
                                <label class="col-from-label">{{('Employee')}} <span
                                        class="text-danger">*</span></label>
                                <select class="form-control js-example-basic-multiple" name="employee_id[]"
                                        id="employee_id" data-live-search="true" required multiple>
                                    <option value="0">All</option>
                                    @foreach ($employees as $value)
                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-top:28px;">
                                    <div style="margin-top:28px;">
                                        <button id="btn-reset"
                                                class="btn btn-danger btn-sm btn-elevate btn-icon float-left mr-1"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Reset')}}">
                                            <i class="fas fa-undo-alt"></i></button>
                                        <button id="btn-filter"
                                                class="btn btn-primary btn-sm btn-elevate btn-icon mr-2 float-left btn-search"
                                                type="button"
                                                data-toggle="tooltip" data-theme="dark" title="{{__('file.Search')}}">
                                            <i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <!--begin: Datatable-->
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <form id="salary_store_or_update">
                                    @csrf
                                    <div id="report" style="width: 100%;margin:0;padding:0;">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $('.js-example-basic-multiple').each(function () {
            $(this).select2({
                placeholder: "    Select Employee",
                theme: 'bootstrap4',
                width: 'style',
                allowClear: Boolean($(this).data('allow-clear')),
            });
        });

        $(document).on('click', '.btn-search', function () {
            report();
        });

        function report() {
            let selectedOptions = $('#employee_id').val();
            let year = Number($('#year').val());
            let month = Number($('#month').val());
            if (selectedOptions.length > 0) {
                $.ajax({
                    url: "{{ route('get.employees.data') }}",
                    type: "POST",
                    data: {id: selectedOptions, year: year, month: month, _token: _token},
                    success: function (data) {
                        $('#report').empty();
                        $('#report').append(data);
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            } else {
                $('#report').empty();
                notification('error', 'Please select Employee');
            }
        }

        function store_data() {
            var rownumber = $('table#dataTable tbody tr:last').index();
            if (rownumber < 0) {
                notification("error", "Please Select Employee");
            } else {
                let form = document.getElementById('salary_store_or_update');
                let formData = new FormData(form);
                let url = "{{ route('generate.salary.store.or.update') }}";
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
                        $('#salary_store_or_update').find('.is-invalid').removeClass('is-invalid');
                        $('#salary_store_or_update').find('.error').remove();
                        if (data.status == false) {
                            $.each(data.errors, function(key, value) {
                                var key = key.split('.').join('_');
                                $('#salary_store_or_update input#' + key).addClass('is-invalid');
                                $('#salary_store_or_update textarea#' + key).addClass('is-invalid');
                                $('#salary_store_or_update select#' + key).parent().addClass('is-invalid');
                                $('#salary_store_or_update #' + key).parent().append(
                                    '<small class="error text-danger">' + value + '</small>');
                            });
                            $('html, body').animate({
                                scrollTop: ($('.is-invalid').offset().top - 300)
                            }, 1000);
                        } else {
                            notification(data.status, data.message);
                            if (data.status == 'success') {
                                window.location.replace("{{ route('generate.salary') }}");
                            }
                        }
                    },
                    error: function(xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            }
        }
    </script>

@endpush
