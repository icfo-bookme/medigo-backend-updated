<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-white" id="model-1"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i aria-hidden="true" class="ki ki-close text-white"></i></button>
            </div>
            <form id="store_or_update_form" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="update_id" id="update_id"/>
                        <div class="form-group col-md-12 required">
                            <label for="draft_no">{{__('Draft No')}}</label>
                            <input type="text" name="draft_no" id="draft_no" class="form-control" readonly value="" placeholder="">
                        </div>
                        <x-form.textbox labelName="{{__('Amount')}}" name="amount" required="required" col="col-md-12" placeholder="{{__('Amount')}}"/>
                        <div class="form-group col-md-12">
                            <label for="description">{{__('Description')}}</label>
                            <textarea name="description" class="form-control" id="description" cols="10" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('file.Close')}}</button>
                    <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
                </div>
            </form>
        </div>
    </div>
</div>
