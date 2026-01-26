<?php

use Illuminate\Support\Facades\Route;
use Modules\HRM\Http\Controllers\AllowanceController;
use Modules\HRM\Http\Controllers\AllowancesController;
use Modules\HRM\Http\Controllers\AttendanceController;
use Modules\HRM\Http\Controllers\AttendanceSettingController;
use Modules\HRM\Http\Controllers\BonusController;
use Modules\HRM\Http\Controllers\BranchController;
use Modules\HRM\Http\Controllers\DeductionController;
use Modules\HRM\Http\Controllers\EmployeeAttendanceController;
use Modules\HRM\Http\Controllers\EmployeeController;
use Modules\HRM\Http\Controllers\EmployeeLeaveAppController;
use Modules\HRM\Http\Controllers\EmployeeLeaveController;
use Modules\HRM\Http\Controllers\EmployeeSalaryPaymentController;
use Modules\HRM\Http\Controllers\GenerateMonthlySalaryController;
use Modules\HRM\Http\Controllers\HolidayController;
use Modules\HRM\Http\Controllers\IncrementController;
use Modules\HRM\Http\Controllers\LabourAllowancesController;
use Modules\HRM\Http\Controllers\LabourBonusController;
use Modules\HRM\Http\Controllers\LabourController;
use Modules\HRM\Http\Controllers\LabourDeductionController;
use Modules\HRM\Http\Controllers\LabourSalaryPaymentController;
use Modules\HRM\Http\Controllers\LabourAdvanceController;
use Modules\HRM\Http\Controllers\LeaveCatController;
use Modules\HRM\Http\Controllers\OvertimeController;
use Modules\HRM\Http\Controllers\PaySlipController;
use Modules\HRM\Http\Controllers\ProvidentFundController;
use Modules\HRM\Http\Controllers\SalaryAdvanceController;
use Modules\HRM\Http\Controllers\SalaryController;
use Modules\HRM\Http\Controllers\SalaryPaymentController;
use Modules\HRM\Http\Controllers\SalaryStatementController;
use Modules\HRM\Http\Controllers\ShiftController;
use Modules\HRM\Http\Controllers\WorkingDayController;


