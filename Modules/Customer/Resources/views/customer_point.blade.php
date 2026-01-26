<div class="modal fade" id="customer_point_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
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
            <form id="customer_point_form" method="post">
                @csrf
                <!-- Modal Body -->
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="customer_id" id="customer_id"/>
                        <x-form.textbox labelName="Available Point" name="available_point" required="required" col="col-md-12"/>
                        <x-form.textbox labelName="Conversion Rate" name="conversion_rate" col="col-md-12" required="required" placeholder="points/money"/>
                        <x-form.textbox labelName="Min. Redeem" name="min_use_point" col="col-md-12"/>
                    </div>
                </div>
                <!-- /modal body -->

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="set-point">Save</button>
                </div>
                <!-- /modal footer -->
            </form>
        </div>
        <!-- /modal content -->
    </div>
</div>
