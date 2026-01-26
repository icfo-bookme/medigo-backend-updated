<div class="form-group col-md-12">
    <label for="customer_id">Customer</label>
    <table class="table table-borderless customer">
        <tr>
            <td width="{{ permission('customer-add') ? '95%' : '100%' }}">
                <select class="form-control selectpicker" name="customer_id" id="customer_id" data-live-search="true"></select>
            </td>
            <td width="5%" class="text-right"><button type="button" class="btn btn-sm btn-primary" onclick="showFormModal('Add New Customer','Save')"> <i class="fas fa-plus-square"></i> </button></td>
        </tr>
    </table>

</div>

<div class="form-group col-md-12">
    <label for="product_code_name">Select Product</label>
    <div class="input-group mb-3">
        <div class="input-group-prepend">
            <span class="input-group-text" id="basic-addon1"><i class="fas fa-barcode"></i></span>
        </div>

        <input type="text" class="form-control" name="product_code_name" id="product_code_name"
               placeholder="Please Search ...">
    </div>
</div>

