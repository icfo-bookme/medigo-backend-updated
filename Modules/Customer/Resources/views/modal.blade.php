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
                        <x-form.textbox labelName="Name" name="name" required="required" col="col-md-4" placeholder="Enter name"/>
                        <x-form.textbox labelName="Phone" name="phone" col="col-md-4" required="required" placeholder="Enter phone number"/>
                        <x-form.textbox labelName="Email" name="email" col="col-md-4" placeholder="Enter email address"/>
                        <x-form.textbox labelName="Country" name="country" col="col-md-4" placeholder="Enter country name"/>
                        <x-form.textbox labelName="District" name="district" col="col-md-4" placeholder="Enter district name"/>
                        <x-form.textbox labelName="City" name="city" col="col-md-4" placeholder="Enter city name"/>
                        <x-form.textbox labelName="Thana" name="thana" col="col-md-4" placeholder="Enter thana name"/>
                        <x-form.textbox labelName="Area" name="area" col="col-md-4" placeholder="Enter area name"/>
                        <x-form.textarea labelName="Address" name="information" col="col-md-12" placeholder="Enter address"/>
                    </div>
                </div>
                <!-- /modal body -->

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="save-btn">Save</button>
                </div>
                <!-- /modal footer -->
            </form>
        </div>
        <!-- /modal content -->
    </div>
</div>
