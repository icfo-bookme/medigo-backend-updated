@extends('layouts.app')
@section('title', $page_title)

@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> Customer Bulk Upload</h3></div>
                    <div class="card-toolbar">
                        <a href="{{ route('customer') }}" class="btn btn-warning btn-sm font-weight-bolder"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="p-4 mb-4 text-black" style="background-color: #a9c9f6; border-radius: 8px;">
                        <strong>Step 1:</strong><br>
                        1. Download the skeleton file and fill it with proper data.<br>
                        2. You can download the example file to understand how the data must be filled.<br>
                        3. Once you have downloaded and filled the skeleton file, upload it in the form below and submit.<br>
                        4. After uploading customers you need to edit them and set the customer's images and choices.
                    </div>
                    <a href="{{ route('customer.download_csv') }}" class="btn btn-info font-weight-bolder" id="download_csv" style="border-radius: 5px;"><i class="fas fa-download"></i> Download Skeleton CSV
                    </a>
                </div>
            </div>

            <div class="card card-custom gutter-b">
                <div class="card-header flex-wrap py-5">
                    <div class="card-title"><h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> Upload Customer File</h3></div>
                </div>
                <div class="card-body">
                    <form id="store_or_update_form" method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.file label="CSV/Excel File" name="csv_file" required="required" col="col-md-6" placeholder="Enter product name"/>
                        <button type="button" class="btn btn-info btn-sm" id="upload_btn" style="border-radius: 5px;"><i class="fas fa-upload"></i> Upload CSV</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        $(document).on('click', '#upload_btn', function () {
            let form = $('#store_or_update_form');
            let formData = new FormData(form[0]);
            $.ajax({
                url: "{{ route('customer.bulk.store') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#upload_btn').prop('disabled', true);
                    $('#upload_btn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                    // notification('info', 'Please wait while we are processing your request...');
                },
                complete: function () {
                    $('#upload_btn').prop('disabled', false);
                    $('#upload_btn').html('Upload CSV');
                },
                success: function (data) {
                    if (data.status === 'success') {
                        notification(data.status, data.message);
                    } else {
                        let errorMessage = '';
                        $.each(data.errors, function (key, value) {
                            errorMessage += value[0] + '\n';
                        });
                        notification(data.status, errorMessage);
                    }
                },
                error: function (xhr, ajaxOption, thrownError) {
                    console.log(xhr.responseJSON); // Log the full response
                    console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    $('#upload_btn').prop('disabled', false);
                    $('#upload_btn').html('Upload CSV');
                }
            });
        });
    </script>
@endpush
