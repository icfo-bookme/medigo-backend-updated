@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link rel="stylesheet" href="{{asset('css/jquery-ui.css')}}" />
    <link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .small-btn{
            width: 20px !important;
            height: 20px !important;
            padding: 0 !important;
        }
        .small-btn i{font-size: 10px !important;}
    </style>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title">
                        <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                    </div>
                    <div class="card-toolbar">
                        <a href="{{ route('sr.salary') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> SR Salary </a>
                    </div>
                </div>
            </div>
            <div class="card card-custom">
                <div class="card-body">
                    <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                        <form action="" id="salary_form" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class = "col-md-6 form-group">
                                    <label for="month">Month</label>
                                    <input type = "month" class = "form-control month" id = "month" name = "month"/>
                                </div>
                                <div class = "col-md-6 form-group">
                                    <label for="date">Date</label>
                                    <input type = "date" class = "form-control" id = "date" name = "date" value="{{date('Y-m-d')}}"/>
                                </div>
                            </div>
                            <div class="row table-responsive"><div class="col-md-12" id="salaryData"></div></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{asset('js/jquery-ui.js')}}"></script>
    <script src="{{asset('js/bootstrap-datetimepicker.min.js')}}"></script>
    <script>
        function _(x){
            return document.getElementById(x);
        }
        $(document).on('click','.deleteRaw',function(){
            $(this).parent().parent().remove();
        });
        $(document).on('change','.month',function(){
            if($(this).val() != ''){
                $.ajax({
                    url      : 'sr-salary/month-wise-salary-generate/' + $(this).val(),
                    success  : function(data){
                        $('#salaryData').empty().html(data);
                    },
                })
            }
        });
        $(document).on('input','.monthlyTargetAchieve',function(){
            _($(this).data('claim')).value = + $(this).val() + + _($(this).data('salary')).value + + _($(this).data('product_target_achieve')).value;
        })
        $(document).on('input','.productTargetAchieve',function(){
            _($(this).data('claim')).value = + $(this).val() + + _($(this).data('salary')).value + + _($(this).data('monthly_target_achieve')).value;
        });
        $(document).on('input','.ta_td',function(){
            _($(this).data('claim')).value = + _($(this).data('product_target_achieve')).value + + _($(this).data('salary')).value + + _($(this).data('monthly_target_achieve')).value + + $(this).val() - _($(this).data('deduction')).value;
        });
        $(document).on('input','.deduction',function(){
            _($(this).data('claim')).value = + _($(this).data('product_target_achieve')).value + + _($(this).data('salary')).value + + _($(this).data('monthly_target_achieve')).value - $(this).val();
        });
        function srSalaryStore(){
            let form     = document.getElementById('salary_form');
            let formData = new FormData(form);
            let url      = "{{route('sr.salary.store')}}";
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
                    $('#salary_form').find('.is-invalid').removeClass('is-invalid');
                    $('#salary_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#salary_form input#' + key).addClass('is-invalid');
                            $('#salary_form textarea#' + key).addClass('is-invalid');
                            $('#salary_form select#' + key).parent().addClass('is-invalid');
                            $('#salary_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') { window.location.replace("{{ route('sr.salary') }}"); }
                    }
                },
                error        : function (xhr, ajaxOption, thrownError) { console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
            });
        }
    </script>
@endpush

