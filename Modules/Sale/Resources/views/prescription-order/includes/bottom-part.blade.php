<div class="col-md-12">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-bordered">
                <thead class="bg-primary">
                <th><strong>Items</strong><span class="float-right" id="item">{{ isset($sale_data) ? $sale_data['sale']['item'].'('.$sale_data['sale']['total_qty'].')': '0.00' }}</span></th>
                <th><strong>Total</strong><span class="float-right" id="subtotal">{{ isset($sale_data) ? $sale_data['sale']['total_price']: '0.00' }}</span></th>
                <th><strong>Order Tax</strong><span class="float-right" id="order_total_tax">{{ isset($sale_data) ? $sale_data['sale']['order_tax']: '0.00' }}</span></th>
                <th><strong>Order Discount</strong><span class="float-right" id="order_total_discount">{{ isset($sale_data) ? $sale_data['sale']['total_discount']: '0.00' }}</span></th>
                <th><strong>Shipping Cost</strong><span class="float-right" id="shipping_total_cost">{{ isset($sale_data) ? $sale_data['sale']['shipping_cost']: '0.00' }}</span></th>
                <th><strong>Grand Total</strong><span class="float-right" id="grand_total">{{ isset($sale_data) ? $sale_data['sale']['grand_total']: '0.00' }}</span></th>
                </thead>
            </table>
        </div>
        <div class="col-md-12">
            <input type="hidden" name="total_qty" value="{{ isset($sale_data) ? $sale_data['sale']['total_qty']: '' }}">
            <input type="hidden" name="total_discount" value="{{ isset($sale_data) ? $sale_data['sale']['total_discount']: '' }}">
            <input type="hidden" name="total_tax" value="{{ isset($sale_data) ? $sale_data['sale']['total_tax']: '' }}">
            <input type="hidden" name="net_total" value="{{ isset($sale_data) ? $sale_data['sale']['total_tax']: '' }}">
            <input type="hidden" name="total_price" value="{{ isset($sale_data) ? $sale_data['sale']['total_price']: '' }}">
            <input type="hidden" name="item" value="{{ isset($sale_data) ? $sale_data['sale']['item']: '' }}">
            <input type="hidden" name="order_tax" value="{{ isset($sale_data) ? $sale_data['sale']['order_tax']: '' }}">

        </div>


        @include('sale::includes.payment-method-html')

        <div class="form-group col-md-12 text-center pt-5">
            <button type="button" class="btn btn-danger btn-sm mr-3" onClick="refreshPage()"><i class="fas fa-sync-alt"></i> Reset</button>
            <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn" onclick="store_data()"><i class="fas fa-save"></i> Save</button>
        </div>
    </div>
</div>
