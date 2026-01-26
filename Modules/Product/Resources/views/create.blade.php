@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link href="{{asset('css/tagify.css')}}" rel="stylesheet" type="text/css"/>
    <link href="{{asset('css/bootstrap-datetimepicker.min.css')}}" rel="stylesheet" type="text/css"/>
    <!-- select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/css/select2.min.css" rel="stylesheet"/>

    <!-- select2-bootstrap4-theme -->
    <link href="https://raw.githack.com/ttskch/select2-bootstrap4-theme/master/dist/select2-bootstrap4.css" rel="stylesheet"> <!-- for live demo page -->

    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.css" rel="stylesheet">


    <style>
        .tagsinput {
            height: calc(1.5em + 1.3rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
            color: #495057;
            height: 25px;
        }

        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            background: #8950FC;
            padding: 3px 3px !important;
            color: white !important;
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
                        <a href="{{ route('product') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
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
                                    <input type="hidden" name="product_id" id="product_id">
                                    <x-form.textbox labelName="Product Name" name="name" required="required" col="col-md-6" placeholder="Enter product name"/>

                                    {{--                            <x-form.selectbox labelName="Barcode Symbology" name="barcode_symbology" required="required" col="col-md-4" class="selectpicker">--}}
                                    {{--                                @foreach (BARCODE_SYMBOL as $key => $value)--}}
                                    {{--                                    <option value="{{ $key }}" {{ ($key == 1) ? 'selected' : '' }}>{{ $value }}</option>--}}
                                    {{--                                @endforeach--}}
                                    {{--                            </x-form.selectbox>--}}
                                    <x-form.selectbox labelName="Generic Name" name="generic_id" required="required" col="col-md-6" class="selectpicker">
                                        @if (!$generic->isEmpty())
                                            @foreach ($generic as $g_key => $row)
                                                <option value="{{ $g_key }}">{{ $row }}</option>
                                            @endforeach
                                        @endif
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Company Name" name="brand_id" required="required" col="col-md-5" class="selectpicker">
                                        @if (!$brands->isEmpty())
                                            @foreach ($brands as $b_key =>  $brand)
                                                <option value="{{ $b_key }}">{{ $brand }}</option>
                                            @endforeach
                                        @endif
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Category" name="category_id" required="required" col="col-md-4" class="selectpicker">
                                        @if (!$categories->isEmpty())
                                            @foreach ($categories as $c_key => $category)
                                                <option value="{{ $c_key }}">{{ $category }}</option>
                                            @endforeach
                                        @endif
                                    </x-form.selectbox>
                                    <x-form.selectbox labelName="Status" name="status" col="col-md-3" class="selectpicker" required="required">
                                        @foreach (STATUS as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </x-form.selectbox>

{{--                                    <div class="form-group col-md-12">--}}
{{--                                        <label class="col-from-label">Similar Product</label>--}}
{{--                                        <select class="form-control js-example-basic-multiple" name="similar_product_id[]" id="similar_product_id" data-live-search="true" required--}}
{{--                                                multiple>--}}
{{--                                            @if (!$products->isEmpty())--}}
{{--                                                @foreach ($products as $product)--}}
{{--                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>--}}
{{--                                                @endforeach--}}
{{--                                            @endif--}}
{{--                                        </select>--}}
{{--                                    </div>--}}

                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="row">
                                    <div class="form-group col-md-12 mb-0 text-center">
                                        <label for="logo" class="form-control-label">Product Image</label>
                                        <div class="col=md-12 px-0  text-center">
                                            <div id="image"></div>
                                            <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB and Format (png,jpg,jpeg,svg,webp)</span></div>
                                        </div>
                                        <input type="hidden" name="old_image" id="old_image">
                                    </div>
                                </div>
                            </div>
                            <input type="checkbox" id="product_type" name="product_type" value="2" style="height: 23px; width: 39px;">
                            <label for="vehicle1"> Home Section Product</label><br>
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
                                        <th>Unit</th>
                                        <th>Item code</th>
                                        <th>Sale Price</th>
                                        <th>Discount (%)</th>
                                        <th>Alert Qty</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="text-center">
                                        <td>
                                            <select class="form-control selectpicker" data-live-search="true" id="product_unit_id" name="product_unit_id[]" required="required">
                                                <option value="">Please Select</option>
                                                @foreach($units as $u_key => $u_row)
                                                    <option value="{{$u_key}}">{{$u_row}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group" id="code_section">
                                                <input type="text" class="form-control" name=" item_code[]" required="required">
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="price" name="price[]"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="discount" name="discount[]"/>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="alert_qty" name="alert_qty[]"/>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm addRaw"><i class="fas fa-plus-circle"></i></button>
                                            <br/>
                                            <button type="button" class="btn btn-danger btn-sm deleteRaw" style="margin-top:3px"><i class="fas fa-minus-circle"></i></button>
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
                            <div class="col-md-12">
                                <div class="row">

                                    <div class="form-group col-md-6">
                                        <label for="medical_overview" class="col-md-3 col-from-label">Medical Overview</label>
                                        <div class="col-md-12">
                                            <textarea class="summernote" name="medical_overview"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="quick_tips" class="col-md-3 col-from-label">Quick Tips</label>
                                        <div class="col-md-12">
                                            <textarea class="summernote" name="quick_tips"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label for="brief_description" class="col-md-3 col-from-label">Brief Description</label>
                                        <div class="col-md-12">
                                            <textarea class="summernote" name="brief_description"></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="disclaimer" class="col-md-3 col-from-label">Disclaimer</label>
                                        <div class="col-md-12">
                                            <textarea class="summernote" name="disclaimer"></textarea>
                                        </div>
                                    </div>


                                    <div class="form-group col-md-6">
                                        <label for="indication" class="col-md-3 col-from-label">Indication</label>
                                        <div class="col-md-12">
                                            <textarea class="summernote" name="indication"></textarea>
                                        </div>
                                    </div>


                                </div>
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
    {{--<script src="https://cdn.ckeditor.com/ckeditor5/35.3.1/classic/ckeditor.js"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.9/summernote-bs4.js"></script>

    {{--<script>--}}
    {{--    ClassicEditor--}}
    {{--        .create(document.querySelector('#description'))--}}
    {{--        .catch(error => {--}}
    {{--            console.error(error);--}}
    {{--        });--}}
    {{--</script>--}}
    <script>
        $(document).ready(function () {

            $('.summernote').summernote({
                placeholder: '',
                tabsize: 2,
                height: 250
            });


            $('.js-example-basic-multiple').each(function () {
                $(this).select2({
                    placeholder: " Select ",
                    theme: 'bootstrap4',
                    width: 'style',
                    allowClear: Boolean($(this).data('allow-clear')),
                });
            });


            $('.date').datetimepicker({format: 'YYYY-MM-DD', ignoreReadonly: true});
            //tagify plugin initialization
            $('#attribute_1_value').tagify({
                transformTag: function (e) {
                    e.class = "tagify__tag tagify__tag--primary"
                },
            });
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
            $(document).on('click', '#generate-code', function (row) {
                $.ajax({
                    url: "{{ route('product.generate.code') }}",
                    type: "GET",
                    dataType: "JSON",
                    beforeSend: function () {
                        $('#generate-code').addClass('spinner spinner-white spinner-right');
                    },
                    complete: function () {
                        $('#generate-code').removeClass('spinner spinner-white spinner-right');
                    },
                    success: function (data) {
                        data ? $('#store_or_update_form #code').val(data) : $('#store_or_update_form #code').val('');
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });


            $(document).on('click', '#offer', function () {
                if ($(this).is(':checked')) {
                    $(this).val(1);
                    $('.offer-section').removeClass('d-none');
                } else {
                    $(this).val(2);
                    $('.offer-section').addClass('d-none');
                    $('#offer_price,#start_date,#end_date').val('');
                }
            });

        });

        function storeData(btn) {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            $.ajax({
                url: "{{route('product.store')}}",
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
                                window.location.replace("{{ route('product') }}");
                            } else {
                                window.location.replace("{{ route('product.add') }}");
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
        $(document).on('click', '.addRaw', function () {
            let row = 1;
            let html;
            html = `<tr>
                    <td>
                        <select class="form-control " data-live-search="true" id="product_unit_id" name="product_unit_id[]>
                            <option value="">Please Select</option>
                            @foreach($units as $u_key=> $row)
            <option value="{{$u_key}}">{{$row}}</option>
                            @endforeach
            </select>
        </td>
        <td>
            <div class="input-group" id="code_section">
                <input type="text" class="form-control" name="item_code[]" required="required">
            </div>
        </td>
        <td>
            <input type="text" class="form-control" id="price" name="price[]"/>
        </td>
        <td>
            <input type="text" class="form-control" id="discount" name="discount[]"/>
        </td>
        <td>
            <input type="text" class="form-control" id="alert_qty" name="alert_qty[]"/>
        </td>
        <td>
            <button type="button" class="btn btn-primary btn-sm addRaw" style="margin-left: 28%;"><i class="fas fa-plus-circle"></i></button><br/>
            <button type = "button" class = "btn btn-danger btn-sm  deleteRaw" style="margin-top:3px;margin-left: 28%;"><i class = "fas fa-minus-circle"></i></button>
        </td>
    </tr>`;
            $('#unitTable tbody').append(html);
            i++;
        });
        $(document).on('click', '.deleteRaw', function () {
            $(this).parent().parent().remove();
        });
    </script>

@endpush
