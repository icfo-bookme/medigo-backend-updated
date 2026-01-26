<div class="col-md-12">
    <table class="table table-bordered" id="product_table">
        <thead class="bg-primary">
        <th>Name</th>
        <th class="text-center">Code</th>
        <th class="text-center">Serial No</th>
        <th class="text-center">Unit</th>
        <th class="text-center">Stock(QTY)</th>
        <th class="text-center">Qty</th>
        <th class="text-center">Unit Price</th>
        <th class="text-center">Discount(%)</th>
        <th class="text-center">Discount(TK)</th>
        <th class="text-center">Net Price</th>
        <th></th>
        </thead>
        <tbody>
        </tbody>
        <tfoot>
        <tr class="bg-primary">
            <th colspan="5" class="font-weight-bolder">Total</th>
            <th  class="text-center font-weight-bolder">
                <input type="text" class="form-control text-center"  name="total_qty" id="total-qty" style="background-color: #003f7b; color: white;" readonly
                       value="{{ isset($sale_data) ? $sale_data['sale']['total_qty']: '0' }}" />
            </th>
            <th></th>
            <th class="text-right font-weight-bolder">
{{--                <input type="text" class="form-control text-center"  id="total-discount_rate" name="total_discount_rate" style="background-color: #003f7b; color: white;" readonly--}}
{{--                       value="{{--}}
{{--                isset--}}
{{--                ($sale_data) ?--}}
{{--                $sale_data['sale']['total_discount_rate']:--}}
{{--                 '0.00'--}}
{{--            }}" />--}}
            </th>
{{--            <th id="total-tax" class="text-right font-weight-bolder">{{ isset($sale_data) ? $sale_data['sale']['total_tax']: '0.00' }}</th>--}}
            <th class="text-center font-weight-bolder">
{{--                <input type="text" id="total-discount" class="form-control text-center" name="total_discount" style="background-color: #003f7b; color: white;" readonly--}}
{{--                       value="{{--}}
{{--                isset($sale_data) ?--}}
{{--                $sale_data['sale']['total_discount']: '0.00' }} " />--}}

            </th>
            <th class="text-center font-weight-bolder">
                <input type="text" id="total_price" name="total_price" class="form-control text-center" style="background-color: #003f7b; color: white;" readonly
                       value="
                {{ isset($sale_data) ? $sale_data['sale']['total_price']: '0.00' }}" />

               </th>
            <th></th>
        </tr>
{{--        <tr>--}}
{{--            <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Order Tax: </th>--}}
{{--            <td colspan="4">--}}
{{--                <x-form.selectbox labelName="" name="order_tax_rate" class="selectpicker">--}}
{{--                    <option value="0" selected>No Tax</option>--}}
{{--                    @if (!$taxes->isEmpty())--}}
{{--                        @foreach ($taxes as $tax)--}}
{{--                            <option value="{{ $tax->rate }}" @if(isset($sale_data)) {{ ($sale_data['sale']['order_tax_rate'] == $tax->rate) ? 'selected' : ''}} @endif>{{ $tax->name }}</option>--}}
{{--                        @endforeach--}}
{{--                    @endif--}}
{{--                </x-form.selectbox>--}}
{{--            </td>--}}
{{--        </tr>--}}
        <tr>
            <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Order Discount:
{{--                <input placeholder="%"--}}
{{--                       name="order_discount_per"--}}
{{--                       id="order_discount_per" checked--}}
{{--                       value="1" type="checkbox"/>--}}
                 <span>(% / TK)</span>
            </th>
            <td colspan="2">
                <input type="text"
                       oninput="orderDiscountCalculator(this.value, 'percentage')"
                       class="form-control" placeholder="0.00 %" step="0.01" name="order_discount_rate" id="order_discount_rate"   value="0">
            </td>
            <td colspan="2">
                <input type="text" class="form-control order_discount" id="order_discount"
                       oninput="orderDiscountCalculator(this.value, 'amount')" step="0.01"
                       placeholder="0.00" name="order_discount"  value="0">
            </td>

        </tr>
        <tr>
            <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Shipping Cost: </th>
            <td colspan="4">
                <input type="text" class="form-control" name="shipping_cost" id="shipping_cost" value="{{ isset($sale_data) ? $sale_data['sale']['shipping_cost'] : '' }}">
                <input type="hidden" class="form-control" name="payment_status" id="payment_status" value="1" />
                <input type="hidden" class="form-control" name="payment_method" id="payment_method" value="1">
            </td>
        </tr>
{{--        <tr>--}}
{{--            <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Adjustment: <input placeholder="-" name="adjustment_per" id="adjustment_per" type="checkbox" /><label for="adjustment_per">(-)</label> </th>--}}
{{--            <td colspan="4">--}}
{{--                <input type="text" class="form-control" placeholder="Adjustment" name="adjustment" id="adjustment" value="{{ isset($sale_data) ? $sale_data['sale']['order_discount'] : '' }}">--}}
{{--            </td>--}}
{{--        </tr>--}}
        <tr>
            <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Order Source: </th>
            <td colspan="4">
                <x-form.selectbox labelName="" name="order_source_id" class="selectpicker">
                    @foreach (ORDER_SOURCE as $key => $order_source)
                        <option value="{{ $key }}"
                        @if(isset($sale_data)) {{ ($sale_data['sale']['order_source_id'] == $key) ? 'selected' : ''}} @endif
                        @if($order_source === 'POS') selected @endif
                        >{{ $order_source }}</option>
                    @endforeach
                </x-form.selectbox>
            </td>
        </tr>

        <tr>
            <th colspan="7" style="color: #000 !important;border: none;" class="text-right font-weight-bolder">Grand Total: </th>
            <td colspan="4">
                <input type="text" class="form-control text-right" id="grand_total" name="grand_total" value="{{ isset($sale_data) ? $sale_data['sale']['grand_total']:
                0 }}">
            </td>
        </tr>

        @include('sale::includes.payment-clicker')

        </tfoot>
    </table>
</div>

