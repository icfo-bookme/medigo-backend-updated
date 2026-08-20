<?php

use App\Http\Controllers\Account\AccountReportController;
use App\Http\Controllers\Account\AuditController;
use App\Http\Controllers\Account\CostCategoryController;
use App\Http\Controllers\Account\CostInsertController;
use App\Http\Controllers\Account\CostSubCategoryController;
use App\Http\Controllers\Account\EndingBlanceController;
use App\Http\Controllers\Account\FundCategoryController as AccountFundCategoryController;
use App\Http\Controllers\Account\FundInsertController;
use App\Http\Controllers\Account\FundSubCategoryController;
use Illuminate\Support\Facades\Route;
use Modules\AccountNew\Http\Controllers\BaseCategoryController;

Route::group(['middleware' => ['auth']], function () {
    //Ending Balance Routes
    Route::get('/ending-blance', [EndingBlanceController::class, 'endingBlanceView'])->name('endingBlanceView');
    Route::get('/listAllEndingBlance', [EndingBlanceController::class, 'listAllEndingBlance'])->name('listAllEndingBlance');
    Route::post('/endingBlanceInsert', [EndingBlanceController::class, 'endingBlanceInsert'])->name('endingBlanceInsert');
    Route::post('/updateEndingBlance', [EndingBlanceController::class, 'updateEndingBlance'])->name('updateEndingBlance');
    Route::post('/deleteEndingBlance', [EndingBlanceController::class, 'deleteEndingBlance'])->name('deleteEndingBlance');
    Route::get('/get-ending-blance/{id}', [EndingBlanceController::class, 'getEndingBlance'])
        ->name('getEndingBlance');


    //Cost Audit Routes
    Route::get('/costAudit', [AuditController::class, 'auditView'])->name('auditView');
    Route::get('/expenseAudit', [AuditController::class, 'expenseAudit'])->name('expenseAudit');

    //Fund Audit Routes
    Route::get('/fundAudit', [AuditController::class, 'fundAuditView'])->name('auditView');
    Route::get('/listfundAudit', [AuditController::class, 'fundAudit'])->name('fundAudit');



    //(Reinvestment routes)
    // Route::get('/reinvestment', [ReinvestmentController::class, 'reinvestmentView'])->name('reinvestmentView');
    // Route::get('listAllReinvestment', [ReinvestmentController::class, 'listAllReinvestment'])->name('listAllReinvestment');
    // Route::post('/reinvestmentInsert', [ReinvestmentController::class, 'reinvestmentInsert'])->name('reinvestmentInsert');
    // Route::post('/getReinvestmentById', [ReinvestmentController::class, 'getReinvestmentById'])->name('getReinvestmentById');
    // Route::post('/reinvestmentUpdate', [ReinvestmentController::class, 'reinvestmentUpdate'])->name('reinvestmentUpdate');
    // Route::post('/reinvestmentDelete', [ReinvestmentController::class, 'investmentDelete'])->name('reinvestmentDelete');

    //Ending Balance Routes
    Route::get('cashStoragePlatformView', 'cashStoragePlatform\cashStoragePlatformController@cashStoragePlatformView');
    Route::post('cashStoragePlatformInsertAjax', 'cashStoragePlatform\cashStoragePlatformController@cashStoragePlatformInsertAjax');
    Route::post('cashStoragePlatformUpdateAjax', 'cashStoragePlatform\cashStoragePlatformController@cashStoragePlatformUpdateAjax');
    Route::post('cashStoragePlatformDeleteAjax', 'cashStoragePlatform\cashStoragePlatformController@cashStoragePlatformDeleteAjax');
    Route::post('getCashPlatformName', 'cashStoragePlatform\cashStoragePlatformController@getCashPlatformName');
    Route::post('cashPlatformNameUpdate', 'cashStoragePlatform\cashStoragePlatformController@cashPlatformNameUpdate');
    // Route::post('totalCashCalculationDetails/{parameter}', 'cashStoragePlatform\cashStoragePlatformController@totalCashCalculationDetails');
    Route::get('totalCashCalculationDetails', 'cashStoragePlatform\cashStoragePlatformController@totalCashCalculationDetails')->name('totalCashCalculationDetails');



    // Base Category Page
    Route::get('/base-category', [BaseCategoryController::class, 'index'])->name('base_category.index');
    Route::get('/base-category/list', [BaseCategoryController::class, 'list'])->name('base_category.list');
    Route::post('/base-category/store', [BaseCategoryController::class, 'store'])->name('base_category.store');
    Route::get('/base-category/edit/{id}', [BaseCategoryController::class, 'edit'])->name('base_category.edit');
    Route::post('/base-category/update/{id}', [BaseCategoryController::class, 'update'])->name('base_category.update');
    Route::delete('/base-category/delete/{id}', [BaseCategoryController::class, 'destroy'])->name('base_category.destroy');

    //(Cost Category routes)
    Route::get('costCategoryView', [CostCategoryController::class, 'costCategoryView'])->name('costCategoryView');
    Route::get('listAllCostCategories', [CostCategoryController::class, 'listAllCostCategories'])->name('listAllCostCategories');
    Route::post('costCategoryInsert', [CostCategoryController::class, 'costCategoryInsert'])->name('costCategoryInsert');
    Route::get('getCategoriesByBaseCategoryId/{id}', [CostCategoryController::class, 'getCategoriesByBaseCategoryId'])->name('getCategoriesByBaseCategoryId');
    Route::post('getCostCategoryById', [CostCategoryController::class, 'getCostCategoryById'])->name('getCostCategoryById');
    Route::post('costCategoryUpdate', [CostCategoryController::class, 'costCategoryUpdate'])->name('costCategoryUpdate');
    Route::post('costCategoryDelete', [CostCategoryController::class, 'costCategoryDelete'])->name('costCategoryDelete');
    Route::get('getCategoriesByBaseCategoryId/{id}', [CostCategoryController::class, 'getCostCategoryByBaseCategoryId'])->name('getCategoriesByBaseCategoryId');
    //(Cost Sub Category routes)
    Route::get('costSubCategoryView', [CostSubCategoryController::class, 'costSubCategoryView'])->name('costSubCategoryView');
    Route::get('listAllCostSubCategories', [CostSubCategoryController::class, 'listAllCostSubCategories'])->name('listAllCostSubCategories');
    Route::post('costSubCategoryInsert', [CostSubCategoryController::class, 'costSubCategoryInsert'])->name('costSubCategoryInsert');
    Route::get('getSubCatergoryById', [CostSubCategoryController::class, 'getSubCatergoryById'])->name('getSubCatergoryById');
    Route::post('costSubCategoryUpdate', [CostSubCategoryController::class, 'costSubCategoryUpdate'])->name('costSubCategoryUpdate');
    Route::post('costSubCategoryDelete', [CostSubCategoryController::class, 'costSubCategoryDelete'])->name('costSubCategoryDelete');

    //(Cost Insert routes)
    Route::get('costInsertView', [CostInsertController::class, 'costInsertView'])->name('costInsertView');
    Route::get('listAllInsertedCosts', [CostInsertController::class, 'listAllInsertedCosts'])->name('listAllInsertedCosts');
    Route::get('getSubcategoriesByCategoryId/{id}', [CostInsertController::class, 'getSubcategoriesByCategoryId'])->name('getSubcategoriesByCategoryId');
    Route::post('costInsert', [CostInsertController::class, 'costInsert'])->name('costInsert');
    Route::post('getCostEditForm', [CostInsertController::class, 'getCostEditForm'])->name('getCostEditForm');
    Route::post('showCostEditReasonPage', [CostInsertController::class, 'showCostEditReasonPage'])->name('showCostEditReasonPage');
    Route::post('getCostEditReasonDetails', [CostInsertController::class, 'getCostEditReasonDetails'])->name('getCostEditReasonDetails');
    Route::put('costUpdate', [CostInsertController::class, 'costUpdate'])->name('costUpdate');
    Route::post('costDelete', [CostInsertController::class, 'costDelete'])->name('costDelete');
    Route::post('costApproval/{id}', [CostInsertController::class, 'approvalStatusChange'])->name('approvalStatusChange');
    Route::post('getCostLogForm', [CostInsertController::class, 'getCostLogForm'])->name('getCostLogForm');

    // (Fund Category routes)
    Route::get('fundCategoryView', [AccountFundCategoryController::class, 'fundCategoryView'])->name('fundCategoryView');
    Route::get('listAllFundCategories', [AccountFundCategoryController::class, 'listAllFundCategories'])->name('listAllFundCategories');
    Route::post('fundCategoryInsert', [AccountFundCategoryController::class, 'fundCategoryInsert'])->name('fundCategoryInsert');
    Route::get('getFundCategoryById', [AccountFundCategoryController::class, 'getCostCategoryById'])->name('getFundCategoryById');
    Route::get('getFundCategoriesByBaseCategoryId/{id}', [AccountFundCategoryController::class, 'getFundCategoriesByBaseCategoryId'])->name('getFundCategoriesByBaseCategoryId');
    Route::post('fundCategoryUpdate', [AccountFundCategoryController::class, 'fundCategoryUpdate'])->name('fundCategoryUpdate');
    Route::post('fundCategoryDelete', [AccountFundCategoryController::class, 'fundCategoryDelete'])->name('fundCategoryDelete');

    //(Fund Sub Category routes)
    Route::get('fundSubCategoryView', [FundSubCategoryController::class, 'fundSubCategoryView'])->name('fundSubCategoryView');
    Route::get('listAllFundSubCategories', [FundSubCategoryController::class, 'listAllFundSubCategories'])->name('listAllFundSubCategories');
    Route::post('fundSubCategoryInsert', [FundSubCategoryController::class, 'fundSubCategoryInsert'])->name('fundSubCategoryInsert');
    Route::post('getFundSubCategoryEditForm', [FundSubCategoryController::class, 'getFundSubCategoryEditForm'])->name('getFundSubCategoryEditForm');
    Route::post('fundSubCategoryUpdate', [FundSubCategoryController::class, 'fundSubCategoryUpdate'])->name('fundSubCategoryUpdate');
    Route::post('fundSubCategoryDelete', [FundSubCategoryController::class, 'fundSubCategoryDelete'])->name('fundSubCategoryDelete');

    //(Fund Insert routes) 
    Route::get('fundInsertView', [FundInsertController::class, 'fundInsertView'])->name('fundInsertView');
    Route::get('listAllInsertedFunds', [FundInsertController::class, 'listAllInsertedFunds'])->name('listAllInsertedFunds');
    Route::get('getFundcategoriesBybaseCategoryId', [FundInsertController::class, 'getFundcategoriesBybaseCategoryId']);
    Route::get('getFundSubcategoriesByCategoryId', [FundInsertController::class, 'getFundSubcategoriesByCategoryId'])->name('getFundSubcategoriesByCategoryId');
    Route::post('fundInsert', [FundInsertController::class, 'fundInsert'])->name('fundInsert');
    Route::post('getFundEditForm', [FundInsertController::class, 'getFundEditForm'])->name('getFundEditForm');
    Route::post('fundUpdate', [FundInsertController::class, 'fundUpdate'])->name('fundUpdate');
    Route::post('fundDelete', [FundInsertController::class, 'fundDelete'])->name('fundDelete');


    // Reports routes
    Route::get('expenseReportView', [AccountReportController::class, 'accountReportView'])->name('accountReportView');
    Route::get('expenseReport', [AccountReportController::class, 'expenseReport'])->name('expenseReport');
    Route::get('fundReportView', [AccountReportController::class, 'fundReportView'])->name('fundReportView');
    Route::get('fundReport', [AccountReportController::class, 'fundReport'])->name('fundReport');
});
