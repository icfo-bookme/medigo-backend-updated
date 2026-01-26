<div class="modal fade" id="store_or_update_modal" tabindex="-1" role="dialog" aria-labelledby="model-1"
     aria-hidden="true">
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
                        <x-form.selectbox labelName="{{__('file.Employee')}}" name="employee_id" required="required"
                                          col="col-md-12" class="selectpicker">
                            @foreach($employees as $row)
                                <option value="{{$row->id}}">{{$row->name}}</option>
                            @endforeach
                        </x-form.selectbox>
                        <div class="form-group col-md-12 required">
                            <label for="start_date">{{__('file.Start Date')}}</label>
                            <input type="date" class="form-control date date-change" id="start_date" name="start_date"
                                   required="required" value="{{date('Y-m-d')}}" onkeyup="setHours()"
                                   onchange="setHours()"/>
                        </div>
                        <div class="form-group col-md-12 required">
                            <label for="start_time">{{__('file.start_time')}}</label>
                            <input type="time" name="start_time" id="start_time"
                                   class="form-control"
                                   value=""
                                   onkeyup="setHours()" onchange="setHours()">
                        </div>
                        <div class="form-group col-md-12 required">
                            <label for="end_date">{{__('file.End Date')}}</label>
                            <input type="date" class="form-control date date-change" id="end_date" name="end_date"
                                   required="required" value="{{date('Y-m-d')}}" onkeyup="setHours()"
                                   onchange="setHours()"/>
                        </div>
                        <div class="form-group col-md-12 required">
                            <label for="end_time">{{__('file.end_time')}}</label>
                            <input type="time" name="end_time" id="end_time"
                                   class="form-control"
                                   onkeyup="setHours()" onchange="setHours()">
                        </div>
                        <div class="form-group col-md-12 required">
                            <label for="duration">{{__('file.Duration')}}</label>
                            <input type="text" class="form-control" id="working_hour" name="working_hour"
                                   required="required" value="0" readonly/>
                        </div>

                        <x-form.textbox labelName="{{__('file.Note')}}" name="approve_remarks" col="col-md-12"
                                        placeholder="Enter Note"/>
                    </div>
                </div>
                <!-- /modal body -->

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger btn-sm"
                            data-dismiss="modal">{{__('file.Close')}}</button>
                    <button type="button" class="btn btn-primary btn-sm" id="save-btn"></button>
                </div>
                <!-- /modal footer -->
            </form>
        </div>
        <!-- /modal content -->

    </div>
</div>
