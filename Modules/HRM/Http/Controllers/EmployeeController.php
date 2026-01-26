<?php

namespace Modules\HRM\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Traits\UploadAble;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Account\Entities\ChartOfAccount;
use Modules\Department\Entities\Department;
use Modules\Designation\Entities\Designation;
use Modules\HRM\Entities\Branch;
use Modules\HRM\Entities\Employee;
use Modules\HRM\Http\Requests\EmployeeFormRequest;

class EmployeeController extends BaseController
{
    use UploadAble;

    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    public function index()
    {
        if (permission('employee-access')) {
            $setTitle = __('file.employee');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $deletable = self::DELETABLE;
            return view('hrm::employee.index', compact('deletable'));
        } else {
            return $this->access_blocked();
        }
    }

    public function add()
    {
        if (permission('employee-add')) {
            $setTitle = __('file.employee add');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $department = Department::where('status', 1)->get();
            $designation = Designation::where('status', 1)->get();
            $branch = Branch::all();
            return view('hrm::employee.create', compact('department', 'designation', 'branch'));
        } else {
            return $this->access_blocked();
        }
    }

    public function get_datatable_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-access')) {
                if (!empty($request->employee_id)) {
                    $this->model->setName($request->employee_id);
                }
                $this->set_datatable_default_properties($request);//set datatable default properties
                $list = $this->model->getDatatableList();//get table data
                $data = [];
                $no = $request->input('start');
                foreach ($list as $value) {
                    $no++;
                    $action = '';
                    if (permission('employee-edit')) {
                        $action .= ' <a class="dropdown-item employee-edit" href="' . url("employee/edit/" . $value->id) . '">' . $this->actionButton('Edit') . '</a>';
                    }
                    if (permission('employee-delete')) {
                        if ($value->deletion_status == 2) {
                            $action .= ' <a class="dropdown-item delete_data"  data-id="' . $value->id . '" data-name="' . $value->name . '">' . $this->actionButton('Delete') . '</a>';
                        }
                    }
                    $row = [];
                    $row[] = $no;
                    $row[] = $value->employee_id;
                    $row[] = $value->name;
                    $row[] = $value->joining_date;
                    $row[] = $value->email;
                    $row[] = $value->contact_no_one;
                    $row[] = action_button($action);//custom helper function for action button
                    $data[] = $row;
                }
                return $this->datatable_draw($request->input('draw'), $this->model->count_all(),
                    $this->model->count_filtered(), $data);
            }
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function store_or_update_data(EmployeeFormRequest $request)
    {
        if ($request->ajax()) {
            $user_id = Auth::user()->id;
            if (permission('employee-add')) {
                $joining_month = date('Y-m', strtotime($request->joining_date));
                $user = new User();
                $user->name = $request->name;
                $user->username = $request->username;
                $user->role_id = 2;
                $user->password = $request->contact_no_one;
                $user->email = $request->email;
                $user->phone = $request->contact_no_one;
                $user->gender = $request->gender;
                $user->save();
                $resume = !empty($request->resume) ? $request->resume : null;
                if ($request->hasFile('resume')) {
                    $resume = $this->upload_file($request->file('resume'), EMPLOYEE_IMAGE_PATH);
                    if (!empty($request->old_resume)) {
                        unlink(EMPLOYEE_IMAGE_PATH . '/' . $request->old_resume);
                    }
                }
                $collection = collect($request->all())->merge(['user_id' => $user->id, 'created_by' => $user_id, 'joining_month' => $joining_month, 'resume' => $resume]);
                $employees = $this->model->create($collection->all());
//startcoa
                $coa_max_code = ChartOfAccount::where('level', 3)->where('code', 'like', '50202%')->max('code');
                $code = $coa_max_code ? ($coa_max_code + 1) : $this->coa_head_code('default_employee');
                $head_name = $employees->id . '-' . $request->name;
                $employee_coa_data = $this->employeeCoa($code, $head_name, $employees->id);

                $employee_coa = ChartOfAccount::create($employee_coa_data);
//endcoa
                $output = $this->store_message($employees);
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    public function edit($id)
    {
        if (permission('employee-access')) {
            $setTitle = __('file.Employee Edit');
            $this->setPageData($setTitle, $setTitle, 'far fa-handshake', [['name' => $setTitle]]);
            $employee = Employee::find($id);
            $department = Department::where('status', 1)->get();
            $designation = Designation::where('status', 1)->get();
            $branch = Branch::all();
            return view('hrm::employee.edit', compact('employee', 'department', 'designation', 'branch'));
        } else {
            return $this->access_blocked();
        }
    }

    public function update_data(Request $request)
    {
        if ($request->ajax()) {
            if (permission('employee-edit')) {
                $resume = !empty($request->resume) ? $request->resume : null;
                if ($request->hasFile('resume')) {
                    $resume = $this->upload_file($request->file('resume'), EMPLOYEE_IMAGE_PATH);
                    if (!empty($request->old_resume)) {
                        unlink(public_path('storage/' . EMPLOYEE_IMAGE_PATH . '' . $request->old_resume));
                    }
                }
                $collection = collect($request->all())->merge(['resume' => $resume]);
                $oldData = $this->model->find($request->update_id);
                //coa
                $old_head_name = $request->update_id . '-' . $oldData->name;
                $new_head_name = $request->update_id . '-' . $request->name;
                $employee_coa = ChartOfAccount::where(['name' => $old_head_name, 'employee_id' => $request->update_id])->first();
                if ($employee_coa) {
//                    $supplier_coa_id = $employee_coa->id;
                    $employee_coa->update(['name' => $new_head_name]);
                }
                $employee = $oldData->update($collection->all());
                //endcoa
                $output = $this->store_message($employee);
            } else {
                $output = $this->unauthorized();
            }
            return response()->json($output);
        } else {
            return response()->json($this->unauthorized());
        }
    }

    private function employeeCoa(string $code,string $head_name,int $employee_id)
    {
        return [
            'code'              => $code,
            'name'              => $head_name,
            'parent_name'       => 'Employee Ledger',
            'level'             => 3,
            'type'              => 'L',
            'transaction'       => 1,
            'general_ledger'    => 2,
            'customer_id'       => null,
            'supplier_id'       => null,
            'employee_id'       => $employee_id,
            'budget'            => 2,
            'depreciation'      => 2,
            'depreciation_rate' => '0',
            'status'            => 1,
            'created_by'        => auth()->user()->name
        ];
    }
}
