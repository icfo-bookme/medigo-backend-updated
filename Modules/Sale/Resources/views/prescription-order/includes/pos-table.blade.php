<div class="form-group col-md-12">
    <label for="customer_id">Customer</label>
    <table class="table table-borderless customer">
        <tr>
            <td width="{{ permission('customer-add') ? '95%' : '100%' }}">
                <select class="form-control selectpicker" name="customer_id" id="customer_id" data-live-search="true"></select>
            </td>
            <td width="5%" class="text-right">
                <button type="button" class="btn btn-sm btn-primary" onclick="showFormModal('Add New Customer','Save')"><i class="fas fa-plus-square"></i></button>
            </td>
        </tr>
    </table>
</div>

<div class="form-group col-md-12">
    <label for="product_code_name">Select Product</label>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
        </div>

        <input type="text" class="form-control" name="product_code_name" id="product_code_name" placeholder="Please Search ...">
    </div>
</div>

<div class="col-md-12">
    <table class="table table-bordered" id="product_table">
        <thead class="bg-primary text-center">
        <th>Name</th>
{{--        <th>Code</th>--}}
        <th>Serial No.</th>
        <th>Unit</th>
        <th>Stock Qty</th>
        <th>Qty</th>
        <th>Unit Price</th>
        <th>Discount(%)</th>
        <th>Discount(TK)</th>
        <th>Subtotal</th>
        <th></th>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr class="bg-primary">
            <th colspan="5" class="font-weight-bolder">Total</th>
            <th>
                <input type="text" class="form-control text-center" name="total_qty" id="total-qty" style="background-color: #003f7b; color: white;" value="0" readonly/>
            </th>
            <th></th>
            <th></th>
            <th class="text-center">
                <input type="text" id="total_price" name="total_price" class="form-control text-center" style="background-color: #003f7b; color: white;" value="0" readonly/>
            </th>
            <th></th>
        </tr>

        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Order Discount:
                <span>(% / TK)</span>
            </th>
            <td colspan="2">
                <input type="text" oninput="orderDiscountCalculator(this.value, 'percentage')" class="form-control" placeholder="0.00 %" step="0.01" name="order_discount_rate"
                       id="order_discount_rate" value="0">
            </td>
            <td colspan="2">
                <input type="text" class="form-control order_discount" id="order_discount" oninput="orderDiscountCalculator(this.value, 'amount')" step="0.01" placeholder="0.00"
                       name="order_discount" value="0">
            </td>
        </tr>

        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Shipping Cost:</th>
            <td colspan="4">
                <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="">
                <input type="hidden" class="form-control" name="payment_status" id="payment_status" value="1"/>
                <input type="hidden" class="form-control" name="payment_method" id="payment_method" value="1">
            </td>
        </tr>
        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Order Source:</th>
            <td colspan="4">
                <x-form.selectbox labelName="" name="order_source_id" class="selectpicker">
                    @foreach (ORDER_SOURCE as $key => $order_source)
                        <option value="{{ $key }}" @if(isset($sale_data))
                            {{ ($sale_data['sale']['order_source_id'] == $key) ? 'selected' : ''}}
                        @endif
                        @if($key == 5) selected @endif
                        >{{ $order_source }}</option>
                    @endforeach
                </x-form.selectbox>
            </td>
        </tr>

        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Grand Total:</th>
            <td colspan="4">
                <input type="text" class="form-control text-right" name="grand_total" value="{{ isset($sale_data) ? $sale_data['sale']['grand_total'] : 0 }}">
            </td>
        </tr>

        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Paid Amount:</th>
            <td colspan="4">
                <input type="text" class="form-control text-right" value="0" name="paid_amount" id="paid_amount" placeholder="0.00" readonly>
            </td>
        </tr>

        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Due Amount:</th>
            <td colspan="4">
                <input type="text" class="form-control text-right" name="due_amount" id="due_amount" value="0" placeholder="0.00" readonly>
            </td>
        </tr>

        <tr>
            <th colspan="6" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Payment Status:</th>
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
        </tfoot>
    </table>
</div>

<div class="col-md-12">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered">
                <thead class="bg-primary d-none">
                <th><strong>Items</strong><span class="float-right" id="item">{{ '0.00' }}</span></th>
                <th><strong>Total</strong><span class="float-right" id="subtotal">{{ '0.00' }}</span></th>
                <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">{{ '0.00' }}</span></th>
                <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">{{ '0.00' }}</span>
                </th>
                <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">{{ '0.00' }}</span>
                </th>
                <th><strong>Grand Total</strong><span class="float-right" id="grand_total">{{ '0.00' }}</span></th>
                </thead>
            </table>
        </div>
        <div class="col-md-12">
            <input type="hidden" name="total_qty" value="">
            <input type="hidden" name="total_discount" value="">
            <input type="hidden" name="total_tax" value="">
            <input type="hidden" name="net_total" value="">
            <input type="hidden" name="total_price" value="">
            <input type="hidden" name="item" value="">
            <input type="hidden" name="order_tax" value="">
        </div>


        @include('sale::includes.payment-method-html')

        <div class="form-group col-md-12 text-center pt-5">
            <button type="button" class="btn btn-danger btn-sm mr-3" onClick="refreshPage()"><i class="fas fa-sync-alt"></i> Reset</button>
            <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>

