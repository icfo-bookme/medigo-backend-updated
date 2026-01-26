<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <!-- Modal Content -->
      <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header bg-primary">
          <h3 class="modal-title text-white" id="model-1"></h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <i aria-hidden="true" class="ki ki-close text-white"></i>
          </button>
        </div>
        <!-- /modal header -->
        <form id="store_or_update_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="update_id" id="update_id"/>
                    <x-form.textbox labelName="Coupon" name="name" required="required" col="col-md-6" placeholder="Enter Coupon"/>
                    <div class="form-group col-md-6 required">
                        <label for="department_id">Coupon Type</label>
                        <select class="form-control selectpicker" id="coupon_type" name="coupon_type" required="required" >
                            <option value="1">General Coupon</option>
                            <option value="2">Category Coupon</option>
                            <option value="3">Customer Coupon</option>
                        </select>
                    </div>
                    <div class="form-group col-md-4 required">
                        <label for="department_id">Discount Type</label>
                        <select class="form-control selectpicker departmentUser type" id="type" name="type" required="required" >
                            <option value="">Select</option>
                            <option value="1">Fixed</option>
                            <option value="2">Percentage</option>
                        </select>
                    </div>
                    <x-form.textbox labelName="Coupon Value" name="value" required="required" col="col-md-4" placeholder="Enter Coupon value"/>
                    <x-form.textbox labelName="Coupon Value Limitation" name="coupon_value_limit" required="required" col="col-md-4" placeholder="Enter Coupon value"/>
                    <x-form.date type="datetime-local" labelName="Coupon Start Date" name="start_date" col="col-md-4" required="required"/>
                    <x-form.date type="datetime-local" labelName="Coupon End Date" name="end_date" col="col-md-4" required="required"/>
                    <div class="form-group col-md-4 required">
                        <label for="department_id">Status</label>
                        <select class="form-control selectpicker departmentUser status" id="status" name="status" required="required" >
                            <option value="">Select</option>
                            <option value="1">Active</option>
                            <option value="2">Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>
