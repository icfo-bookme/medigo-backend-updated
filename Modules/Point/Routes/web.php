<?php
use Illuminate\Support\Facades\Route;
use Modules\Point\Http\Controllers\MoneyWisePointController;
use Modules\Point\Http\Controllers\PointWiseMoneyController;

Route::group(['middleware' => ['auth']], function () {
    // Money Wise Point Routes
    Route::get('money-wise-point', [MoneyWisePointController::class, 'index'])->name('money_wise_point');
    Route::group(['prefix' => 'point', 'as' => 'point.'], function () {
        Route::post('datatable-data', [MoneyWisePointController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [MoneyWisePointController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [MoneyWisePointController::class, 'edit'])->name('edit');
        Route::post('delete', [MoneyWisePointController::class, 'delete'])->name('delete');
    });

    // Point Wise Money Routes
    Route::get('point-wise-money', [PointWiseMoneyController::class, 'index'])->name('point_wise_money');
    Route::group(['prefix' => 'points', 'as' => 'points.'], function () {
        Route::post('datatable-data', [PointWiseMoneyController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [PointWiseMoneyController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [PointWiseMoneyController::class, 'edit'])->name('edit');
        Route::post('delete', [PointWiseMoneyController::class, 'delete'])->name('delete');
    });
});
