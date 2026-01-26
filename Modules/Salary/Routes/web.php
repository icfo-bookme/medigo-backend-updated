<?php

use Illuminate\Support\Facades\Route;
use Modules\Salary\Http\Controllers\AsmSalaryController;
use Modules\Salary\Http\Controllers\SrSalaryController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('sr-salary', [SrSalaryController::class, 'index'])->name('sr.salary');
    Route::group(['prefix' => 'sr-salary', 'as' => 'sr.salary.'], function () {
        Route::post('datatable-data', [SrSalaryController::class, 'getDataTableData'])->name('datatable.data');
        Route::get('add', [SrSalaryController::class, 'create'])->name('add');
        Route::post('store', [SrSalaryController::class, 'store'])->name('store');
        Route::get('month-wise-salary-generate/{month}', [SrSalaryController::class, 'monthWiseSalaryGenerate'])->name('month.wise.salary.generate');
    });

    Route::get('asm-salary', [AsmSalaryController::class, 'index'])->name('asm.salary');
    Route::group(['prefix' => 'asm-salary', 'as' => 'asm.salary.'], function () {
        Route::post('datatable-data', [AsmSalaryController::class, 'getDataTableData'])->name('datatable.data');
        Route::get('add', [AsmSalaryController::class, 'create'])->name('add');
        Route::post('store', [AsmSalaryController::class, 'store'])->name('store');
        Route::get('month-wise-salary-generate/{month}', [AsmSalaryController::class, 'monthWiseSalaryGenerate'])->name('month.wise.salary.generate');
    });
});
