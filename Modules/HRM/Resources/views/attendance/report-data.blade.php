<table id="dataTable" class="table table-bordered table-hover">
    <thead class="bg-primary">
    <tr>
        <th>{{__('file.Employee Name')}}</th>
        <th>{{__('file.Designation')}}</th>
        <th>{{__('file.Department')}}</th>
        <th>{{__('file.Branch')}}</th>
        <th>{{__('file.Shift')}}</th>
        <th>{{__('file.Check In Date')}}</th>
        <th>{{__('file.Check In Time')}}</th>
        <th>{{__('file.Check Out Date')}}</th>
        <th>{{__('file.Check Out Time')}}</th>
        <th>{{__('file.Over Time(Hours)')}}</th>
        <th>{{__('file.Working Hour')}}</th>
        <th>{{__('file.approved_by')}}</th>
        <th>{{__('file.approval_status')}}</th>
        <th>{{__('file.Notes')}}</th>
    </tr>
    </thead>
    <tbody>
    @forelse($data as $key => $value)
        @php
            $overtime = \Modules\HRM\Entities\Overtime::where(['employee_id' => $value->employee_id, 'start_date' => $value->check_in_date])->groupBy('employee_id','start_date')->sum('working_hour');
        @endphp
        <tr>
            <td>{{ $value->employee->name }}</td>
            <td>{{ $value->employee->designation->name }}</td>
            <td>{{ $value->employee->department->name }}</td>
            <td>{{ $value->employee->branch->name }}</td>
            <td>{{ $value->shift->name }}</td>
            <td>{{ date("d-m-Y",strtotime($value->check_in_date)) }}</td>
            <td>{{ date("H:i:s a",strtotime($value->check_in_time)) }}</td>
            <td>{{ date("d-m-Y",strtotime($value->check_out_date)) }}</td>
            <td>{{ date("H:i:s a",strtotime($value->check_out_time)) }}</td>
            <td>{{ $overtime }}</td>

            <td>{{ $value->working_hour+$overtime }}</td>
            <td>{{ $value->approved_by }}</td>
            <td>{!! DAILY_ATTENDENCE_LABEL[$value->approval_status] !!}</td>
            <td>{{ $value->approve_remarks }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="14" class="text-danger">No data Found</td>
        </tr>
    @endforelse
    </tbody>
</table>
