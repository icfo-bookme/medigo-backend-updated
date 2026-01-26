@extends('layouts.app')

@section('title', $page_title)
@push('styles')
@endpush
@section('content')

<div class="d-flex flex-column-fluid">
    <div class="container-fluid">
        <!--begin::Notice-->
        <div class="card card-custom gutter-b">
            <div class="card-header flex-wrap py-5">
                <div class="card-title">
                    <h3 class="card-label"><i class="{{ $page_icon }} text-primary"></i> {{ $sub_title }}</h3>
                </div>
                <div class="card-toolbar">
                    <!--end::Button-->
                </div>
            </div>
        </div>
        <!--end::Notice-->
        <!--begin::Card-->
        <div class="card card-custom">
            <div class="card-body">
                <!--begin: Datatable-->
                <div id="kt_datatable_wrapper" class="dataTables_wrapper dt-bootstrap4 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <form action="{{ url('/workingDays/working-days/update/')}}" method="post">
                                @csrf
                                <div class="box-body">
                                    <!-- /.Notification Box -->
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            @foreach($working_days as $working_day)
                                                <label class="checkbox-inline">
                                                    @if($working_day['working_status'] == 1)
                                                        <input type="hidden" name="day[]" value="1"><input checked type="checkbox" >
                                                    @else
                                                        <input type="hidden" name="day[]" value="0"><input type="checkbox" >
                                                    @endif
                                                    <span>{{ $working_day['day'] }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary btn-flat"><i class="icon fa fa-edit"></i> {{ __('Update') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!--end: Datatable-->
            </div>
        </div>
        <!--end::Card-->
    </div>
</div>

@endsection

@push('scripts')

@endpush
