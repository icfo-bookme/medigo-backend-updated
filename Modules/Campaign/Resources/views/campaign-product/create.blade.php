@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link rel="stylesheet" href="css/jquery-ui.css"/>
    <style>
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 120px;
            height: 120px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
        }

        /* Safari */
        @-webkit-keyframes spin {
            0% {
                -webkit-transform: rotate(0deg);
            }
            100% {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <!--begin::Notice-->
            <form action="" id="store_or_update_form" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card card-custom">
                    <div class="card-header flex-wrap py-3">
                        <div class="card-title">
                            <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                        </div>
                        <div class="card-toolbar"><a href="{{ route('campaign.product') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <x-form.selectbox labelName="Campaign" name="campaign_id" required="required" col="col-md-3"
                                              class="selectpicker" onchange="getCampaign(this.value)">
                                @foreach ($campaigns as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <input type="hidden" name="campaign_name" id="campaign_name"/>
                            <x-form.date type="datetime-local" labelName="Start Date" name="start_date" required="required" col="col-md-3"/>
                            <x-form.date type="datetime-local" labelName="End Date" name="end_date" required="required" col="col-md-3"/>
                            <x-form.selectbox labelName="Type" name="discount_type" col="col-md-2" class="selectpicker">
                                <option value="percentage">Percentage</option>
                                <option value="amount">Fixed</option>
                            </x-form.selectbox>
                            <x-form.textbox labelName="Amount" name="discount_amount"  col="col-md-1"/>
                            <x-form.textbox labelName="Search Product" name="search_text" col="col-md-4" onkeyup="delayedGetProducts()"
                                            required="required" Placeholder="Search Product By Name, SKU, Code"/>
                            <x-form.selectbox labelName="Category" name="category_id" col="col-md-3" class="selectpicker" onchange="delayedGetProducts()">
                                @foreach ($categories as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="btn-group-sm col-md-5 mt-8">
                                <button type="button" class="btn btn-info btn-sm" id="list-btn"><i class="far fa-list-alt"></i>Get Added Products
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="refresh-btn"><i class="fas fa-sync-alt"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-5" id="product-section">

                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        let delayTimer;

        function getCampaign(value) {
            if (value) {
                $.ajax({
                    url: "{{ route('get.campaign') }}",
                    type: "GET",
                    data: {id: value, _token: _token},
                    dataType: "JSON",
                    success: function (data) {
                        if (data) {
                            $('#store_or_update_form #campaign_name').val(data.name);
                            $('#store_or_update_form #start_date').val(data.start_date);
                            $('#store_or_update_form #end_date').val(data.end_date);
                            $('#store_or_update_form #discount_type').val(data.discount_type);
                            $('#store_or_update_form #discount_amount').val(data.discount_amount);

                            $('#store_or_update_form #discount_type.selectpicker').selectpicker('refresh');

                            // Disable input fields
                            $('#store_or_update_form #start_date').prop('readonly', true);
                            $('#store_or_update_form #end_date').prop('readonly', true);
                            $('#store_or_update_form #discount_type').prop('disabled', true);
                            $('#store_or_update_form #discount_amount').prop('readonly', true);

                            // Enable search text input
                            $('#search_text').prop('disabled', false);
                        } else {
                            notification('error', 'Campaign data not found');
                        }
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
                getProducts();
            }
        }

        function getProducts() {
            let searchText = $('#search_text').val();
            let campaignId = $('#campaign_id').val();
            let categoryId = $('#category_id').val();
            $.ajax({
                url: "{{ route('get.product') }}",
                type: "GET",
                data: {search_text: searchText, campaign_id: campaignId, category_id: categoryId, _token: _token},
                dataType: "html",
                beforeSend: function () {
                    $('#product-section').html('');
                    $('#product_loading').removeClass('d-none');
                },
                complete: function () {
                    $('#product_loading').addClass('d-none');
                },
                success: function (data) {
                    $('#product-section').html(data);
                    $('#product_loading').addClass('d-none');
                    $('.product-table tbody .selectpicker').selectpicker('refresh');
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function toggleCheckbox(key) {
            let checkbox = document.getElementById('product_check_' + key);
            let productId = $('#product_id_' + key).val();
            checkbox.checked = !checkbox.checked;
            storeData(productId);
        }

        function storeData(productId) {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            formData.append('checked_product_id', productId);
            let url = "{{ route('campaign.product.store.or.update') }}";
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "JSON",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $('#product-section').addClass('spinner spinner-white spinner-right');
                },
                complete: function () {
                    $('#product-section').removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    if (data.status === 'success') {
                        notification(data.status, data.message);
                        table.ajax.reload();
                        $('#campaign-check-section').addClass('d-none');
                    } else {
                        notification(data.status, data.message);
                    }
                },
                error: function (xhr) {
                    $('#save-btn').prop('disabled', false).removeClass('spinner spinner-white spinner-right');
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let allErrors = 'The given data was invalid. Please check the errors below:\n';
                        $.each(xhr.responseJSON.errors, function (key, value) {
                            allErrors += value[0] + '\n';

                            let element = $('#store_or_update_form').find('#' + key);
                            element.addClass('is-invalid');
                            element.parent().append(
                                '<small class="error text-danger">' + value[0] + '</small>'
                            );
                        });

                        notification('error', allErrors);
                    } else {
                        notification('error', xhr.responseJSON.message || 'An unexpected error occurred.');
                        console.error(xhr.responseText);
                    }
                }
            });
        }

        function delayedGetProducts() {
            let campaignId = $('#campaign_id').val();
            if (!campaignId) {
                notification('error', 'Please select a campaign first');
                $('#search_text').prop('disabled', true);
                $('#search_text').val('');
                return;
            } else {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(getProducts, 1000);
            }
        }

        function getListedProducts(campaignId) {
            $.ajax({
                url: "{{ route('get.listed.product') }}",
                type: "GET",
                data: {campaign_id: campaignId, _token: _token},
                dataType: "html",
                beforeSend: function () {
                    $('#product-section').html('');
                    $('#product_loading').removeClass('d-none');
                },
                complete: function () {
                    $('#product_loading').addClass('d-none');
                },
                success: function (data) {
                    $('#product-section').html(data);
                    $('#product_loading').addClass('d-none');
                    $('#refresh-btn').removeClass('d-none');
                    $('#save-btn').removeClass('d-none');
                    $('.product-table tbody .selectpicker').selectpicker('refresh');
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        $(document).on('click', '#list-btn', function () {
            let id = $('#campaign_id').val();
            if (!id) {
                notification('error', 'Please select a campaign first');
                return;
            } else {
                getListedProducts(id);
            }
        });
        $(document).on('click', '#refresh-btn', function () {
            $('#search_text').val('');
            $('#category_id').val('').selectpicker('refresh');
            getProducts();
            $('#save-btn').addClass('d-none');
        });
    </script>
@endpush
