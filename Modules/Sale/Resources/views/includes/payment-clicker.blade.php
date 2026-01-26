<tr>
    <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Paid Amount:</th>
    <td colspan="4">

        <input type="text" class="form-control text-right" readonly value="0"
               name="paid_amount" id="paid_amount" placeholder="0.00">
    </td>
</tr>

<tr>
    <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Due Amount:</th>
    <td colspan="4">

        <input type="text" class="form-control text-right" readonly
               name="due_amount" id="due_amount" value="0" placeholder="0.00">
    </td>
</tr>

<tr>
    <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Payment Status:</th>
    <td colspan="4">
        <select class="form-control selectpicker" name="payment_status"
                data-live-search="true" id="payment_status" data-live-search-placeholder="Search" onchange="paymentMethodVisibility(this.value)">
            <option value="">Select Please</option>
            @foreach (PAYMENT_STATUS as $key => $value)
                <option value="{{ $key }}">{{ $value }}</option>
            @endforeach
        </select>
    </td>
</tr>
