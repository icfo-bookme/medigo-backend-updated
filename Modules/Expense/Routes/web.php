<?php

use Illuminate\Support\Facades\Route;
use Modules\Expense\Http\Controllers\ExpenseController;
use Modules\Expense\Http\Controllers\ExpenseItemController;
use Modules\Expense\Http\Controllers\ExpenseStatementController;

Route::group(['middleware' => ['auth']], function () {
    // Expense Item Routes
    Route::get('expense-item', [ExpenseItemController::class, 'index'])->name('expense.item');
    Route::group(['prefix' => 'expense-item', 'as' => 'expense.item.'], function () {
        Route::post('datatable-data', [ExpenseItemController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [ExpenseItemController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [ExpenseItemController::class, 'edit'])->name('edit');
        Route::post('delete', [ExpenseItemController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [ExpenseItemController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [ExpenseItemController::class, 'change_status'])->name('change.status');
    });

    // Expense Routes
    Route::get('expense', [ExpenseController::class, 'index'])->name('expense');
    Route::group(['prefix' => 'expense', 'as' => 'expense.'], function () {
        Route::post('datatable-data', [ExpenseController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [ExpenseController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::post('delete', [ExpenseController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [ExpenseController::class, 'bulk_delete'])->name('bulk.delete');
    });

    // Expense Statement Routes
    Route::get('expense-statement', [ExpenseStatementController::class, 'index'])->name('expense.statement');
    Route::post('expense-statement/datatable-data', [ExpenseStatementController::class, 'get_datatable_data'])->name('expense.statement.datatable.data');
});
