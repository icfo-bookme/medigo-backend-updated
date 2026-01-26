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
                        <x-form.selectbox labelName="Coupon" name="coupon_id" required="required" col="col-md-6"
                                          class="selectpicker" onchange="getCoupon(this.value)">
                            @foreach ($coupons as $c_key => $c_value)
                                <option value="{{ $c_key }}">{{ $c_value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <div class="form-group col-md-6 required">
                            <label for="type">Discount Type</label>
                            <select class="form-control selectpicker type" id="type" name="type" required="required">
                                <option value="">Select</option>
                                <option value="1">Fixed</option>
                                <option value="2">Percentage</option>
                            </select>
                        </div>
                        <x-form.textbox labelName="Coupon Value" name="value" required="required" col="col-md-6" placeholder="Enter Coupon value"/>
                        <x-form.date type="datetime-local" labelName="Campaign Start Date" name="start_date" col="col-md-6" required="required"/>
                        <x-form.date type="datetime-local" labelName="Campaign End Date" name="end_date" col="col-md-6" required="required"/>
                        <div class="form-group col-md-12">
                            <label class="col-from-label ml-4">Category<span class="text-danger">*</span></label>
                            <div class="col-md-12">
                                <select class="js-example-basic-multiple" name="category_id[]" id="category_id" data-live-search="true" required multiple>
                                    @foreach ($categories as $cat_key => $category)
                                        <option value="{{ $cat_key }}">{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
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
