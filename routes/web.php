<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\TaxController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PusherController;
use App\Http\Controllers\MyProfileController;


Auth::routes(['register' => false]);


Route::group(['middleware' => ['auth']], function () {
    Route::get('/foo', function () {
        Artisan::call('storage:link');
    });
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('dashboard-data/{start_date}/{end_date}', [HomeController::class, 'dashboard_data']);
    Route::get('dashboard-data/current-balance-data', [HomeController::class, 'currentBalanceData']);
    Route::get('unauthorized', [HomeController::class, 'unauthorized'])->name('unauthorized');
    Route::get('my-profile', [MyProfileController::class, 'index'])->name('my.profile');
    Route::post('update-profile', [MyProfileController::class, 'updateProfile'])->name('update.profile');
    Route::post('update-password', [MyProfileController::class, 'updatePassword'])->name('update.password');
    Route::get('product-stock-notification', [HomeController::class, 'product_stock_alert']);

    //Menu Routes
    Route::get('menu', [MenuController::class, 'index'])->name('menu');
    Route::group(['prefix' => 'menu', 'as' => 'menu.'], function () {
        Route::post('datatable-data', [MenuController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [MenuController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [MenuController::class, 'edit'])->name('edit');
        Route::post('delete', [MenuController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [MenuController::class, 'bulk_delete'])->name('bulk.delete');

        // Module Routes
        Route::post('module-order/{menu}', [ModuleController::class, 'orderItem'])->name('module.order');
        Route::get('builder/{menu}', [ModuleController::class, 'index'])->name('builder');
        Route::post('items', [ModuleController::class, 'get_menu_modules'])->name('items');

        Route::group(['prefix' => 'module', 'as' => 'module.'], function () {
            Route::get('create/{menu}', [ModuleController::class, 'create'])->name('create');
            Route::post('store-or-update', [ModuleController::class, 'storeOrUpdate'])->name('store.or.update');
            Route::post('edit', [ModuleController::class, 'edit'])->name('edit');
            Route::post('delete', [ModuleController::class, 'delete'])->name('delete');

            // Permission Routes
            Route::get('permission', [PermissionController::class, 'index'])->name('permission');

            Route::group(['prefix' => 'menu', 'as' => 'permission.'], function () {
                Route::post('datatable-data', [PermissionController::class, 'get_datatable_data'])->name('datatable.data');
                Route::post('store', [PermissionController::class, 'store'])->name('store');
                Route::post('edit', [PermissionController::class, 'edit'])->name('edit');
                Route::post('update', [PermissionController::class, 'update'])->name('update');
                Route::post('delete', [PermissionController::class, 'delete'])->name('delete');
                Route::post('bulk-delete', [PermissionController::class, 'bulk_delete'])->name('bulk.delete');
            });
        });
    });

    //Role Routes
    Route::get('role', [RoleController::class, 'index'])->name('role');
    Route::group(['prefix' => 'role', 'as' => 'role.'], function () {
        Route::get('create', [RoleController::class, 'create'])->name('create');
        Route::post('datatable-data', [RoleController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [RoleController::class, 'store_or_update_data'])->name('store.or.update');
        Route::get('edit/{id}', [RoleController::class, 'edit'])->name('edit');
        Route::get('view/{id}', [RoleController::class, 'show'])->name('view');
        Route::post('delete', [RoleController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [RoleController::class, 'bulk_delete'])->name('bulk.delete');
    });

    //User Routes
    Route::get('user', [UserController::class, 'index'])->name('user');
    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
        Route::post('datatable-data', [UserController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [UserController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [UserController::class, 'edit'])->name('edit');
        Route::post('view', [UserController::class, 'show'])->name('view');
        Route::post('delete', [UserController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [UserController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [UserController::class, 'change_status'])->name('change.status');
    });

    //Software Settings Route
    Route::get('setting', [SettingController::class, 'index'])->name('software.setting');
    Route::post('general-setting', [SettingController::class, 'general_setting'])->name('general.setting');
    Route::post('mail-setting', [SettingController::class, 'mail_setting'])->name('mail.setting');

    //Category Routes
    Route::get('category', [CategoryController::class, 'index'])->name('category');
    Route::group(['prefix' => 'category', 'as' => 'category.'], function () {
        Route::get('category-create', [CategoryController::class, 'create'])->name('category.create');
        Route::post('datatable-data', [CategoryController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [CategoryController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('update', [CategoryController::class, 'update'])->name('update');
        Route::get('edit/{id}', [CategoryController::class, 'edit'])->name('edit');
        Route::post('delete', [CategoryController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [CategoryController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [CategoryController::class, 'change_status'])->name('change.status');
        Route::get('generate-code', [CategoryController::class, 'generateProductCode'])->name('generate.code');
        Route::post('update-serial', [CategoryController::class, 'updateSerial'])->name('update.serial');
    });

    //Unit Group Routes
    Route::get('unit', [UnitController::class, 'index'])->name('unit');
    Route::group(['prefix' => 'unit', 'as' => 'unit.'], function () {
        Route::post('datatable-data', [UnitController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [UnitController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [UnitController::class, 'edit'])->name('edit');
        Route::post('delete', [UnitController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [UnitController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [UnitController::class, 'change_status'])->name('change.status');
        Route::post('base-unit', [UnitController::class, 'base_unit'])->name('base.unit');
    });

    //Tax Routes
    Route::get('tax', [TaxController::class, 'index'])->name('tax');
    Route::group(['prefix' => 'tax', 'as' => 'tax.'], function () {
        Route::post('datatable-data', [TaxController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [TaxController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [TaxController::class, 'edit'])->name('edit');
        Route::post('delete', [TaxController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [TaxController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [TaxController::class, 'change_status'])->name('change.status');
    });

    //Brand Routes
    Route::get('brand', [BrandController::class, 'index'])->name('brand');
    Route::group(['prefix' => 'brand', 'as' => 'brand.'], function () {
        Route::post('datatable-data', [BrandController::class, 'get_datatable_data'])->name('datatable.data');
        Route::post('store-or-update', [BrandController::class, 'store_or_update_data'])->name('store.or.update');
        Route::post('edit', [BrandController::class, 'edit'])->name('edit');
        Route::post('delete', [BrandController::class, 'delete'])->name('delete');
        Route::post('bulk-delete', [BrandController::class, 'bulk_delete'])->name('bulk.delete');
        Route::post('change-status', [BrandController::class, 'change_status'])->name('change.status');
    });

    Route::get('pusher', [PusherController::class, 'pusher']);
    Route::get('broadcast', [PusherController::class, 'broadcast']);
});
