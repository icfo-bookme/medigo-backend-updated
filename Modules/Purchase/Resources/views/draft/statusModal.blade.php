<div class="modal fade" id="change_status_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h3 class="modal-title text-white" id="model-1"></h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><i aria-hidden="true" class="ki ki-close text-white"></i></button>
            </div>
            <form id="status_update_form" method="post">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <input type="hidden" name="draft_id" id="draft_id"/>
                        <x-form.selectbox labelName="{{__('file.Status')}}" name="status" col="col-md-12"
                                          class="selectpicker">
                            @foreach (DRAFT_STATUS as $key => $value)
                                <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                        </x-form.selectbox>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('file.Close')}}</button>
                    <button type="button" class="btn btn-primary btn-sm" id="savebtn"></button>
                </div>
            </form>
        </div>
    </div>
</div>