Route::group(['middleware' => ['auth']], function () {
    // Employee Routes
    Route::get('manage-employee', [EmployeeController::class, 'index'])->name('employee');
    Route::group(['prefix' => 'employee', 'as' => 'employee.'], function () {
        Route::get('add-employee', [EmployeeController::class, 'add'])->name('add');
        Route::post('datatable-data', [EmployeeController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [EmployeeController::class, 'store_or_update_data'])->name('store.or.update');
        Route::get('edit/{id}', [EmployeeController::class, 'edit'])->name('edit');
        Route::post('update', [EmployeeController::class, 'update_data'])->name('update');
        Route::post('delete', [EmployeeController::class, 'delete'])->name('delete');
        Route::post('change-status', [EmployeeController::class, 'change_status'])->name('change.status');
    });

    // Labour Routes
    Route::get('manage-labour', [LabourController::class, 'index'])->name('labour');
    Route::group(['prefix' => 'labour', 'as' => 'labour.'], function () {
        Route::get('add-labour', [LabourController::class, 'add'])->name('add');
        Route::post('datatable-data', [LabourController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourController::class, 'store_or_update_data'])->name('store.or.update');
        Route::get('edit/{id}', [LabourController::class, 'edit'])->name('edit');
        Route::post('update', [LabourController::class, 'update_data'])->name('update');
        Route::post('delete', [LabourController::class, 'delete'])->name('delete');
        Route::post('change-status', [LabourController::class, 'change_status'])->name('change.status');
    });

    // Salary Setup Routes
    Route::get('manage-salary', [SalaryController::class, 'index'])->name('salary');
    Route::group(['prefix' => 'salary', 'as' => 'salary.'], function () {
        Route::post('/add-employee-salary/go', [SalaryController::class, 'go']);
        Route::get('/manage-salary/{id}', [SalaryController::class, 'create']);
        Route::get('add-employee', [SalaryController::class, 'add'])->name('add');
        Route::post('datatable-data', [SalaryController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [SalaryController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [SalaryController::class, 'update_data'])->name('update');
        Route::get('edit/{id}', [SalaryController::class, 'edit'])->name('edit');
        Route::post('delete', [SalaryController::class, 'delete'])->name('delete');
        Route::post('change-status', [SalaryController::class, 'change_status'])->name('change.status');
    });

    // Monthly Employee Salary Payment Setup Routes
    Route::get('manage-salary-payment', [SalaryPaymentController::class, 'index'])->name('salaryPayment');
    Route::group(['prefix' => 'salaryPayment', 'as' => 'salaryPayment.'], function () {
        Route::get('/employee-salary/go', [SalaryPaymentController::class, 'create'])->name('salary.create');
        Route::get('/employee-allowance/{id}/{month}', [SalaryPaymentController::class, 'allowanceView'])->name('salary.allowance');
        Route::get('/employee-deduction/{id}/{month}', [SalaryPaymentController::class, 'deductionView'])->name('salary.deduction');
        Route::get('/salary-payslip/{id}', [SalaryPaymentController::class, 'payslipView'])->name('salary.payslip');
        Route::get('add-employee', [SalaryPaymentController::class, 'add'])->name('add');
        Route::post('datatable-data', [SalaryPaymentController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [SalaryPaymentController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [SalaryPaymentController::class, 'update_data'])->name('update');
        Route::get('edit/{id}', [SalaryPaymentController::class, 'edit'])->name('edit');
        Route::post('delete', [SalaryPaymentController::class, 'delete'])->name('delete');
        Route::post('change-status', [SalaryPaymentController::class, 'change_status'])->name('change.status');
    });

    // Monthly Labour Salary Payment Setup Routes
    Route::get('manage-labour-salary-payment', [LabourSalaryPaymentController::class, 'index'])->name('labourSalaryPayment');
    Route::group(['prefix' => 'labourSalaryPayment', 'as' => 'labourSalaryPayment.'], function () {
        Route::get('/labour-salary/go', [LabourSalaryPaymentController::class, 'create'])->name('salary.create');
        Route::get('/labour-allowance/{id}/{month}', [LabourSalaryPaymentController::class, 'allowanceView'])->name('salary.allowance');
        Route::get('/labour-deduction/{id}/{month}', [LabourSalaryPaymentController::class, 'deductionView'])->name('salary.deduction');
        Route::get('/salary-payslip/{id}', [LabourSalaryPaymentController::class, 'payslipView'])->name('salary.payslip');
        Route::get('add-labour', [LabourSalaryPaymentController::class, 'add'])->name('add');
        Route::post('datatable-data', [LabourSalaryPaymentController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourSalaryPaymentController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [LabourSalaryPaymentController::class, 'update_data'])->name('update');
        Route::get('edit/{id}', [LabourSalaryPaymentController::class, 'edit'])->name('edit');
        Route::post('delete', [LabourSalaryPaymentController::class, 'delete'])->name('delete');
        Route::post('change-status', [LabourSalaryPaymentController::class, 'change_status'])->name('change.status');
    });

    // Salary PaySlip Routes
    Route::get('generate-salary-sheet-and-payslip', [PaySlipController::class, 'index'])->name('payslip');
    Route::group(['prefix' => 'payslip', 'as' => 'payslip.'], function () {
        Route::post('datatable-data', [PaySlipController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('view-payslip', [PaySlipController::class, 'payslip'])->name('view.payslip');
    });

    // Salary Statement Routes
    Route::get('salary-statement', [SalaryStatementController::class, 'index'])->name('statement');
    Route::group(['prefix' => 'statement', 'as' => 'statement.'], function () {
        Route::post('datatable-data', [SalaryStatementController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('salary-statement', [SalaryStatementController::class, 'salaryStatement'])->name('salary.statement');
    });

    // Employee Salary Advance Routes
    Route::get('manage-salary-advance', [SalaryAdvanceController::class, 'index'])->name('salaryAdvance');
    Route::group(['prefix' => 'salaryAdvance', 'as' => 'salaryAdvance.'], function () {
        Route::post('datatable-data', [SalaryAdvanceController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [SalaryAdvanceController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [SalaryAdvanceController::class, 'edit'])->name('edit');
    });

    // Labour Salary Advance Routes
    Route::get('labour-salary-advance', [LabourAdvanceController::class, 'index'])->name('labourSalaryAdvance');
    Route::group(['prefix' => 'labourSalaryAdvance', 'as' => 'labourSalaryAdvance.'], function () {
        Route::post('datatable-data', [LabourAdvanceController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourAdvanceController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [LabourAdvanceController::class, 'edit'])->name('edit');
    });

    // Provident Fund Routes
    Route::get('provident-funds', [ProvidentFundController::class, 'index'])->name('providentFund');
    Route::group(['prefix' => 'providentFund', 'as' => 'providentFund.'], function () {
        Route::post('datatable-data', [ProvidentFundController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('/provident-fund/{id}', [ProvidentFundController::class, 'view'])->name('view.providentFund');
    });

    // Salary Increment Setup Routes
    Route::get('manage-increment', [IncrementController::class, 'index'])->name('increment');
    Route::group(['prefix' => 'increment', 'as' => 'increment.'], function () {
        Route::get('add-increment', [IncrementController::class, 'add'])->name('add');
        Route::post('datatable-data', [IncrementController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [IncrementController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [IncrementController::class, 'edit'])->name('edit');
        Route::post('delete', [IncrementController::class, 'delete'])->name('delete');
    });

    // Employee Deduction Setup Routes
    Route::get('manage-deductions', [DeductionController::class, 'index'])->name('deduction');
    Route::group(['prefix' => 'deduction', 'as' => 'deduction.'], function () {
        Route::post('datatable-data', [DeductionController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [DeductionController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [DeductionController::class, 'edit'])->name('edit');
    });

    // Labour Deduction Setup Routes
    Route::group(['prefix' => 'labourDeduction', 'as' => 'labourDeduction.'], function () {
        Route::post('datatable-data', [LabourDeductionController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourDeductionController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [LabourDeductionController::class, 'edit'])->name('edit');
    });

    // Employee Allowances Setup Routes
    Route::get('manage-allowances', [AllowancesController::class, 'index'])->name('allowances');
    Route::group(['prefix' => 'allowances', 'as' => 'allowances.'], function () {
        Route::post('datatable-data', [AllowancesController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [AllowancesController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [AllowancesController::class, 'edit'])->name('edit');
    });

    // Labour Allowances Setup Routes
    Route::group(['prefix' => 'labourAllowances', 'as' => 'labourAllowances.'], function () {
        Route::post('datatable-data', [LabourAllowancesController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourAllowancesController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [LabourAllowancesController::class, 'edit'])->name('edit');
    });

    // Employee Bonus Setup Routes
    Route::get('manage-bonuses', [BonusController::class, 'index'])->name('bonus');
    Route::group(['prefix' => 'bonus', 'as' => 'bonus.'], function () {
        Route::post('datatable-data', [BonusController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [BonusController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [BonusController::class, 'edit'])->name('edit');
        Route::post('delete', [BonusController::class, 'delete'])->name('delete');
        Route::post('change-status', [BonusController::class, 'change_status'])->name('change.status');
    });

    // Labour Bonus Setup Routes
    Route::group(['prefix' => 'bonus', 'as' => 'bonus.'], function () {
        Route::post('datatable-data', [LabourBonusController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourBonusController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [LabourBonusController::class, 'edit'])->name('edit');
        Route::post('delete', [LabourBonusController::class, 'delete'])->name('delete');
        Route::post('change-status', [LabourBonusController::class, 'change_status'])->name('change.status');
    });

    // Employee Leave Setup Routes
    Route::get('employee-leave-application', [EmployeeLeaveAppController::class, 'index'])->name('empLeave');
    Route::group(['prefix' => 'empLeave', 'as' => 'empLeave.'], function () {
        Route::post('datatable-data', [EmployeeLeaveAppController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [EmployeeLeaveAppController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [EmployeeLeaveAppController::class, 'edit'])->name('edit');
        Route::post('delete', [EmployeeLeaveAppController::class, 'delete'])->name('delete');
        Route::post('change-status', [EmployeeLeaveAppController::class, 'change_status'])->name('change.status');
    });

    // Labour Leave Setup Routes
    Route::get('labour-leave-application', [LabourLeaveAppController::class, 'index'])->name('labourLeave');
    Route::group(['prefix' => 'labourLeave', 'as' => 'labourLeave.'], function () {
        Route::post('datatable-data', [LabourLeaveAppController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LabourLeaveAppController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [LabourLeaveAppController::class, 'edit'])->name('edit');
        Route::post('delete', [LabourLeaveAppController::class, 'delete'])->name('delete');
        Route::post('change-status', [LabourLeaveAppController::class, 'change_status'])->name('change.status');
    });

    // Leave Category Setup Routes
    Route::get('manage-leave-category', [LeaveCatController::class, 'index']);
//    Route::get('manage-leave-category', [LeaveCatController::class, 'index'])->name('leave');
    Route::group(['prefix' => 'leave', 'as' => 'leave.'], function () {
        Route::post('/add-employee-salary/go', [LeaveCatController::class, 'go']);
        Route::get('/manage-salary/{id}', [LeaveCatController::class, 'create']);
        Route::get('add-employee', [LeaveCatController::class, 'add'])->name('add');
        Route::post('datatable-data', [LeaveCatController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LeaveCatController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [LeaveCatController::class, 'edit'])->name('edit');
        Route::post('delete', [LeaveCatController::class, 'delete'])->name('delete');
        Route::post('change-status', [LeaveCatController::class, 'change_status'])->name('change.status');
    });

    // Working Time Setup Routes
    Route::get('attendance-setting', [AttendanceSettingController::class, 'index'])->name('attendanceSetting');
    Route::group(['prefix' => 'attendanceSetting', 'as' => 'attendanceSetting.'], function () {
        Route::post('datatable-data', [AttendanceSettingController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [AttendanceSettingController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [AttendanceSettingController::class, 'edit'])->name('edit');
    });

    // Working Day Setup Routes
    Route::get('manage-working-days', [WorkingDayController::class, 'index'])->name('workingDays');
    Route::group(['prefix' => 'workingDays', 'as' => 'workingDays.'], function () {
        Route::post('/working-days/update/', [WorkingDayController::class, 'update']);
    });

    // Branch Routes
    Route::get('manage-branch', [BranchController::class, 'index'])->name('branch');
    Route::group(['prefix' => 'branch', 'as' => 'branch.'], function () {
        Route::post('datatable-data', [BranchController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [BranchController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [BranchController::class, 'edit'])->name('edit');
        Route::post('delete', [BranchController::class, 'delete'])->name('delete');
    });

    // Holiday Routes
    Route::get('holiday', [HolidayController::class, 'index'])->name('holiday');
    Route::group(['prefix' => 'holiday', 'as' => 'holiday.'], function () {
        Route::post('datatable-data', [HolidayController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [HolidayController::class, 'store'])->name('store');
        Route::post('delete', [HolidayController::class, 'delete'])->name('delete');
        Route::post('view', [HolidayController::class, 'view'])->name('view');
    });

    // Allowance Routes
    Route::get('hrm-allowance', [AllowanceController::class, 'index'])->name('hrm.allowance');
    Route::group(['prefix' => 'hrm-allowance', 'as' => 'hrm.allowance.'], function () {
        Route::post('datatable-data', [AllowanceController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [AllowanceController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('delete', [AllowanceController::class, 'delete'])->name('delete');
        Route::post('edit', [AllowanceController::class, 'edit'])->name('edit');
    });

    // Deduction Routes
    Route::get('hrm-deduction', [DeductionController::class, 'index'])->name('hrm.deduction');
    Route::group(['prefix' => 'hrm-deduction', 'as' => 'hrm.deduction.'], function () {
        Route::post('datatable-data', [DeductionController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [DeductionController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('delete', [DeductionController::class, 'delete'])->name('delete');
        Route::post('edit', [DeductionController::class, 'edit'])->name('edit');
    });

    // Shift Routes
    Route::get('hrm-shift', [ShiftController::class, 'index'])->name('hrm.shift');
    Route::group(['prefix' => 'hrm-shift', 'as' => 'hrm.shift.'], function () {
        Route::post('datatable-data', [ShiftController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [ShiftController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('delete', [ShiftController::class, 'delete'])->name('delete');
        Route::post('edit', [ShiftController::class, 'edit'])->name('edit');
    });

    // Leave Category Routes
    Route::get('hrm-leave-category', [LeaveCatController::class, 'index'])->name('hrm.leave.category');
    Route::group(['prefix' => 'hrm-leave-category', 'as' => 'hrm.leave.category.'], function () {
        Route::post('datatable-data', [LeaveCatController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [LeaveCatController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('delete', [LeaveCatController::class, 'delete'])->name('delete');
        Route::post('edit', [LeaveCatController::class, 'edit'])->name('edit');
    });

    // Daily Attendance Store Routes
    Route::get('attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('attendance-get-employees', [AttendanceController::class, 'getEmployees'])->name('attendance.get.employees');
    Route::post('attendance-store-or-update', [AttendanceController::class, 'store_or_update'])->name('attendance.store.or.update');

    // Leave management
    Route::get('leave', [EmployeeLeaveController::class, 'index'])->name('leave');
    Route::group(['prefix' => 'leave', 'as' => 'leave.'], function () {
        Route::post('datatable-data', [EmployeeLeaveController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [EmployeeLeaveController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('delete', [EmployeeLeaveController::class, 'delete'])->name('delete');
        Route::post('edit', [EmployeeLeaveController::class, 'edit'])->name('edit');
        Route::post('view', [EmployeeLeaveController::class, 'view'])->name('view');
        Route::get('approve/{id}', [EmployeeLeaveController::class, 'approve'])->name('approve');
    });

    // Overtime
    Route::get('overtime', [OvertimeController::class, 'index'])->name('overtime');
    Route::group(['prefix' => 'overtime', 'as' => 'overtime.'], function () {
        Route::post('datatable-data', [OvertimeController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [OvertimeController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('delete', [OvertimeController::class, 'delete'])->name('delete');
        Route::post('edit', [OvertimeController::class, 'edit'])->name('edit');
        Route::post('view', [OvertimeController::class, 'view'])->name('view');
        Route::get('approve/{id}', [OvertimeController::class, 'approve'])->name('approve');
    });

    // Generate Salary
    Route::get('generate-salary', [GenerateMonthlySalaryController::class, 'index'])->name('generate.salary');
    Route::group(['prefix' => 'generate-salary', 'as' => 'generate.salary.'], function () {
        Route::post('datatable-data', [GenerateMonthlySalaryController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('create', [GenerateMonthlySalaryController::class, 'create'])->name('create');
        Route::post('store-or-update', [GenerateMonthlySalaryController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('delete', [GenerateMonthlySalaryController::class, 'delete'])->name('delete');
        Route::post('edit', [GenerateMonthlySalaryController::class, 'edit'])->name('edit');
        Route::post('view', [GenerateMonthlySalaryController::class, 'view'])->name('view');
        Route::get('approve/{id}', [GenerateMonthlySalaryController::class, 'approve'])->name('approve');
    });
    Route::post('get-employees-data', [GenerateMonthlySalaryController::class, 'getEmployees'])->name('get.employees.data');

    // Employee Attendance Setup Routes
    Route::get('employee-attendance', [EmployeeAttendanceController::class, 'index'])->name('empAttendance');
    Route::group(['prefix' => 'employee-attendance', 'as' => 'empAttendance.'], function () {
        Route::post('datatable-data', [EmployeeAttendanceController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [EmployeeAttendanceController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [EmployeeAttendanceController::class, 'edit'])->name('edit');
        Route::post('delete', [EmployeeAttendanceController::class, 'delete'])->name('delete');
        Route::post('change-status', [EmployeeAttendanceController::class, 'change_status'])->name('change.status');
        Route::post('view', [EmployeeAttendanceController::class, 'view'])->name('view');
        Route::get('approve/{id}', [EmployeeAttendanceController::class, 'approve'])->name('approve');
    });

    // Attendance Report
    Route::get('attendance-report', [AttendanceController::class, 'reportIndex'])->name('attendance.report');
    Route::post('attendance-report-data', [AttendanceController::class, 'reportData'])->name('attendance.report.data');

    // Attendance Summary
    Route::get('attendance-summery', [AttendanceController::class, 'summery'])->name('attendance.summery');
    Route::post('attendance-summery-data', [AttendanceController::class, 'summeryData'])->name('attendance.summery.data');

    // Employee Salary Payment Setup Routes
    Route::get('employee-salary-payment', [EmployeeSalaryPaymentController::class, 'index'])->name('employee.salary.payment');
    Route::group(['prefix' => 'employee-salary-payment', 'as' => 'employee.salary.payment.'], function () {
        Route::post('datatable-data', [EmployeeSalaryPaymentController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('create', [EmployeeSalaryPaymentController::class, 'create'])->name('create');
        Route::post('store-or-update', [EmployeeSalaryPaymentController::class, 'storeOrUpdate'])->name('store.or.update');
        Route::post('update', [EmployeeSalaryPaymentController::class, 'update'])->name('update');
        Route::get('edit/{id}', [EmployeeSalaryPaymentController::class, 'edit'])->name('edit');
        Route::post('delete', [EmployeeSalaryPaymentController::class, 'delete'])->name('delete');
        Route::post('view', [EmployeeSalaryPaymentController::class, 'view'])->name('view');
        Route::get('approve/{id}', [EmployeeSalaryPaymentController::class, 'approve'])->name('approve');
    });
    Route::post('get-employees-due', [EmployeeSalaryPaymentController::class, 'getEmployees'])->name('get.employees.due');
});
