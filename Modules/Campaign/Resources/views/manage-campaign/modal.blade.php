<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1"
     aria-hidden="true">
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
            <form id="store_or_update_form" method="post" enctype="multipart/form-data">
                @csrf
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="update_id" id="update_id"/>
                        <x-form.selectbox labelName="Campaign Type" name="campaign_type" required="required" col="col-md-6"
                                          class="selectpicker">
                            @foreach (CAMPAIGN_TYPE as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                        <x-form.textbox labelName="Campaign Name" name="name" required="required" col="col-md-6"
                                        placeholder="Enter campaign name" onkeyup="slugGenerator(this.value,'slug')"/>
                        <x-form.textbox labelName="Campaign Slug" name="slug" readonly="true" required="required" col="col-md-6" placeholder="Enter Slug "/>
                        <div class="form-group col-md-6 mb-0 text-center">
                            <label for="logo" class="form-control-label">Campaign Image</label>
                            <div class="col=md-12 px-0 text-center">
                                <div id="image">
                                </div>
                            </div>
                            <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB and Format (png,jpg,jpeg,svg,webp)</span></div>
                            <input type="hidden" name="old_image" id="old_image">
                        </div>
                        <x-form.date type="datetime-local" labelName="Campaign Start Date" name="start_date" col="col-md-6" required="required"/>
                        <x-form.date type="datetime-local" labelName="Campaign End Date" name="end_date" col="col-md-6" required="required"/>
                        <x-form.selectbox labelName="Discount Type" name="discount_type" required="required" col="col-md-6" class="selectpicker">
                            <option value="percentage">Percentage</option>
                            <option value="amount">Flat Amount</option>
                        </x-form.selectbox>
                        <x-form.textbox labelName="Discount Amount" name="discount_amount" required="required" col="col-md-6" placeholder="Enter discount amount"/>
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
