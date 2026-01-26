<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\HRM\Entities\DailyAttendance;
use Modules\HRM\Entities\Employee;

class AttendanceController extends BaseController
{
    public function index()
    {
        if (permission('attendance-add') || permission('attendance-edit')) {
            $setTitle = __('file.Daily Attendance');
            $this->setPageData($setTitle, $setTitle, 'fas fa-user-plus', [['name' => $setTitle]]);
            $employees = Employee::where('activation_status', '1')->get();
            return view('hrm::attendance.employee.index', compact('employees'));
        } else {
            return $this->access_blocked();
        }
    }

    public function getEmployees(Request $request)
    {
        if ($request->ajax()) {
            if (permission('attendance-add') || permission('attendance-edit')) {
                $idArray = $request->id;
                if (in_array("0", $idArray)) {
                    $employees = Employee::with('designation', 'department', 'branch', 'salary', 'salary.shift')->where('activation_status', '1')->get();
                } else {
                    $employees = Employee::with('designation', 'department', 'branch', 'salary', 'salary.shift')->where('activation_status', '1')->whereIn('id', $idArray)->get();
                }
                return view('hrm::attendance.employee.entry', compact('employees'))->render();
            }
        }
    }

    public function store_or_update(Request $request)
    {
        if ($request->ajax()) {
            if (permission('attendance-add') || permission('attendance-edit')) {
                DB::beginTransaction();
                try {
                    $attendance = [];
                    foreach ($request->salary as $key => $value) {
                        DailyAttendance::where(['employee_id' => $value['employee_id'], 'check_in_date' => $value['check_in_date'], 'shift_id' => $value['shift_id']])->delete();
                        $attendance[] = [
                            'employee_id' => $value['employee_id'],
                            'shift_id' => $value['shift_id'],
                            'check_in_date' => $value['check_in_date'],
                            'check_in_time' => $value['check_in_time'],
                            'check_out_date' => $value['check_out_date'],
                            'check_out_time' => $value['check_out_time'],
                            'working_hour' => $value['working_hour'],
                            'dept_type' => 1,
                            'approval_status' => 1,
                            'approve_remarks' => $value['approve_remarks'],
                        ];
                    }
                    DailyAttendance::insert($attendance);
                    DB::commit();
                    $output = ['status' => 'success', 'message' => 'Attendance Added Successfully'];
                } catch (\Exception $e) {
                    DB::rollBack();
                    $output = ['status' => 'error', 'message' => $e->getMessage()];
                }
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function reportIndex()
    {
        if (permission('attendance-report-access')) {
            $setTitle = __('file.Attendance Report');
            $this->setPageData($setTitle, $setTitle, 'fas fa-user-plus', [['name' => $setTitle]]);
            $employees = Employee::where('activation_status', '1')->get();
            return view('hrm::attendance.report', compact('employees'));
        } else {
            return $this->access_blocked();
        }
    }

    public function reportData(Request $request)
    {
        if ($request->ajax()) {
            if (permission('attendance-report-access')) {
                $data = DailyAttendance::with('shift', 'employee', 'employee.designation', 'employee.department', 'employee.branch')
                    ->where('employee_id', $request->id)
                    ->where('check_in_date', '>=', $request->from_date)
                    ->where('check_in_date', '<=', $request->to_date)
                    ->orderBy('check_in_date', 'asc')
                    ->get();
                return view('hrm::attendance.report-data', compact('data'))->render();
            }
        }
    }

    public function summery()
    {
        if (permission('attendance-summery-access')) {
            $setTitle = __('file.Attendance Summery');
            $this->setPageData($setTitle, $setTitle, 'fas fa-user-plus', [['name' => $setTitle]]);
            $employees = Employee::where('activation_status', '1')->get();
            return view('hrm::attendance.summery', compact('employees'));
        } else {
            return $this->access_blocked();
        }
    }

    public function summeryData(Request $request)
    {
        if ($request->ajax()) {
            if (permission('attendance-summery-access')) {
                $idArray = $request->id;
                $data = DB::table('employees as e')
                    ->join('designations as dg', 'dg.id', '=', 'e.designation_id')
                    ->join('departments as dp', 'dp.id', '=', 'e.department_id')
                    ->join('branches as b', 'b.id', '=', 'e.branch_id')
                    ->leftJoin('daily_attendances as da', function ($join) use ($request) {
                        $join->on('da.employee_id', '=', 'e.id')
                            ->whereBetween('da.check_in_date', [$request->from_date, $request->to_date])
                            ->where('da.approval_status', 2);
                    })
                    ->leftJoin('overtimes as o', function ($join) use ($request) {
                        $join->on('o.employee_id', '=', 'e.id')
                            ->where('o.start_date', '>=', $request->from_date)
                            ->where('o.start_date', '<=', $request->to_date)
                            ->where('o.approval_status', 2);
                    });

                if (!in_array("0", $idArray)) {
                    $data->whereIn('e.id', $idArray);
                }
                $data=
                    $data->select(
                    DB::raw('count(da.check_in_date) as total_attended'),
                    DB::raw('sum(IFNULL(da.working_hour, 0)) as total_attended_hour'),
                    DB::raw('sum(IFNULL(o.working_hour, 0)) as total_overtime_hour'),
                    'e.name as employee_name',
                    'dp.name as department_name',
                    'dg.name as designation_name',
                    'b.name as branch_name',
                    'e.id as employee_id',
                )
                    ->groupBy('e.id')
                    ->get();
                return view('hrm::attendance.summery-data', compact('data', 'request'))->render();
            }
        }
    }
}
