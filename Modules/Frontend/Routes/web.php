<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontend\Http\Controllers\AboutusController;
use Modules\Frontend\Http\Controllers\PrivacyController;
use Modules\Frontend\Http\Controllers\RefundController;
use Modules\Frontend\Http\Controllers\SliderController;
use Modules\Frontend\Http\Controllers\TermsController;
use Modules\Frontend\Http\Controllers\PromotionVideoController;

// Aboutus
Route::get('about-us', [AboutusController::class, 'aboutus'])->name('admin.aboutus');
Route::post('about-us/store', [AboutusController::class, 'create'])->name('aboutus.create');
Route::get('about-us/delete/{id}', [AboutusController::class, 'delete'])->name('aboutus.delete');
Route::get('about-us/edit/{id}', [AboutusController::class, 'edit'])->name('aboutus.edit');
Route::put('about-us/update/{id}', [AboutusController::class, 'update'])->name('aboutus.update');

// Terms & Condition
Route::get('terms', [TermsController::class, 'terms'])->name('admin.terms');
Route::post('terms/store', [TermsController::class, 'create'])->name('terms.create');
Route::get('terms/delete/{id}', [TermsController::class, 'delete'])->name('terms.delete');
Route::get('terms/edit/{id}', [TermsController::class, 'edit'])->name('terms.edit');
Route::put('terms/update/{id}', [TermsController::class, 'update'])->name('terms.update');

// Privacy Policy
Route::get('privacy', [PrivacyController::class, 'privacy'])->name('admin.privacy');
Route::post('privacy/store', [PrivacyController::class, 'create'])->name('privacy.create');
Route::get('privacy/delete/{id}', [PrivacyController::class, 'delete'])->name('privacy.delete');
Route::get('privacy/edit/{id}', [PrivacyController::class, 'edit'])->name('privacy.edit');
Route::put('privacy/update/{id}', [PrivacyController::class, 'update'])->name('privacy.update');

// Refund & Return Policy
Route::get('refund', [RefundController::class, 'refund'])->name('admin.refund');
Route::post('refund/store', [RefundController::class, 'create'])->name('refund.create');
Route::get('refund/delete/{id}', [RefundController::class, 'delete'])->name('refund.delete');
Route::get('refund/edit/{id}', [RefundController::class, 'edit'])->name('refund.edit');
Route::put('refund/update/{id}', [RefundController::class, 'update'])->name('refund.update');

// Slider
Route::get('slider', [SliderController::class, 'slider'])->name('admin.slider');
Route::post('slider/store', [SliderController::class, 'create'])->name('slider.create');
Route::get('slider/delete/{id}', [SliderController::class, 'delete'])->name('slider.delete');
Route::get('slider/edit/{id}', [SliderController::class, 'edit'])->name('slider.edit');
Route::put('slider/update/{id}', [SliderController::class, 'update'])->name('slider.update');

// Promotion Video
Route::get('promotion-video', [PromotionVideoController::class, 'index'])->name('promotion.video');
Route::group(['prefix' => 'promotion-video', 'as' => 'promotion.video.'], function () {
    Route::post('datatable-data', [PromotionVideoController::class, 'get_datatable_data'])->name('datatable.data');
    Route::post('store-or-update', [PromotionVideoController::class, 'store_or_update'])->name('store.or.update');
    Route::post('delete', [PromotionVideoController::class, 'delete'])->name('delete');
    Route::get('edit', [PromotionVideoController::class, 'edit'])->name('edit');
});
