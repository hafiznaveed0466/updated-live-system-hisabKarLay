<?php

use Illuminate\Support\Facades\Route;

    Route::middleware('auth:api', 'timezone')->prefix('connector/api')->group(function () {
    
       

    Route::resource('business-location', Modules\Connector\Http\Controllers\Api\BusinessLocationController::class)->only('index', 'show');

    Route::resource('contactapi', Modules\Connector\Http\Controllers\Api\ContactController::class)->only('index', 'show', 'store', 'update');

    Route::post('contactapi-payment', [Modules\Connector\Http\Controllers\Api\ContactController::class, 'contactPay']);

        
    Route::resource('unit', Modules\Connector\Http\Controllers\Api\UnitController::class)->only('index', 'show');
    Route::post('unit/store', [Modules\Connector\Http\Controllers\Api\UnitController::class, 'store']);
    Route::post('unit/update/{id}', [Modules\Connector\Http\Controllers\Api\UnitController::class, 'update']);
    Route::post('unit/delete/{id}', [Modules\Connector\Http\Controllers\Api\UnitController::class, 'delete']);

    Route::resource('taxonomy', 'Modules\Connector\Http\Controllers\Api\CategoryController')->only('index', 'show');

    Route::post('taxonomy/store', [Modules\Connector\Http\Controllers\Api\CategoryController::class , 'store']);

	Route::post('taxonomy/update/{id}', [Modules\Connector\Http\Controllers\Api\CategoryController::class , 'update']);

	Route::get('taxonomy/delete/{id}', [Modules\Connector\Http\Controllers\Api\CategoryController::class , 'destroy']);


    Route::resource('brand', Modules\Connector\Http\Controllers\Api\BrandController::class)->only('index', 'show');
    Route::post('brand/store', [Modules\Connector\Http\Controllers\Api\BrandController::class, 'store']);
    Route::post('brand/update/{id}', [Modules\Connector\Http\Controllers\Api\BrandController::class, 'update']);
    Route::post('brand/delete/{id}', [Modules\Connector\Http\Controllers\Api\BrandController::class, 'delete']);
    Route::resource('product', Modules\Connector\Http\Controllers\Api\ProductController::class)->only('index', 'show');

    Route::get('selling-price-group', [Modules\Connector\Http\Controllers\Api\ProductController::class, 'getSellingPriceGroup']);

    Route::get('variation/{id?}', [Modules\Connector\Http\Controllers\Api\ProductController::class, 'listVariations']);

	Route::get('get_variation', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'get_Variations']);

	Route::post('store_variation', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'store_variation']);

	Route::get('show_variation/{id}', [Modules\Connector\Http\Controllers\Api\ProductController::class, 'show_variation']);

	Route::post('update_variation/{id}', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'update_variation']);


	Route::get('variation/delete/{id}', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'delete']);

	Route::get('recipe/get', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'indexMfgRecipe']);

	Route::post('recipe/store', [Modules\Connector\Http\Controllers\Api\ProductController::class,'storeMfgRecipe']);

	Route::get('recipe/show/{id}', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'show']);
	
	Route::post('recipe/update/{recipe_ids}', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'updateMfgRecipe']);
	
	Route::get('recipe/show/{id}', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'showMfgRecipe']);

	Route::get('recipe/delete/{id}', [Modules\Connector\Http\Controllers\Api\ProductController::class , 'destroy']);
	

    Route::resource('tax', 'Modules\Connector\Http\Controllers\Api\TaxController')->only('index', 'show');

    Route::any('/tax/store', [Modules\Connector\Http\Controllers\Api\TaxControlle::class , 'store']);
	Route::any('/tax/update/{id}', [Modules\Connector\Http\Controllers\Api\TaxControlle::class , 'update']);
	Route::get('/tax/delete/{id}', [Modules\Connector\Http\Controllers\Api\TaxControlle::class , 'destroy']);


    Route::resource('table', Modules\Connector\Http\Controllers\Api\TableController::class)->only('index', 'show');

    Route::post('table/store', [Modules\Connector\Http\Controllers\Api\TableController::class, 'store']);

	Route::post('table/update/{id}',[Modules\Connector\Http\Controllers\Api\TableController::class, 'update']);

	Route::post('table/delete/{id}', [Modules\Connector\Http\Controllers\Api\TableController::class, 'delete']);

    Route::get('user/loggedin', [Modules\Connector\Http\Controllers\Api\UserController::class, 'loggedin']);
    Route::post('user-registration', [Modules\Connector\Http\Controllers\Api\UserController::class, 'registerUser']);
    Route::resource('user', Modules\Connector\Http\Controllers\Api\UserController::class)->only('index', 'show');
  
    Route::post('/logout', [Modules\Connector\Http\Controllers\Api\UserController::class, 'logout']);
    
    Route::resource('types-of-service', Modules\Connector\Http\Controllers\Api\TypesOfServiceController::class)->only('index', 'show');

    Route::post('types-of-service/store', [Modules\Connector\Http\Controllers\Api\TypesOfServiceController::class , 'store']);

	Route::post('types-of-service/update/{id}',[Modules\Connector\Http\Controllers\Api\TypesOfServiceController::class , 'update']);

	Route::get('types-of-service/delete/{id}', [Modules\Connector\Http\Controllers\Api\TypesOfServiceController::class , 'delete']);

    Route::resource('sell', Modules\Connector\Http\Controllers\Api\SellController::class)->only('index', 'store', 'show', 'update', 'destroy');
    
    Route::get('kot/{id}', [Modules\Connector\Http\Controllers\Api\SellController::class,'kot']);
    
	Route::get('bill/{id}', [Modules\Connector\Http\Controllers\Api\SellController::class , 'bill']);

    Route::post('sell-return', [Modules\Connector\Http\Controllers\Api\SellController::class, 'addSellReturn']);

    Route::get('list-sell-return', [Modules\Connector\Http\Controllers\Api\SellController::class, 'listSellReturn']);

    Route::post('update-shipping-status', [Modules\Connector\Http\Controllers\Api\SellController::class, 'updateSellShippingStatus']);

    Route::resource('expense', Modules\Connector\Http\Controllers\Api\ExpenseController::class)->only('index', 'store', 'show', 'update');
    Route::get('expense-refund', [Modules\Connector\Http\Controllers\Api\ExpenseController::class, 'listExpenseRefund']);

    Route::get('expense-categories', [Modules\Connector\Http\Controllers\Api\ExpenseController::class, 'listExpenseCategories']);

    Route::resource('cash-register', Modules\Connector\Http\Controllers\Api\CashRegisterController::class)->only('index', 'store', 'show', 'update');

    Route::post('close-register', [Modules\Connector\Http\Controllers\Api\CashRegisterController::class , 'postCloseRegister']);
	Route::get('register-details', [Modules\Connector\Http\Controllers\Api\CashRegisterController::class, 'getRegisterDetails']);
    

    // Start Reports CommonResourceController
    Route::get('payment-accounts', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getPaymentAccounts']);
    Route::get('payment-methods', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getPaymentMethods']);
    Route::get('business-details', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getBusinessDetails']);
    Route::get('profit-loss-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getProfitLoss']);
    Route::get('product-stock-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getProductStock']);
    Route::get('notifications', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getNotifications']);
    Route::get('get-location', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class, 'getLocation']);
    Route::get('items-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'itemsReport']);
	
	Route::get('expense-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getexpense']);
	
	Route::get('stock-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getStockReport']);

	Route::get('register-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getRegisterReport']);

	Route::get('customer-supplier', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getCustomerSuppliers']);

	Route::get('product-sell-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getproductSellReport']);

	Route::get('trending-products', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getTrendingProducts']);


	Route::get('activity-log', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'activityLog']);
	Route::get('get-kot', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getkot']);
	Route::get('type-of-service-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'TypeOfService']);
	Route::get('product-purchase-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getproductPurchaseReport']);
	Route::get('sell-payment-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'sellPaymentReport']);
	Route::get('purchase-payment-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'purchasePaymentReport']);
	Route::get('purchase-sell', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getPurchaseSell']);
	Route::get('sales-representative-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getSalesRepresentativeTotalSell']);
	Route::get('stock-adjustment-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getStockAdjustmentReport']);
	Route::get('Kitchen-Performance', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'gettimeReport']);
	Route::get('customer-group', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getCustomerGroup']);
	Route::get('tax-report', [Modules\Connector\Http\Controllers\Api\CommonResourceController::class , 'getTaxReport']);

    // End Report CommonResourceController
    Route::get('active-subscription', [Modules\Connector\Http\Controllers\Api\SuperadminController::class, 'getActiveSubscription']);
    Route::get('packages', [Modules\Connector\Http\Controllers\Api\SuperadminController::class, 'getPackages']);

    Route::get('get-attendance/{user_id}', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'getAttendance']);
    Route::post('clock-in', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'clockin']);
    Route::post('clock-out', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'clockout']);
    Route::get('holidays', [Modules\Connector\Http\Controllers\Api\AttendanceController::class, 'getHolidays']);
    Route::post('update-password', [Modules\Connector\Http\Controllers\Api\UserController::class, 'updatePassword']);
    Route::post('forget-password', [Modules\Connector\Http\Controllers\Api\UserController::class, 'forgetPassword']);

    Route::get('new_product', [Modules\Connector\Http\Controllers\Api\ProductSellController::class, 'newProduct'])->name('new_product');
    Route::get('new_sell', [Modules\Connector\Http\Controllers\Api\ProductSellController::class, 'newSell'])->name('new_sell');
    Route::get('new_contactapi', [Modules\Connector\Http\Controllers\Api\ProductSellController::class, 'newContactApi'])->name('new_contactapi');

    // Purchase Api Controller

    Route::get('get_purchase', [Modules\Connector\Http\Controllers\Api\purchaseController::class , 'getpurchase']);

	Route::get('show_purchase/{id}', [Modules\Connector\Http\Controllers\Api\purchaseController::class ,'show_purchase']);

	Route::post('store_purchase', [Modules\Connector\Http\Controllers\Api\purchaseController::class ,'store']);

	Route::post('update_purchase/{id}', [Modules\Connector\Http\Controllers\Api\purchaseController::class ,'update']);

	Route::get('delete_purchase/{id}', [Modules\Connector\Http\Controllers\Api\purchaseController::class ,'delete']);

    // Modifiers sets Controllers
     Route::post('modifier/store', [Modules\Connector\Http\Controllers\Api\ModifierSetsController::class ,'storeModifier']);
     Route::post('modifier/update/{id}', [Modules\Connector\Http\Controllers\Api\ModifierSetsController::class ,'update']);
     Route::post('modifier/delete/{id}', [Modules\Connector\Http\Controllers\Api\ModifierSetsController::class ,'delete']);
     Route::get('modifier/{id}', [Modules\Connector\Http\Controllers\Api\ModifierSetsController::class ,'show']);
     Route::get('modifier', [Modules\Connector\Http\Controllers\Api\ModifierSetsController::class ,'index']);
     Route::post('/product-modifiers/{id}', [Modules\Connector\Http\Controllers\Api\ModifierSetsController::class ,'productModifier']);

    //  Role And Permissions 
	Route::get('roles',[Modules\Connector\Http\Controllers\Api\RoleController::class , 'index']);
	Route::post('role/store',[Modules\Connector\Http\Controllers\Api\RoleController::class , 'store']);
	Route::post('role/update/{id}',[Modules\Connector\Http\Controllers\Api\RoleController::class , 'update']);
	Route::post('role/delete/{id}',[Modules\Connector\Http\Controllers\Api\RoleController::class , 'destroy']);
    // 	Permissions 
    Route::get('permissions',[Modules\Connector\Http\Controllers\Api\RoleController::class , 'GetAllPermissions']);


	// User Managemnet System 
	Route::get('manage/user',[Modules\Connector\Http\Controllers\Api\ManageUserControllerApi::class , 'index']);
	Route::post('manage/user/store',[Modules\Connector\Http\Controllers\Api\ManageUserControllerApi::class , 'store']);
	Route::post('manage/user/update/{id}',[Modules\Connector\Http\Controllers\Api\ManageUserControllerApi::class , 'update']);
	Route::post('manage/user/delete/{id}',[Modules\Connector\Http\Controllers\Api\ManageUserControllerApi::class , 'destroy']);

  // Stock adjustment
     Route::get('get/stock-adjustment', [Modules\Connector\Http\Controllers\Api\StockAdjustmnentController::class , 'index']);
     Route::post('store/stock-adjustment', [Modules\Connector\Http\Controllers\Api\StockAdjustmnentController::class , 'createStockAdjustment']);
     Route::post('destroy/stock-adjustment/{id}', [Modules\Connector\Http\Controllers\Api\StockAdjustmnentController::class , 'deleteStockAdjustment']);
 
    // stock transfer
     Route::get('get/stock-transfer', [Modules\Connector\Http\Controllers\Api\StockTransaferController::class , 'index']);
     Route::post('store/stock-transfer', [Modules\Connector\Http\Controllers\Api\StockTransaferController::class , 'createStockTransfer']);
     Route::post('delete/stock-transfer/{id}', [Modules\Connector\Http\Controllers\Api\StockTransaferController::class , 'deleteStockTransfer']);
     Route::post('update/stock-transfer/{id}', [Modules\Connector\Http\Controllers\Api\StockTransaferController::class , 'updateStockTransfer']);

    // Kitchen Api 

        Route::get('/kitchen', [Modules\Connector\Http\Controllers\Api\KitchenController::class, 'index']);
        Route::get('/kitchen/mark-as-cooked/{id}', [Modules\Connector\Http\Controllers\Api\KitchenController::class, 'markAsCooked']);
        // Route::post('/refresh-orders-list', [Modules\Connector\Http\Controllers\Api\Restaurant\KitchenController::class, 'refreshOrdersList']);
        // Route::post('/refresh-line-orders-list', [Modules\Connector\Http\Controllers\Api\Restaurant\KitchenController::class, 'refreshLineOrdersList']);
    
});

Route::middleware('auth:api', 'timezone')->prefix('connector/api/crm')->group(function () {
    Route::resource('follow-ups', 'Modules\Connector\Http\Controllers\Api\Crm\FollowUpController')->only('index', 'store', 'show', 'update');

    Route::get('follow-up-resources', [Modules\Connector\Http\Controllers\Api\Crm\FollowUpController::class, 'getFollowUpResources']);

    Route::get('leads', [Modules\Connector\Http\Controllers\Api\Crm\FollowUpController::class, 'getLeads']);

    Route::post('call-logs', [Modules\Connector\Http\Controllers\Api\Crm\CallLogsController::class, 'saveCallLogs']);
});

Route::middleware('auth:api', 'timezone')->prefix('connector/api')->group(function () {
    Route::get('field-force', [Modules\Connector\Http\Controllers\Api\FieldForce\FieldForceController::class, 'index']);
    Route::post('field-force/create', [Modules\Connector\Http\Controllers\Api\FieldForce\FieldForceController::class, 'store']);
    Route::post('field-force/update-visit-status/{id}', [Modules\Connector\Http\Controllers\Api\FieldForce\FieldForceController::class, 'updateStatus']);
});
