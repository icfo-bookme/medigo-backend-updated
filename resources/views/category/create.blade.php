@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('css/tagify.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css"/>
    <style>
        .tagsinput {
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
                        <a href="{{ route('category') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
            </div>
            <form method="post" id="store_or_update_form" enctype="multipart/form-data">
                @csrf
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group required code">
                                <label for="code">Code</label>
                                <div class="input-group" id="code_section">
                                    <input type="text" class="form-control" name="cat_code" id="cat_code" required="required">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-primary" id="generate-code" data-toggle="tooltip" data-theme="dark" title="Generate Code"
                                              style="border-top-right-radius: 0.42rem;border-bottom-right-radius: 0.42rem;border:0;cursor: pointer;"><i
                                                class="fas fa-retweet text-white"></i></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="row">
                                    <input type="hidden" name="product_id" id="product_id">
                                    <x-form.textbox labelName="Category Name" name="name" required="required" col="col-md-12" placeholder="Enter cagory name"/>
                                </div>
                            </div>
                            <x-form.selectbox labelName="Parent Category" name="parent_id" required="required" col="col-md-3" class="selectpicker">
                                <option value="0" selected>No Parent</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="col-md-2">
                                <div class="row">
                                    <div class="form-group col-md-12 mb-0 text-center">
                                        <label for="logo" class="form-control-label">Category Image</label>
                                        <div class="col=md-12 px-0  text-center">
                                            <div id="image"></div>
                                            <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB and Format (png,jpg,jpeg,svg,webp)</span></div>
                                        </div>
                                        <input type="hidden" name="old_image" id="old_image">
                                    </div>
                                </div>
                            </div>
                            {{--                            <div class="col-md-2">--}}
                            {{--                                <div class="row" style="border: 2px dotted;padding: 31px 0px;">--}}
                            {{--                                    <div class="form-group col-md-12 mb-0 text-center">--}}
                            {{--                                        <label for="image" class="form-control-label">Category Image</label>--}}
                            {{--                                        <div class="col=md-12 px-0  text-center">--}}
                            {{--                                            <input type="file" name="image" required="required" id="image">--}}
                            {{--                                        </div>--}}
                            {{--                                        <input type="hidden" name="old_image" id="old_image">--}}
                            {{--                                    </div>--}}
                            {{--                                </div>--}}
                            {{--                            </div>--}}
                        </div>
                    </div>
                </div>
                <div class="card card-custom">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <a type="button" class="btn btn-danger btn-sm mr-3" href="{{ route('category') }}"><i class="fas fa-sync-alt"></i> Reset</a>
                                <button type="button" class="btn btn-primary btn-sm mr-3" onclick="storeData(1)" id="save-btn-1"><i class="far fa-save"></i> Save</button>
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
    <script>
        function storeData(btn) {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            $.ajax({
                url: "{{route('category.store.or.update')}}",
                type: "POST",
                data: formData,
                dataType: "JSON",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $('#save-btn-' + btn).addClass('spinner spinner-white spinner-right');
                },
                complete: function () {
                    $('#save-btn-' + btn).removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    $('#store_or_update_form').find('.is-invalid').removeClass('is-invalid');
                    $('#store_or_update_form').find('.error').remove();
                    if (data.status == false) {
                        $.each(data.errors, function (key, value) {
                            var key = key.split('.').join('_');
                            $('#store_or_update_form input#' + key).addClass('is-invalid');
                            $('#store_or_update_form textarea#' + key).addClass('is-invalid');
                            $('#store_or_update_form select#' + key).parent().addClass('is-invalid');
                            if (key == 'code' || key == 'start_date' || key == 'end_date') {
                                $('#store_or_update_form #' + key).parents('.form-group').append('<small class="error text-danger">' + value + '</small>');
                            } else {
                                $('#store_or_update_form #' + key).parent().append('<small class="error text-danger">' + value + '</small>');
                            }
                        });
                    } else {
                        notification(data.status, data.message);
                        if (data.status == 'success') {
                            if (btn == 1) {
                                window.location.replace("{{ route('category') }}");
                            } else {
                                window.location.replace("{{ route('category') }}");
                            }
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
        $(document).ready(function () {
            /** Start :: Product Image **/
            $("#image").spartanMultiImagePicker({
                fieldName: 'image',
                maxCount: 1,
                rowHeight: '150px',
                groupClassName: 'col-md-12 col-sm-12 col-xs-12',
                maxFileSize: '',
                dropFileLabel: "Drop Here",
                allowedExt: '',
            });
            $("input[name='image']").prop('required', true);
            $('.remove-files').on('click', function () {
                $(this).parents(".col-md-12").remove();
            });
            /** End :: Product Image **/
            //Generate Code
            $(document).on('click', '#generate-code', function () {
                $.ajax({
                    url: "{{ route('category.generate.code') }}",
                    type: "GET",
                    dataType: "JSON",
                    beforeSend: function () {
                        $('#generate-code').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
                        $('#generate-code').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        data ? $('#store_or_update_form #cat_code').val(data) : $('#store_or_update_form #cat_code').val('');
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush
