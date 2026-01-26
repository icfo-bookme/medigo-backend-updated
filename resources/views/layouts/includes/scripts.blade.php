<script src="js/app.js" type="text/javascript"></script>
<script src="js/perfect-scrollbar.min.js"></script>
<script src="js/config.js" type="text/javascript"></script>
<script src="js/scripts.bundle.js" type="text/javascript"></script>
<script src="js/custom.js" type="text/javascript"></script>
<script src="{{ asset('js/datatables.bundle.js') }}" type="text/javascript"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="plugins/custom/datatables/datatables.bundle.js" type="text/javascript"></script>

<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<script>
    window.OneSignalDeferred = window.OneSignalDeferred || [];
    OneSignalDeferred.push(async function(OneSignal) {
        await OneSignal.init({
            appId: "04644f3f-4090-474f-a798-0f19659dbf71",
        });
    });
</script>

<script>
    var _token = "{{ csrf_token() }}";
    var $window = $(window);

    // :: Preloader Active Code
    $window.on('load', function () {
        $('#preloader').fadeOut('slow', function () {
            $(this).remove();
        });
    });


    $(document).ready(function () {

        table = $('#dataTable_noti').DataTable({
            "processing": true, //Feature control the processing indicator
            "serverSide": true, //Feature control DataTable server side processing mode
            "order": [], //Initial no order
            "responsive": false, //Make table responsive in mobile device
            "bInfo": false, //TO show the total number of data
            "bFilter": false, //For datatable default search box show/hide

            "lengthMenu": [
                [5, 10, 15, 25, 50, 100, 1000, 10000, -1],
                [5, 10, 15, 25, 50, 100, 1000, 10000, "All"]
            ],
            "pageLength": 10, //number of data show per page
            "language": {
                processing: `<i class="fas fa-spinner fa-spin fa-3x fa-fw text-primary"></i> `,
                emptyTable: '<strong class="text-danger">No Data Found</strong>',
                infoEmpty: '',
                zeroRecords: '<strong class="text-danger">No Data Found</strong>'
            },
            "ajax": {
                "url": "{{route('sale.notification.datatable.data')}}",
                "type": "POST",
                "data": function (data) {
                    data.bank_name = $("#form-filter #bank_name").val();
                    data.account_name = $("#form-filter #account_name").val();
                    data.account_number = $("#form-filter #account_number").val();
                    data._token = _token;
                }
            },
            "columnDefs": [
                {
                    "targets": [0],
                    "orderable": false,
                    "className": "text-center"
                }
            ],
        });

        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('ab3dc64fd6c06427d672', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event', function (data) {
            $('.new_sale_counter').text(data.message);
            $('.total-alert-qty-badge').text(data.notifications_count);

            sale_notification_list();
        });

        sale_notification_list();


        function sale_notification_list() {
            $.get("{{ url('sale/sale_notification_list') }}", function (data) {
                if (data.notifications_count) {
                    table.ajax.reload();
                }

                $('.new_sale_counter').text(data?.new_sale_count);
                $('.total-alert-qty-badge').text(data?.notifications_count);
            });
        }

        $('.notification_clicker').click(function () {
            $.post("{{ url('sale/sale_notification/update-list') }}",
                {
                    _token: _token
                },
                function (data) {
                    console.log(data);
                });
        });

        material_stock_alert();

        function material_stock_alert() {
            {{--$.get("{{ url('product-stock-notification') }}", function (data) {--}}
            {{--    if (data.count > 0) {--}}
            {{--        $('.total-alert-qty-badge').removeClass('d-none');--}}
            {{--        $('.total-alert-qty-badge').text(data.count);--}}
            {{--        $('#total-alert-qty').text(data.count);--}}

            {{--        let alert_html = data.output;--}}
            {{--        $('#material-stock-alert').empty().html(alert_html);--}}
            {{--    } else {--}}
            {{--        $('.total-alert-qty-badge').addClass('d-none');--}}
            {{--        $('.total-alert-qty-badge').text('');--}}
            {{--        $('#total-alert-qty').text('');--}}
            {{--        $('#material-stock-alert').empty().html(`<div class="p-8 text-center font-weight-bolder">All caught up!<br>No new notifications.</div>`);--}}
            {{--    }--}}
            {{--});--}}
        }

        <?php
        if (session('status')){
            ?>
        notification("{{session('status')}}", "{{session('message')}}");
            <?php
        }
        ?>
        <?php
        if (session('success')){
            ?>
        notification("success", "{{session('success')}}");
            <?php
        }
        ?>
        <?php
        if (session('error')){
            ?>
        notification("error", "{{session('error')}}");
            <?php
        }
        ?>
    });
</script>
@stack('scripts') <!-- Load Scripts Dynamically -->
