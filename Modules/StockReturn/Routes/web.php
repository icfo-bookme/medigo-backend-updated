<?php

use Illuminate\Support\Facades\Route;
use Modules\StockReturn\Http\Controllers\StockReturnController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('stock-return/{id}', [StockReturnController::class, 'stockReturn'])->name('stock.return');

    Route::get('stock-return-list', [StockReturnController::class, 'index'])->name('stock.return.list');
    Route::group(['prefix' => 'stock-return', 'as' => 'stock.return.'], function () {
        Route::post('datatable-data', [StockReturnController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [StockReturnController::class, 'stockReturnStore'])->name('store');
        Route::get('show/{id}', [StockReturnController::class, 'show'])->name('show');
        Route::post('delete', [StockReturnController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [StockReturnController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('approve-refund', [StockReturnController::class, 'approveRefund'])->name('approve.refund');

        //Log Routes
        Route::post('log', [StockReturnController::class, 'stockReturnLog'])->name('log');
    });
});
