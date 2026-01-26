<table id="dataTable" class="table table-bordered table-hover">
    <thead class="bg-primary">
    <tr>
        <th>{{__('file.Employee ID')}}</th>
        <th>{{__('file.Employee Name')}}</th>
        <th>{{__('file.Designation')}}</th>
        <th>{{__('file.Department')}}</th>
        <th>{{__('file.Branch')}}</th>
        <th>{{__('file.Shift')}}</th>
        <th>{{__('file.Check In Date')}}</th>
        <th>{{__('file.Check In Time')}}</th>
        <th>{{__('file.Check Out Date')}}</th>
        <th>{{__('file.Check Out Time')}}</th>
        <th>{{__('file.Working Hour')}}</th>
        <th>{{__('file.Notes')}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($employees as $key => $emp)
        @php
            $existingData = \Modules\HRM\Entities\DailyAttendance::where(['employee_id' => $emp->id, 'check_in_date' => date("Y-m-d")])->first();
        @endphp
        <tr>
            <td>{{ $emp->employee_id }}</td>
            <td>{{ $emp->name }}</td>
            <td>{{ $emp->designation->name }}</td>
            <td>{{ $emp->department->name }}</td>
            <td>{{ $emp->branch->name }}</td>
            <td>{{ $emp->salary->shift->name }}</td>
            <td><input type="text" name="salary[{{$key}}][check_in_date]" id="salary_{{$key}}_check_in_date"
                       class="form-control"
                       readonly value="{{ date("Y-m-d") }}"></td>
            <td><input type="time" name="salary[{{$key}}][check_in_time]" id="salary_{{$key}}_check_in_time"
                       class="form-control"
                       value="{{ isset($existingData) ? $existingData->check_in_time : '' }}"
                       onkeyup="setHours({{$key}})" onchange="setHours({{$key}})"></td>
            <td><input type="date" name="salary[{{$key}}][check_out_date]" id="salary_{{$key}}_check_out_date"
                       class="form-control"
                       value="{{ isset($existingData) ? $existingData->check_out_date : '' }}"
                       onkeyup="setHours({{$key}})" onchange="setHours({{$key}})"></td>
            <td><input type="time" name="salary[{{$key}}][check_out_time]" id="salary_{{$key}}_check_out_time"
                       class="form-control"
                       value="{{ isset($existingData) ? $existingData->check_out_time : '' }}"
                       onkeyup="setHours({{$key}})" onchange="setHours({{$key}})"></td>
            <td><input type="text" name="salary[{{$key}}][working_hour]" id="salary_{{$key}}_working_hour"
                       class="form-control"
                       value="{{ isset($existingData) ? $existingData->working_hour : '0' }}" readonly></td>
            <td><input type="text" name="salary[{{$key}}][approve_remarks]" id="salary_{{$key}}_approve_remarks"
                       class="form-control"
                       value="{{ isset($existingData) ? $existingData->approve_remarks : '' }}">
                <input type="hidden" name="salary[{{$key}}][employee_id]" id="salary_{{$key}}_employee_id"
                       class="form-control"
                       value="{{ $emp->id }}">
                <input type="hidden" name="salary[{{$key}}][shift_id]" id="salary_{{$key}}_shift_id"
                       class="form-control"
                       value="{{ $emp->salary->shift_id }}">
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
<div class="form-group col-md-12 text-center pt-5">
    <button type="button" class="btn btn-primary btn-sm mr-3" id="save-btn"
            onclick="store_data()"><i class="fas fa-save"></i> Save
    </button>
</div>
