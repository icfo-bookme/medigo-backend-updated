<?php

use Illuminate\Support\Facades\Route;
use Modules\Supplier\Http\Controllers\SupplierController;
use Modules\Supplier\Http\Controllers\SupplierAdvanceController;
use Modules\Supplier\Http\Controllers\SupplierLedgerController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('supplier', [SupplierController::class, 'index'])->name('supplier');

    Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
        Route::post('datatable-data', [SupplierController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [SupplierController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [SupplierController::class, 'edit'])->name('edit');
        Route::post('view', [SupplierController::class, 'show'])->name('view');
        Route::post('delete', [SupplierController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [SupplierController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [SupplierController::class, 'change_status'])->name('change.status');
        Route::get('due-amount/{id}', [SupplierController::class, 'due_amount']);
    });

    Route::get('supplier-advance', [SupplierAdvanceController::class, 'index'])->name('supplier.advance');

    Route::group(['prefix' => 'supplier-advance', 'as' => 'supplier.advance.'], function () {
        Route::post('datatable-data', [SupplierAdvanceController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [SupplierAdvanceController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [SupplierAdvanceController::class, 'edit'])->name('edit');
        Route::post('view', [SupplierAdvanceController::class, 'show'])->name('view');
        Route::post('delete', [SupplierAdvanceController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [SupplierAdvanceController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-approval-status', [SupplierAdvanceController::class, 'change_approval_status'])->name('change.approval.status');
    });

    Route::get('supplier-ledger', [SupplierLedgerController::class, 'index'])->name('supplier.ledger');
    Route::post('supplier-ledger/datatable-data', [SupplierLedgerController::class, 'get_datatable_data'])->name('supplier.ledger.datatable.data');
    Route::post('supplier-ledger/previous-ledger-data', [SupplierLedgerController::class, 'getLedgerPreviousData'])->name('get.supplier.ledger.previous.data');

});
