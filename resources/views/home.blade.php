@extends('layouts.app')
@section('title','Dashboard')
@push('styles')
    <link href="css/daterangepicker.min.css" rel="stylesheet" type="text/css"/>
    <style>
        .today-btn {
            border-radius: 5px 0 0 5px !important;
        }

        .week-btn, .month-btn {
            border-radius: 0 !important;
        }

        .year-btn {
            border-radius: 0 5px 5px 0 !important;
        }

        .icon {
            width: 40px;
            height: 40px;
        }
    </style>
@endpush
@section('content')
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid">
            <div class="row">
                <div class="form-group col-md-12 mb-0">
                    <form method="POST" id="form-filter" style="margin-left: 25%">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="name">Choose Your Date</label>
                                <div class="input-group">
                                    <input type="text" class="form-control daterangepicker-filed">
                                    <input type="hidden" id="start_date" name="start_date">
                                    <input type="hidden" id="end_date" name="end_date">
                                </div>
                            </div>
                            <div style="margin-top:28px;">
                                <button id="btn-reset" class="btn btn-danger btn-sm btn-elevate btn-icon" type="button"
                                        data-toggle="tooltip" data-theme="dark" title="Reset"><i
                                        class="fas fa-undo-alt"></i></button>

                                <button id="btn-filter" class="btn btn-primary btn-sm btn-elevate btn-icon mr-2"
                                        type="button"
                                        data-toggle="tooltip" data-theme="dark" title="Search"><i
                                        class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/stock.png')}}" alt="stock"></div>
                        <h6 id="stock_value">{{ 0 }}</h6>
                        <h6>Stock Value</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/order.png')}}" alt="delivered_orders"></div>
                        <h6 id="delivered_orders">{{ 0 }}</h6>
                        <h6>Delivered Orders</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/product.png')}}" alt="product"></div>
                        <h6 id="product">{{ 0 }}</h6>
                        <h6>Product</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/watch.png')}}" alt="avg_delivery_time"></div>
                        <h6 id="avg_delivery_time">{{ 0 }}</h6>
                        <h6>Avg. Delivery Time</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/deadline_miss.png')}}" alt="deadline_miss_percentage"></div>
                        <h6 id="deadline_miss_percentage">{{ 0 }}%</h6>
                        <h6>Deadline Miss Percentage</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/cash.png')}}" alt="ending_balance"></div>
                        <h6 id="ending_balance">{{ 0 }}TK</h6>
                        <h6>Closing Balance</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/cash.png')}}" alt="Current Balance"></div>
                        <h6 id="current_balance">{{ 0 }}TK</h6>
                        <h6>Current Balance</h6>
                        <button type="button" class="btn btn-success view-data">View Invoices</button>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/customer_grp.png')}}" alt="customer"></div>
                        <h6 id="total_customer">{{ 0 }}</h6>
                        <h6>Total Customer</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/customer_grp.png')}}" alt="customer"></div>
                        <h6 id="total_visitor">{{ 0 }}</h6>
                        <h6>Total Visited(Online)</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/feedback.png')}}" alt="customer_feedback"></div>
                        <h6 id="customer_feedback">{{ 0 }}</h6>
                        <h6>Customer Feedback</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/customer.png')}}" alt="customer"></div>
                        <h6 id="customer">{{ 0 }}</h6>
                        <h6>New Customer</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/user.png')}}" alt="user"></div>
                        <h6 id="user">{{ 0 }}</h6>
                        <h6>User</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/purchase.png')}}" alt="purchase"></div>
                        <h6 id="purchase">{{ 0 }}TK</h6>
                        <h6>Purchase</h6>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="bg-white text-center py-3 text-primary bold">
                        <div><img src="{{asset('icon/sale.png')}}" alt="sale"></div>
                        <h6 id="sale">{{ 0 }}TK</h6>
                        <h6>Sale</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('balance.modal')
@endsection
@push('scripts')
    <script src="js/moment.js"></script>
    <script src="js/knockout-3.4.2.js"></script>
    <script src="js/daterangepicker.min.js"></script>
    <script>
        $('.daterangepicker-filed').daterangepicker({
            callback: function (startDate, endDate) {
                var start_date = startDate.format('YYYY-MM-DD');
                var end_date = endDate.format('YYYY-MM-DD');
                var title = start_date + ' To ' + end_date;
                $(this).val(title);
                $('input[name="start_date"]').val(start_date);
                $('input[name="end_date"]').val(end_date);
            }
        });
        $(document).ready(function () {
            loadData("{{ date('Y-m-d', strtotime('-6 days')) }}", "{{ date('Y-m-d') }}");
            $('.data-btn').on('click', function () {
                $('.data-btn').removeClass('active');
                $(this).addClass('active');
                var start_date = $(this).data('start_date');
                var end_date = $(this).data('end_date');
                loadData(start_date, end_date);
            });
            $('#btn-filter').on('click', function () {
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();
                loadData(start_date, end_date);
            });
            $('#btn-reset').on('click', function () {
                $('#form-filter')[0].reset();
                $('#form-filter #start_date').val("");
                $('#form-filter #end_date').val("");
                loadData("{{ date('Y-m-d') }}", "{{ date('Y-m-d') }}");
                $('.data-btn').removeClass('active');
                $('.today-btn').addClass('active');
            });

            function loadData(start_date, end_date) {
                $.get("{{ url('dashboard-data') }}/" + start_date + '/' + end_date, function (data) {
                    $('#stock_value').text((data.stock_value).toFixed(2) + 'Tk');
                    $('#delivered_orders').text((data.delivered_orders));
                    $('#product').text(data.product_count);
                    $('#avg_delivery_time').text((data.avg_delivery_time).toFixed(2) + 'Min');
                    $('#deadline_miss_percentage').text((data.deadline_miss_percentage).toFixed(2) + '%');
                    $('#total_customer').text(data.total_customer);
                    $('#total_visitor').text(data.total_visitor);
                    $('#customer_feedback').text(data.customer_feedback);
                    $('#customer').text(data.customer);
                    $('#sale').text((data.sale).toFixed(2) + 'Tk');
                    $('#purchase').text((data.purchase).toFixed(2) + 'Tk');
                    $('#ending_balance').text((data.closingBalance).toFixed(2) + 'Tk');
                    $('#current_balance').text((data.currentBalance).toFixed(2) + 'Tk');
                });
            }

            $(document).on('click', '.view-data', function () {
                $.ajax({
                    url: "{{url('dashboard-data/current-balance-data')}}", // You can append the ID and token as query parameters if needed
                    type: "GET", // Change POST to GET
                    success: function (data) {
                        $('#view_modal #view-data').html('');
                        $('#view_modal #view-data').html(data);
                        $('#view_modal').modal({
                            keyboard: false,
                            backdrop: 'true',
                        });
                        $('#view_modal .modal-title').html(
                            '<i class="fas fa-eye text-white"></i> <span>Cash In Cash Out Details</span>');
                    },
                    error: function (xhr, ajaxOption, thrownError) {
                        console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
                    }
                });
            });
        });
    </script>
@endpush
