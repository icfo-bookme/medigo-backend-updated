<!-- Start :: Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">

        <!-- Modal Content -->
        <div class="modal-content">

            <!-- Modal Header -->
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-white" id="model-title"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <i aria-hidden="true" class="ki ki-close text-white"></i>
                </button>
            </div>
            <!-- /modal header -->
            <form id="edit_form" method="post">
                @csrf
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <x-form.textbox labelName="Quantity" name="edit_qty" required="required" col="col-md-12" />
                        <x-form.textbox labelName="Unit Discount" name="edit_discount" col="col-md-12" />
                        <x-form.textbox labelName="Unit Price" name="edit_unit_price" col="col-md-12" readonly />
                        @php
                            $tax_name_all[] = 'No Tax';
                            $tax_rate_all[] = 0;
                            foreach ($taxes as $tax) {
                            $tax_name_all[] = $tax->name;
                            $tax_rate_all[] = $tax->rate;
                            }
                        @endphp
                        <div class="form-group col-md-12">
                            <label for="edit_tax_rate">Tax Rate</label>
                            <select name="edit_tax_rate" id="edit_tax_rate" class="form-control selectpicker">
                                @foreach ($tax_name_all as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="edit_unit">Product Unit</label>
                            <select name="edit_unit" id="edit_unit" class="form-control selectpicker"></select>
                        </div>
                    </div>
                </div>
                <!-- /modal body -->

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="update-btn">Update</button>
                </div>
                <!-- /modal footer -->
            </form>
        </div>
        <!-- /modal content -->

    </div>
</div>
<!-- End :: Edit Modal -->
