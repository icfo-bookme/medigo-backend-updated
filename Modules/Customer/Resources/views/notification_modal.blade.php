<div class="modal fade" id="notification_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <!-- Modal Content -->
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-white" id="model-1"></h3>
            </div>
            <!-- /modal header -->
            <!-- Modal Body -->
            <form id="notification_form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="selected_ids" id="selected_ids"/>
                        <x-form.textbox labelName="Headings" name="headings" required="required" col="col-md-12"/>
                        {{-- <x-form.textbox labelName="URL" name="url" required="required" col="col-md-6"/> --}}
                        {{-- <div class="form-group col-md-6 mb-0 text-center">
                            <label for="logo" class="form-control-label">Image</label>
                            <div class="col=md-12 px-0 text-center">
                                <div id="image">
                                </div>
                            </div>
                            <div class="text-center"><span class="text-muted">Maximum Allowed File Size 2MB
                                            and Format (png,jpg,jpeg,svg,webp)</span></div>
                            <input type="hidden" name="old_image" id="old_image">
                        </div> --}}
                        <x-form.textarea labelName="Message" name="message" required="required" col="col-md-12"/>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm" id="send-noti-btn"><i class="fas fa-paper-plane mr-1"></i>Send</button>
                </div>
            </form>
            <!-- /modal body -->
        </div>
        <!-- /modal content -->
    </div>
</div>
