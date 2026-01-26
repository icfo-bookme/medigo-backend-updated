<script>
    function paymentMethodVisibility(input_value) {

        if (input_value != 3) {

            $('#payment_method *').prop('disabled', false);
            $('.payment_method_flag').show();
        } else {

            $('#payment_method *').prop('disabled', true);
            $('.payment_method_flag').hide();
        }
    }

    $('.payment_amounts').on('input', function () {
        var value = $(this).val();


        if ((value !== '') && (value.indexOf('.') === -1)) {
            let payable_amount = parseFloat($('input[name="net_total"]').val());
            let paid_amount = $('#paid_amount').val() ? parseFloat($('#paid_amount').val()) : 0;
            let ongoing = (payable_amount - paid_amount).toFixed(2);
            if (value > payable_amount || value < 0) {
                multiple_notification('error', 'The Payment Can not grater than payable amount');
            }
        }
    });

    let table_row = 0;

    function removeRow(element, idx) {
        $('#' + element + idx).closest('div').remove();
        table_row--;
        calculatePaymentAmount();
        // calculateTotalCredit();
    }

    function addTableRow(tableId, idx) {
        table_row = idx;
        ++table_row;
        // Assuming PAYMENT_STATUS is a JavaScript array or object
        let paymentOptions = [
                @foreach (SALE_PAYMENT_METHOD as $key => $value)
            {
                key: '{{ $key }}', value: '{{ $value }}'
            },
            @endforeach
        ];

        let selectOptions = '';
        paymentOptions.forEach(option => {
            selectOptions += `<option value="${option.key}" data-reference_id="${table_row}" >${option.value}</option>`;
        });

        let html = `
               <div class="row col-md-12" id="payment_method_tr_${table_row}">
        <div class="form-group col-md-3">
            <label>Payment Method</label>
            <select class="form-control selectpicker"
                data-live-search="true"
                 name="payment[${table_row}][payment_method]"
                 id="payment_${table_row}_payment_method"
                onchange="account_list(this.value,${table_row})"
                 data-reference_id="${table_row}"
                data-live-search-placeholder="Search">
                <option value="">Select Please</option>
                ${selectOptions}
            </select>
        </div>
            <div class="form-group col-md-3">
                    <label>Account</label>
                    <select class="form-control  selectpicker"
                          id="payment_${table_row}_account_id"
                                data-live-search="true"
                     name="payment[${table_row}][account_id]"
                                 data-live-search-placeholder="Search">
                        <option value="">Select Please</option>
                    </select>
                </div>
                <div class="form-group col-md-3  d-none reference_no_${table_row}"
                    <label for="reference_no">Reference No</label>
                    <input type="text" class="fcs form-control"
                       name="payment[${table_row}][reference_no]"
                        id="payment_${table_row}_reference_no" >
                </div>

                <div class="form-group col-md-2 ">
                <label for="payment_amounts">Amount</label>
                <input type="number" class="fcs form-control payment_amounts"
                        oninput="calculatePaymentAmount(this.value)"
                       name="payment[${table_row}][payment_amount]"
                       id="payment_${table_row}_payment_amount" >
                </div>
            <div
            class="mb-2 d-flex flex-column justify-content-center align-items-center">
            <button type="button" class="btn btn-danger btn-sm"
            onclick="removeRow('payment_method_tr_','${table_row}')">
            <i class="fas fa-minus"></i>
            </button>
            <button type="button" class="btn btn-success btn-sm"
            onclick="addTableRow('payment_method_flag','${table_row}')">
            <i class="fas fa-plus"></i>
            </button>
            </div>
            </div>
    `;

        // Append the new row to the table or container with the specified ID
        $('#' + tableId).append(html);

        // Refresh the selectpicker to apply Bootstrap-select styling
        $('#' + tableId + ' .selectpicker').selectpicker('refresh');
    }

    function account_list(payment_method, idx, account_idx = null) {


        $.ajax({
            url: "{{ route('account.list') }}",
            type: "POST",
            data: {
                payment_method: payment_method,
                account_id: account_idx,
                _token: _token
            },
            success: function (data) {
                $('#payment_' + idx + '_account_id').empty().html(data);
                $('#payment_' + idx + '_account_id').selectpicker('refresh');

                if (payment_method != 1) {
                    $(`.reference_no_${idx}`).removeClass('d-none').addClass('pt-1'); // You can change 'pt-4' to your desired padding class
                } else {
                    $(`.reference_no_${idx}`).addClass('d-none');
                }
            },
            error: function (xhr, ajaxOption, thrownError) {
                console.log(thrownError + '\r\n' + xhr.statusText + '\r\n' + xhr.responseText);
            }
        });
    }



    function calculatePaymentAmount(value = 0) {
        let totals = 0;
        $(".payment_amounts").each(function () {
            var inputValue = $(this).val() > 0 ? $(this).val() : 0;
            if (!isNaN(inputValue)) {
                totals += parseFloat(inputValue);
            }
        });

        if (!isNaN(totals)) {
            var payable_amount = parseFloat($('input[name="grand_total"]').val());
            let dues = (payable_amount - totals).toFixed(2);
            if (dues >= 0) {
                $('#paid_amount').val(totals);
                $('#due_amount').val(dues);
            }else{
                notification('error','Not Possible to Take Extra Payment');
            }
        }
    }


</script>
