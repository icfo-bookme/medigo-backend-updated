@extends('layouts.app')
@section('title', $page_title)
@push('styles')
    <link rel="stylesheet" href="css/jquery-ui.css"/>
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
                        <div class="card-toolbar"><a href="{{ route('campaign.category') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
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
                                <option value="amount">Amount</option>
                            </x-form.selectbox>
                            <x-form.textbox labelName="Amount" name="discount_amount"  col="col-md-1"/>
                            <x-form.textbox labelName="Search Category" name="search_text" col="col-md-4"
                                            required="required" Placeholder="Search Category By Name" onkeyup="delayedGetCategories()"/>
                            <x-form.selectbox labelName="Category" name="category_id" col="col-md-3" class="selectpicker" onchange="delayedGetCategories()">
                                @foreach ($categories as $value)
                                    <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </x-form.selectbox>
                            <div class="btn-group-sm col-md-5 mt-8">
                                <button type="button" class="btn btn-info btn-sm" id="list-btn"><i class="far fa-list-alt"></i>Get Added Categories
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" id="refresh-btn"><i class="fas fa-sync-alt"></i>Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-5" id="category-section">
                </div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="js/jquery-ui.js"></script>
    <script src="js/moment.js"></script>
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
                getCategories();
            }
        }

        function getCategories() {
            let searchText = $('#search_text').val();
            let campaignId = $('#campaign_id').val();
            let categoryId = $('#category_id').val();
            $.ajax({
                url: "{{ route('get.category') }}",
                type: "GET",
                data: {search_text: searchText, campaign_id: campaignId, category_id: categoryId, _token: _token},
                dataType: "html",
                beforeSend: function () {
                    $('#category-section').html('');
                },
                complete: function () {
                    $('#category-section').removeClass('d-none');
                },
                success: function (data) {
                    $('#category-section').html(data);
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                }
            });
        }

        function toggleCheckbox(key) {
            let checkbox = document.getElementById('category_check_' + key);
            let categoryId = $('#category_id_' + key).val();
            checkbox.checked = !checkbox.checked;
            storeData(categoryId);
        }

        function storeData(categoryId) {
            let form = document.getElementById('store_or_update_form');
            let formData = new FormData(form);
            formData.append('checked_category_id', categoryId);
            let url = "{{ route('campaign.category.store.or.update') }}";
            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                dataType: "JSON",
                contentType: false,
                processData: false,
                cache: false,
                beforeSend: function () {
                    $('#category-section').addClass('spinner spinner-white spinner-right');
                },
                complete: function () {
                    $('#category-section').removeClass('spinner spinner-white spinner-right');
                },
                success: function (data) {
                    if (data.status === 'success') {
                        notification(data.status, data.message);
                        table.ajax.reload();
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

        function delayedGetCategories() {
            let campaignId = $('#campaign_id').val();
            if (!campaignId) {
                notification('error', 'Please select a campaign first');
                $('#search_text').prop('disabled', true);
                $('#search_text').val('');
                return;
            } else {
                clearTimeout(delayTimer);
                delayTimer = setTimeout(getCategories, 1000);
            }
        }

        function getListedCategories(campaignId) {
            $.ajax({
                url: "{{ route('get.listed.category') }}",
                type: "GET",
                data: {campaign_id: campaignId, _token: _token},
                dataType: "html",
                beforeSend: function () {
                    $('#category-section').html('');
                },
                complete: function () {
                    $('#category-section').removeClass('d-none');
                },
                success: function (data) {
                    $('#category-section').html(data);
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
                getListedCategories(id);
            }
        });
        $(document).on('click', '#refresh-btn', function () {
            $('#search_text').val('');
            $('#category_id').val('').selectpicker('refresh');
            getCategories();
            $('#save-btn').addClass('d-none');
        });
    </script>
@endpush
