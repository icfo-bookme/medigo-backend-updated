<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1" aria-hidden="true">
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
        <form id="store_or_update_form" method="post">
          @csrf
            <!-- Modal Body -->
            <div class="modal-body">
                <div class="row">
                    <input type="hidden" name="update_id" id="update_id"/>
                    <x-form.selectbox labelName="{{__('file.Employee Name')}}" name="employee_id" required="required" col="col-md-12" class="selectpicker">
                        @foreach ($employee as $key => $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </x-form.selectbox>
                    <div class="col-md-12">
                        <label for="salary_month" class="col-sm-12 control-label">{{ __('file.Select Month') }}</label>
                        <input type="text" class="form-control" name="date" id="datepicker" />
                    </div>
                    <div class="col-md-12">
                        <label for="salary_month" class="col-sm-12 control-label">{{ __('file.Increment Amount') }}</label>
                        <input type="text" class="form-control" name="amount" id="amount" />
                    </div>

                    <x-form.textbox labelName="{{__('file.Increment Purpose')}}" name="incr_purpose"  col="col-md-12" placeholder="Enter Increment Purpose"/>
                </div>
            </div>
            <!-- /modal body -->

            <!-- Modal Footer -->
            <div class="modal-footer">
            <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">{{__('file.Close')}}</button>
            <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
            </div>
            <!-- /modal footer -->
        </form>
      </div>
      <!-- /modal content -->

    </div>
  </div>
