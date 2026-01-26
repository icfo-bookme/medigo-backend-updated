<?php

use Illuminate\Support\Facades\Route;
use Modules\Campaign\Http\Controllers\CampaignController;
use Modules\Campaign\Http\Controllers\CampaignProductController;
use Modules\Campaign\Http\Controllers\CampaignCategoryController;

Route::group(['middleware' => ['auth']], function () {
    //Campaign Routes
    Route::get('campaign', [CampaignController::class, 'index'])->name('campaign');
    Route::group(['prefix' => 'campaign', 'as' => 'campaign.'], function () {
        Route::post('datatable-data', [CampaignController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [CampaignController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [CampaignController::class, 'edit'])->name('edit');
        Route::post('delete', [CampaignController::class, 'delete'])->name('delete');
        Route::post('change-status', [CampaignController::class, 'change_status'])->name('change.status');
    });

    // Campaign Product Routes
    Route::get('campaign-product', [CampaignProductController::class, 'index'])->name('campaign.product');
    Route::get('get-campaign', [CampaignProductController::class, 'getCampaign'])->name('get.campaign');
    Route::get('get-product', [CampaignProductController::class, 'getProduct'])->name('get.product');
    Route::get('get-listed-product', [CampaignProductController::class, 'getListedProduct'])->name('get.listed.product');
    Route::group(['prefix' => 'campaign-product', 'as' => 'campaign.product.'], function () {
        Route::post('datatable-data', [CampaignProductController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('add', [CampaignProductController::class, 'create'])->name('add');
        Route::post('store-or-update', [CampaignProductController::class, 'store_or_update'])->name('store.or.update');
        Route::post('delete', [CampaignProductController::class, 'delete'])->name('delete');
    });

    // Campaign Category Routes
    Route::get('campaign-category', [CampaignCategoryController::class, 'index'])->name('campaign.category');
    Route::get('get-category', [CampaignCategoryController::class, 'getCategory'])->name('get.category');
    Route::get('get-listed-category', [CampaignCategoryController::class, 'getListedCategory'])->name('get.listed.category');
    Route::group(['prefix' => 'campaign-category', 'as' => 'campaign.category.'], function () {
        Route::post('datatable-data', [CampaignCategoryController::class, 'get_datatable_data'])->name('datatable.data');
        Route::get('add', [CampaignCategoryController::class, 'create'])->name('add');
        Route::post('store-or-update', [CampaignCategoryController::class, 'store_or_update'])->name('store.or.update');
        Route::post('edit', [CampaignCategoryController::class, 'edit'])->name('edit');
        Route::post('delete', [CampaignCategoryController::class, 'delete'])->name('delete');
    });
});
