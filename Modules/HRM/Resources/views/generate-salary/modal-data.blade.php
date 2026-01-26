<div class="col-md-9">
    <div class="table-responsive">
        <table class="table table-borderless">
            <tr>
                <td><b>Employee</b></td>
                <td><b>:</b></td>
                <td>{{ $data->employee->name }}</td>

                <td><b>Shift</b></td>
                <td><b>:</b></td>
                <td>{{ $data->shift->name }}</td>
            </tr>
            <tr>
                <td><b>Month</b></td>
                <td><b>:</b></td>
                <td>{{ date("F", mktime(0, 0, 0, $data->month, 1)) }}</td>
                <td><b>Year</b></td>
                <td><b>:</b></td>
                <td>{{ $data->year }}</td>
            </tr>

            <tr>
                <td><b>Total Working Days</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_working_days }}</td>

                <td><b>Total Holidays</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_holidays }}</td>
            </tr>
            <tr>
                <td><b>Total Attended</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_attended }}</td>
                <td><b>Total Paid Leave</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_paid_leaves }}</td>
            </tr>
            <tr>
                <td><b>Total Unpaid Leave</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_unpaid_leaves }}</td>
                <td><b>Total Working Days</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_working_hours }}</td>
            </tr>
            <tr>
                <td><b>Total Attended Hours</b></td>
                <td><b>:</b></td>
                <td>{{ $data->total_attended_hours }}</td>
                <td><b>Current Salary</b></td>
                <td><b>:</b></td>
                <td>{{ $data->current_salary }}</td>
            </tr>
            <tr>
                <td><b>Increment</b></td>
                <td><b>:</b></td>
                <td>{{ $data->increment_salary }}</td>
                <td><b>Gross Salary</b></td>
                <td><b>:</b></td>
                <td>{{ $data->gross_salary }}</td>
            </tr>
            <tr>
                <td><b>Net Salary</b></td>
                <td><b>:</b></td>
                <td>{{ $data->net_salary }}</td>
                <td><b>Status</b></td>
                <td><b>:</b></td>
                <td>{!! LEAVE_STATUS_LABEL[$data->status] !!} </td>
            </tr>
            <tr>
                <td><b>Created By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->created_by }}</td>
                <td><b>Approved By</b></td>
                <td><b>:</b></td>
                <td>{{ $data->approved_by ?? '' }}</td>
            </tr>

        </table>
    </div>
</div>
