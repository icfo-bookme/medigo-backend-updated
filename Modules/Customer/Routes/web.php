<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;
use Modules\Customer\Http\Controllers\CustomerBulkImportController;
use Modules\Customer\Http\Controllers\WelcomCallController;
use Modules\Customer\Http\Controllers\CustomerFeedbackController;
use Modules\Customer\Http\Controllers\CustomerPointController;

Route::group(['middleware' => ['auth']], function () {
    Route::get('customer', [CustomerController::class, 'index'])->name('customer');
    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::post('datatable-data', [CustomerController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [CustomerController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [CustomerController::class, 'edit'])->name('edit');
        // Route::post('view', [CustomerController::class, 'show'])->name('view');
        Route::post('delete', [CustomerController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [CustomerController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [CustomerController::class, 'change_status'])->name('change.status');
        Route::post('view-order', [CustomerController::class, 'view_order'])->name('view.order');

        // Bulk Import
        Route::get('download_csv', [CustomerBulkImportController::class, 'download_file'])->name('download_csv');
        Route::get('bulk-import', [CustomerBulkImportController::class, 'bulk_import'])->name('bulk.import');
        Route::post('bulk-store', [CustomerBulkImportController::class, 'store'])->name('bulk.store');

        // Send Push Notification
        Route::post('send-push-notification', [CustomerController::class, 'sendPushNotification'])->name('send.push.notification');
    });

    Route::post('customer-list', [CustomerController::class, 'customer_list'])->name('customer.list');
    Route::post('customer-lists', [CustomerController::class, 'customer_lists'])->name('customer.list.edit');

    Route::get('welcome-call', [WelcomCallController::class, 'index'])->name('welcome.call');
    Route::group(['prefix' => 'welcome-call', 'as' => 'welcome.call.'], function () {
        Route::post('datatable-data', [WelcomCallController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('bulk-delete', [WelcomCallController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [WelcomCallController::class, 'change_status'])->name('change.status');
    });

    Route::get('customer-feedback', [CustomerFeedbackController::class, 'index'])->name('customer.feedback');
    Route::group(['prefix' => 'customer-feedback', 'as' => 'customer.feedback.'], function () {
        Route::post('datatable-data', [CustomerFeedbackController::class, 'get_datatable_data'])->name('datatable.data');
    });

    Route::get('get-customer-point', [CustomerPointController::class, 'getCustomerPoint'])->name('get.customer.point');
    Route::post('set-customer-point', [CustomerPointController::class, 'setCustomerPoint'])->name('set.customer.point');
});
