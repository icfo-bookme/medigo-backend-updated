<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Modules\HRM\Entities\DailyAttendance;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Entities\Shift;

class EmployeeAttendanceController extends BaseController
{
    public function __construct(DailyAttendance $model)
    {
        $this->model = $model;
    }
    public function index(){
        if(permission('employee-attendance-access')){
            $setTitle = __('file.Manage Employee Attendance');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $employees = Employee::where('activation_status', '1')->get();
            $shifts = Shift::where('status', '1')->get();
            return view('hrm::attendance.index',compact('employees', 'shifts'));
        }else{
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request) {
        if($request->ajax()){
            if(permission('employee-attendance-access')){

                if (!empty($request->employee_id)) {
                    $this->model->setEmployee($request->employee_id);
                }
                if (!empty($request->shift_id)) {
                    $this->model->setShift($request->shift_id);
                }
                if (!empty($request->from_date)) {
                    $this->model->setFromDate($request->from_date);
                }
                if (!empty($request->to_date)) {
                    $this->model->setToDate($request->to_date);
                }
                if (!empty($request->status)) {
                    $this->model->setStatus($request->status);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $in = date("d-m-Y H:i:s a", strtotime($value->check_in_date . ' ' . $value->check_in_time));
                    $out =  date("d-m-Y H:i:s a", strtotime($value->check_out_date . ' ' . $value->check_out_time));

                    $no++;
                    $action = '';
                    if(permission('employee-attendance-view')){
                        $action .= ' <a class="dropdown-item view-data" data-id="' . $value->id . '" data-name="' . $value->employee->name . '">'.$this->actionButton('View').'</a>';
                    }
                    if(permission('employee-attendance-change-status') && $value->approval_status == 1){
                        $action .= ' <a class="dropdown-item" href="'.route("empAttendance.approve", $value->id).'">'.$this->actionButton('Approve').'</a>';
                    }
                    $row    = [];
                    $row[]  = $no;
                    $row[]  = $value->employee->name;
                    $row[]  = $value->shift->name;
                    $row[]  = $in;
                    $row[]  = $out;
                    $row[]  = $value->working_hour;
                    $row[] = $value->approved_by ?? '<span class="label label-danger label-pill label-inline" style="min-width:70px !important;">Not Approved Yet</span>';
                    $row[]  = DAILY_ATTENDENCE_LABEL[$value->approval_status];
                    $row[]  = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'),$this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        }else{
            return response()->json($this->unauthorized());
        }
    }

    public function view(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-attendance-view')) {
                $data = $this->model->with('employee', 'shift')->findOrFail($request->id);
                return view('hrm::attendance.modal-data', compact('data'))->render();
            }
        }
    }
    public function approve($id)
    {
        if (permission('employee-attendance-change-status')) {
            $result = $this->model->find($id);
            $result->update([
                'approval_status' => '2',
                'approved_by' => auth()->user()->username,
            ]);
            $output = ['status' => 'success', 'message' => 'Attendance Approve Successful'];
        } else {
            $output = $this->unauthorized();
        }
        return redirect()->back()->with($output);
    }
}
