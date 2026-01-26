<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoriesController;
use App\Http\Controllers\Api\AuthenticationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\DeliveryManController;
use \Modules\Sale\Http\Controllers\PrescriptionOrderController;
use \Modules\Sale\Http\Controllers\OrderPackageController;
use App\Http\Controllers\Api\FrontendController;
use App\Http\Controllers\Api\CampaignApiController;

Route::get('get-products', [ProductController::class, 'get_product']);
Route::get('search/{name}', [ProductController::class, 'search_product']);
Route::get('product-details/{slug}', [ProductController::class, 'get_product_details']);
Route::get('product-generic-similar/{slug}', [ProductController::class, 'getSimilarGenericProducts']);
Route::get('get-categories', [CategoriesController::class, 'get_category']);
Route::get('categories-wise-product/{slug}', [CategoriesController::class, 'get_category_wise_product']);

Route::post('registration', [AuthenticationController::class, 'register']);
Route::post('otp/verify', [AuthenticationController::class, 'verifyUserOtp']);
Route::post('login', [AuthenticationController::class, 'authenticate']);

//Delivery man api --------------
Route::post('delivery-man-login', [AuthenticationController::class, 'deliveryManAuthenticate']);

Route::post('checkout', [OrderController::class, 'Order']);
Route::post('coupon-code-check', [OrderController::class, 'couponCheck']);
Route::post('order-feedback', [OrderController::class, 'orderFeedback']);
Route::get('delivery-charge', [OrderController::class, 'deliveryCharge']);

//Campaign Routes
Route::get('get-campaign', [CampaignApiController::class, 'getCampaign']);


Route::group(['middleware' => ['jwt.verify', 'auth:customer']], function () {
    Route::get('my-profile', [AuthenticationController::class, 'myCustomerProfile']);
    Route::post('update-profile', [AuthenticationController::class, 'updateCustomerProfile']);
    Route::post('store-address', [AuthenticationController::class, 'storeAddress']);
    Route::post('change-password', [AuthenticationController::class, 'change_password']);

    Route::get('order-list', [OrderController::class, 'orderList']);

    Route::group(['prefix' => 'prescription-order', 'as' => 'api.prescription-order.'], function () {
        Route::get('list', [PrescriptionOrderController::class, 'indexApi']);
        Route::post('store-or-update', [PrescriptionOrderController::class, 'store_or_update_data_api'])->name('store.or.update');
        Route::delete('delete/{id}', [PrescriptionOrderController::class, 'deleteApi']);
        Route::get('view/{id}', [PrescriptionOrderController::class, 'show'])->name('show');
        Route::put('change-status', [PrescriptionOrderController::class, 'change_status'])->name('change.status');

    });

    Route::group(['prefix' => 'package-order', 'as' => 'api.package-order.'], function () {
        Route::get('list', [OrderPackageController::class, 'indexApi']);
        Route::post('store-or-update', [OrderPackageController::class, 'store_or_update_data_api']);
        Route::delete('delete/{id}', [OrderPackageController::class, 'deleteApi']);
        Route::get('view/{id}', [OrderPackageController::class, 'show']);
        Route::put('change-status', [OrderPackageController::class, 'change_status']);
    });
});

Route::group(['middleware' => ['jwt.verify', 'auth:api']], function () {
//    Delivery man routes -------------
    Route::post('delivery-man-update-profile', [AuthenticationController::class, 'deliveryManUpdateProfile']);

    Route::get('assigned-product', [DeliveryManController::class, 'assignedProduct']);
    Route::post('assigned-product-status', [DeliveryManController::class, 'assignedProductStatus']);
    Route::get('delivered-product', [DeliveryManController::class, 'deliveredProductStatus']);
    Route::get('cancel-product', [DeliveryManController::class, 'cancelProductStatus']);
    Route::get('on-delivery-product', [DeliveryManController::class, 'onDeliveryProductStatus']);
});

//Frontend
Route::get('get-search-text', [FrontendController::class, 'getSearchText']);
Route::get('get-company-info', [FrontendController::class, 'getCompanyInfo']);
Route::get('get-promotion-video', [FrontendController::class, 'getPromotionVideo']);

Route::get('about-us', [FrontendController::class, 'getAboutUs']);
Route::get('terms-condition', [FrontendController::class, 'getTermsAndCondition']);
Route::get('privacy-policy', [FrontendController::class, 'getPrivacyPolicy']);
Route::get('return-refund-policy', [FrontendController::class, 'getReturnAndRefundPolicy']);
Route::get('slider', [FrontendController::class, 'getslider']);

Route::post('prescription-guest-order', [PrescriptionOrderController::class, 'store_or_update_data_guest']);
Route::post('guest-otp/verify', [PrescriptionOrderController::class, 'verifyGuestOtp']);
