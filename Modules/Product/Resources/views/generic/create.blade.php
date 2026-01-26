@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('css/tagify.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css" />
    <style>
        .tagsinput{
            height: calc(1.5em + 1.3rem + 2px) !important;
        }
    </style>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('generic') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
            <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="row">
                                    <div class="col-md-4"></div>
                                    <x-form.textbox labelName="Generic Name" name="generic_name" required="required" col="col-md-12" placeholder="Enter Generic Name"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered" id="unitTable">
                                    <thead class="bg-primary">
                                    <tr class="text-center">
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="text-center" >

                                        <td style="width: 30%;">
                                            <input type="text" class="form-control" id="title" name="title[]"/>
                                        </td>
                                        <td>
                                            <textarea type="text" class="form-control"  name="description[]"></textarea>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm addRaw"><i class="fas fa-plus-circle"></i></button><br/>
                                            <button type = "button" class = "btn btn-danger btn-sm deleteRaw" style="margin-top:3px"><i class = "fas fa-minus-circle"></i></button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <br/>
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a type="button" class="btn btn-danger btn-sm mr-3" href="{{ route('product.add') }}"><i class="fas fa-sync-alt"></i> Reset</a>
                                <button type="button" class="btn btn-primary btn-sm mr-3" onclick="storeData(1)" id="save-btn-1"><i class="far fa-save"></i> Save</button>
                                <button type="button" class="btn btn-success btn-sm"  onclick="storeData(2)" id="save-btn-2"><i class="far fa-plus-square"></i> Save & Add New</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{asset('js/jQuery.tagify.min.js')}}"></script>
    <script src="{{asset('js/spartan-multi-image-picker.min.js')}}"></script>
    <script src="{{asset('js/moment.js')}}"></script>
    <script src="{{asset('js/bootstrap-datetimepicker.min.js')}}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/35.3.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>
    <script>

        function storeData(btn){
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            $.ajax({
                url: "{{route('generic.store.or.update')}}",
                type: "POST",
                data: formData,
                dataType: "JSON",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function(){
                    $('#save-btn-'+btn).addClass('spinner spinner-white spinner-right');
                },
                complete: function(){
                    $('#save-btn-'+btn).removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                    $('#store_or_update_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value){
                            var key = key.split('.').join('_');
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            if(key == 'code' || key == 'start_date' || key == 'end_date'){
                                $('#store_or_update_form #' + key).parents('.form-group').append('<small class="error text-danger">' + value + '</small>');
                            }else{
                                $('#store_or_update_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                            }
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            if(btn == 1){
                                window.location.replace("{{ route('generic') }}");
                            }else{
                                window.location.replace("{{ route('generic') }}");
                            }
                        }
                    }
                },
                error: function (xhr, ajaxOption, thrownError) { console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText); }
            });
        }
    </script>

    <script>
        $(document).on('click','.addRaw',function(){
            let html;
            html = `<tr>
                    <td style="width: 30%;">
                        <input type="text" class="form-control" id="title" name="title[]"/>
                    </td>
                    <td>
                        <textarea type="text" class="form-control"  name="description[]"></textarea>
                    </td>
                    <td>
            <button type="button" class="btn btn-primary btn-sm addRaw" style="margin-left: 41%;"><i class="fas fa-plus-circle"></i></button><br/>
            <button type = "button" class = "btn btn-danger btn-sm  deleteRaw" style="margin-top:3px;margin-left:41%;"><i class = "fas fa-minus-circle"></i></button>
        </td>
    </tr>`;
            $('#unitTable tbody').append(html);
            i++;
        });
        $(document).on('click','.deleteRaw',function(){
            $(this).parent().parent().remove();
        });
    </script>

@endpush
