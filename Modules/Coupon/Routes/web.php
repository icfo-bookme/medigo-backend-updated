<?php

use Illuminate\Support\Facades\Route;
use Modules\Coupon\Http\Controllers\CouponController;
use Modules\Coupon\Http\Controllers\CategoryCouponController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('coupon', [CouponController::class, 'index'])->name('coupon');
    Route::group(['prefix' => 'coupon', 'as' => 'coupon.'], function () {
        Route::get('create', [CouponController::class, 'create'])->name('add');
        Route::post('datatable-data', [CouponController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [CouponController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [CouponController::class, 'update'])->name('update');
        Route::post('edit', [CouponController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [CouponController::class, 'show']);
        Route::post('delete', [CouponController::class, 'delete'])->name('delete');
        Route::post('change-status', [CouponController::class, 'change_status'])->name('change.status');
    });

    //Category wise Coupon
    Route::get('category-coupon', [CategoryCouponController::class, 'index'])->name('category.coupon');
    Route::get('get-coupon', [CategoryCouponController::class, 'get_coupon'])->name('get.coupon');
    Route::group(['prefix' => 'category-coupon', 'as' => 'category.coupon.'], function () {
        Route::post('datatable-data', [CategoryCouponController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store', [CategoryCouponController::class, 'store'])->name('store');
        Route::post('update', [CategoryCouponController::class, 'update'])->name('update');
        Route::post('edit', [CategoryCouponController::class, 'edit'])->name('edit');
        Route::post('delete', [CategoryCouponController::class, 'delete'])->name('delete');
        Route::post('change-status', [CategoryCouponController::class, 'change_status'])->name('change.status');
    });

    //Customer wise Coupon
    Route::get('customer-coupon', 'CustomerCouponController@index')->name('customer.coupon');
    Route::get('get-customer', 'CustomerCouponController@get_customer')->name('get.customer');
    Route::group(['prefix' => 'customer-coupon', 'as'=>'customer.coupon.'], function () {
        Route::post('datatable-data', 'CustomerCouponController@get_datatable_data')->name('datatable.data');
        Route::post('store', 'CustomerCouponController@store')->name('store');
        Route::post('update', 'CustomerCouponController@update')->name('update');
        Route::post('edit', 'CustomerCouponController@edit')->name('edit');
        Route::post('delete', 'CustomerCouponController@delete')->name('delete');
        Route::post('change-status', 'CustomerCouponController@change_status')->name('change.status');
    });

    //Coupon Report
    Route::get('coupon-report', 'CouponReportController@index')->name('coupon.report');
    Route::group(['prefix' => 'coupon-report', 'as'=>'coupon.report.'], function () {
        Route::post('datatable-data', 'CouponReportController@get_datatable_data')->name('datatable.data');
    });
});
