<div class="modal fade" id="approve_status_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
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
            <!-- Modal Body -->
            <form id="approve_status_form" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="status_id" id="status_id">


                    <x-form.selectbox labelName="Status" name="status" col="col-md-12" class="selectpicker">
                        @foreach (PRESCRIPTION_POS_STATUS as $key => $value)
                            <option value="{{ $key }}">{{ $value }}</option>
                        @endforeach
                    </x-form.selectbox>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('Close')}}</button>
                    <button type="button" class="btn btn-primary btn-sm" id="status-btn"></button>
                </div>
            </form>
            <!-- /modal body -->
        </div>
        <!-- /modal content -->
    </div>
</div>
