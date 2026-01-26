<div class="col-md-12">
    <div class="row col-md-12 pt-15 payment_method_flag" style="display: none;" id="payment_method_flag">
        <div class="row col-md-12" id="payment_method_tr_0">
            <div class="form-group col-md-3">
                <label>Payment Method</label>
                <select class="form-control selectpicker" name="payment[0][payment_method]"
                        onchange="account_list(this.value,0)" id="payment_0_payment_method"
                        data-live-search="true" data-live-search-placeholder="Search">
                    <option value="">Select Please</option>
                    @foreach (SALE_PAYMENT_METHOD as $key => $value)
                        <option value="{{ $key }}" data-reference_id="0">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-3">
                <label>Account</label>
                <select class="form-control selectpicker" name="payment[0][account_id]"
                        id="payment_0_account_id" data-live-search="true"
                        data-live-search-placeholder="Search">
                    <option value="">Select Please</option>
                </select>
            </div>

            <div class="form-group col-md-3 d-none  reference_no_0">
                <label for="reference_no">Reference No</label>
                <input type="text" class="fcs form-control" name="payment[0][reference_no]"
                       id="payment_0_reference_no">
            </div>


            <div class="form-group col-md-2">
                <label for="reference_no">Amount</label>
                <input type="number" class="fcs form-control payment_amounts"
                       oninput="calculatePaymentAmount(this.value)"
                       name="payment[0][payment_amount]" id="payment_0_payment_amount">
            </div>

            <div
                class=" d-flex flex-column justify-content-center align-items-center">
                <button type="button" class="btn btn-success btn-sm" onclick="addTableRow('payment_method_flag',0)"><i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
</div>
