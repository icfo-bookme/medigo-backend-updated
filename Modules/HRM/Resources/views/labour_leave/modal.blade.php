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
                    <x-form.selectbox labelName="{{__('file.Leave Category')}}" name="leave_category" required="required" col="col-md-12" class="selectpicker">
                        @foreach($l_cat as $row)
                        <option value="{{$row->id}}">{{$row->leave_category}}</option>
                        @endforeach

                    </x-form.selectbox>
                    <x-form.selectbox labelName="{{__('file.Labour')}}" name="labour_id" required="required" col="col-md-12" class="selectpicker">
                        @foreach($labour as $row)
                            <option value="{{$row->id}}">{{$row->name}}</option>
                        @endforeach
                    </x-form.selectbox>
                    <div class="form-group col-md-12 required">
                        <label for="sale_date">{{__('file.Start Date')}}</label>
                        <input type="date" class="form-control date" id="start_date" name="start_date" required="required" value="{{date('Y-m-d')}}"/>
                    </div>
                    <div class="form-group col-md-12 required">
                        <label for="sale_date">{{__('file.Start Date')}}</label>
                        <input type="date" class="form-control date" id="end_date" name="end_date" required="required" value="{{date('Y-m-d')}}"/>
                    </div>
                    <x-form.textbox labelName="{{__('file.Leave Purpose')}}" name="purpose"  col="col-md-12" placeholder="Enter Leave Purpose"/>
                    <x-form.selectbox labelName="{{__('file.Status')}}" name="status" required="required" col="col-md-12" class="selectpicker">
                        <option value="1">Active</option>
                        <option value="2">Inactive</option>
                    </x-form.selectbox>
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
