@if ($sale->user_activity->isEmpty())
    {{--    <p>No log history available.</p>--}}
    <table class="table table-bordered table-hover">
        <thead class="bg-primary">
        <tr class="text-center">
            <th>Activity Type</th>
            <th>User</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        <tr class="text-center text-danger text-capitalize">
            <td colspan="3">No Log History Available</td>
        </tr>
        </tbody>
    </table>
@else
    <table class="table table-bordered table-hover">
        <thead class="bg-primary">
        <tr class="text-center">
            <th>Activity Type</th>
            <th>User</th>
            <th>Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach($sale->user_activity as $activity)
            <tr class="text-center">
                <td><span class="label label-success label-pill label-inline" style="min-width:70px !important;">{{ $activity->status_name }}</span></td>
                <td>{{ $activity->user->name }}</td>
                <td>{{ $activity->created_at->format('Y-m-d H:i:s A') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
