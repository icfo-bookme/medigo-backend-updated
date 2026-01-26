<script>
    load_products();

    function load_products(page = 1){
        var prescription_order_id = $('#prescription_order_id option:selected').val();

        $.ajax({
            url: "{{url('prescription-order/product-list')}}",
            type: "POST",
            data: {
                page: page,
                prescription_order_id: prescription_order_id,
                _token: _token
            },
            beforeSend: function() {
                $('#product-section').html('');
                $('#product_loading').removeClass('d-none');
            },
            complete: function() {
                $('#product_loading').addClass('d-none');
            },
            success: function(data) {
                $('#product-section').html(data);
            },
            error: function(xhr, ajaxOptions, thrownError) {
                console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }
</script>
