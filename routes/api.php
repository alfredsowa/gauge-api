<?php

use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\BusinessController;
use App\Http\Controllers\Api\CustomersController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\HelperOptionsController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\MaterialsController;
use App\Http\Controllers\Api\ProductsController;
use App\Http\Controllers\Api\IntermediateGoodsController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PurchasesController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\EmployeesController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Resources\IntermediateGood\IntermediateGoodMaterialsResource;
use App\Http\Resources\IntermediateGood\IntermediateGoodVitalResource;
use App\Http\Resources\Product\ProductFullResource;
use App\Models\IntermediateGood;
use App\Models\IntermediateGoodsMaterial;
use App\Models\Product;
use App\Models\Production;
use App\Notifications\ActivityErrorNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

//Login
Route::post('/login', [LoginController::class, 'login']);

//Register
Route::post('/sign-up', [RegisterController::class, 'register']);

//Reset Password
Route::post('/forgot-password', [LoginController::class, 'resetPassword']);
Route::post('/confirm-reset-password', [LoginController::class, 'resetPasswordConfirmation']);

Route::group(['middleware' => ['auth:sanctum']], function(){

    Route::get('/dashboard/analytics', [DashboardController::class, 'index']);


    Route::post('/verify_token', [LoginController::class, 'userLoggedIn']);

    Route::post('/resend-verification-code', [RegisterController::class, 'reSendVerificationCode']);
    Route::post('/confirm-verification-code', [RegisterController::class, 'confirmEmailVerification']);

    //Profile Routes
    Route::post('/save-personal-profile', [ProfileController::class, 'savePersonalDetails']);
    Route::post('/change-email', [ProfileController::class, 'changeEmailAddress']);
    Route::post('/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/change-profile-photo', [ProfileController::class, 'saveProfilePhoto']);
    Route::post('/remove-profile-photo', [ProfileController::class, 'removeProfilePhoto']);
    Route::post('/deactivate-account', [ProfileController::class, 'deactivateAccount']);

    Route::post('/pending-email-change', function (Request $request) {
        if ($request->user()->pendingEmailChange()) {
            return response()->json(['has_pending' => true]);
        }
        return response()->json(['has_pending' => false]);
    });

    Route::get('/default-business', [BusinessController::class, 'getDefaultBusiness']);
    Route::get('/businesses', [BusinessController::class, 'getBusinesses']);
    Route::get('/business/{id}', [BusinessController::class, 'getBusiness']);
    Route::post('/change-business-logo', [BusinessController::class, 'saveProfilePhoto']);
    Route::post('/remove-business-logo', [BusinessController::class, 'removeProfilePhoto']);
    Route::post('/setup-business/details', [BusinessController::class, 'setBusinessDetails']);
    Route::post('/setup-business/components', [BusinessController::class, 'setBusinessComponents']);
    Route::post('/setup-business/location', [BusinessController::class, 'setBusinessLocation']);
    Route::post('/setup-business/complete', [BusinessController::class, 'setBusinessCompleted']);
    Route::post('/setup-business/other-information', [BusinessController::class, 'setBusinessOtherDetails']);
    Route::post('/save-average-monthly-goods', [BusinessController::class, 'saveAverageMonthlyGoods']);
    Route::get('/overheads', [BusinessController::class, 'getOverheads']);
    Route::post('/remove-overhead', [BusinessController::class, 'removeOverhead']);
    Route::post('/save-overhead', [BusinessController::class, 'saveOverhead']);
    Route::post('/remove-business', [BusinessController::class, 'removeBusiness']);

    Route::get('/users', [UserController::class, 'users']);
    Route::get('/user/{id}', [UserController::class, 'user']);

    Route::get('/materials', [MaterialsController::class, 'getMaterials']);
    Route::get('/basic-materials', [MaterialsController::class, 'getBasicMaterials']);
    Route::get('/materials-dropdown', [MaterialsController::class, 'getMaterialsForOptions']);
    Route::get('/material/{id}', [MaterialsController::class, 'getMaterial']);
    Route::get('/materials/{id}/view', [MaterialsController::class, 'viewMaterial']);
    Route::post('/materials/create', [MaterialsController::class, 'newMatrial']);
    Route::put('/materials/update', [MaterialsController::class, 'updateBasicInfo']);
    Route::post('/materials/save-image', [MaterialsController::class, 'savePhoto']);
    Route::post('/materials/remove-image', [MaterialsController::class, 'removePhoto']);
    Route::post('/materials/delete-material', [MaterialsController::class, 'deleteMaterial']);
    Route::post('/materials/save-category', [MaterialsController::class, 'saveCategory']);

    Route::get('/material-categories', [HelperOptionsController::class, 'materialCategories']);
    Route::get('/product-categories', [HelperOptionsController::class, 'productCategories']);
    Route::get('/product-types', [HelperOptionsController::class, 'productTypes']);
    Route::get('/get-guides', [HelperOptionsController::class, 'getGuide']);
    Route::post('/update-guides', [HelperOptionsController::class, 'updateGuide']);

    //Purchase
    Route::get('/purchases', [PurchasesController::class, 'getPurchases']);
    Route::get('/purchases-basic', [PurchasesController::class, 'getPurchasesBasic']);
    Route::get('/purchases/{id}', [PurchasesController::class, 'getPurchaseBasic']);
    Route::post('/purchase/create', [PurchasesController::class, 'savePurchase']);
    Route::post('/purchase/delete', [PurchasesController::class, 'deletePurchase']);
    Route::get('/suppliers', [PurchasesController::class, 'getSuppliers']);
    Route::get('/supplier/{id}', [PurchasesController::class, 'getSupplierFull']);
    Route::post('/suppliers/save', [PurchasesController::class, 'saveSupplier']);
    Route::post('/suppliers/delete', [PurchasesController::class, 'deleteSupplier']);

    // Production
    Route::get('/productions', [ProductionController::class, 'getProductions']);
    Route::get('/production/{id}', [ProductionController::class, 'getBasicProduction']);
    Route::get('/production/{id}/view', [ProductionController::class, 'getFullProduction']);
    Route::post('/productions/new', [ProductionController::class, 'createProduction']);
    Route::post('/productions/save', [ProductionController::class, 'saveProduction']);
    Route::post('/productions/start-production', [ProductionController::class, 'startProduction']);
    Route::post('/productions/reset-materials', [ProductionController::class, 'restoreProductionMaterials']);
    Route::post('/productions/duplicate', [ProductionController::class, 'duplicateProduction']);
    Route::post('/productions/{id}/change-status', [ProductionController::class, 'updateProductionStatus']);
    Route::post('/productions/material/save', [ProductionController::class, 'saveProductionMaterial']);
    Route::post('/productions/material/delete', [ProductionController::class, 'deleteProductionMaterial']);
    Route::post('/productions/delete', [ProductionController::class, 'deleteProduction']);

    //Employees
    Route::get('/employees', [EmployeesController::class, 'getEmployees']);
    Route::get('/employee/{id}', [EmployeesController::class, 'getBasicEmployee']);
    Route::post('/employees/save', [EmployeesController::class, 'saveEmployee']);
    Route::post('/employees/delete', [EmployeesController::class, 'deleteEmployee']);
    Route::post('/employees/remove-image', [EmployeesController::class, 'removePhoto']);

    //Products
    Route::get('/products', [ProductsController::class, 'getProducts']);
    Route::get('/product/{id}', [ProductsController::class, 'getProduct']);
    Route::get('/product-full/{id}', [ProductsController::class, 'getFullProductById']);
    Route::get('/product/{slug}/view', [ProductsController::class, 'getFullProduct']);
    Route::post('/products/save', [ProductsController::class, 'saveProduct']);
    Route::post('/products/duplicate', [ProductsController::class, 'duplicateProduct']);
    Route::post('/products/delete', [ProductsController::class, 'deleteProduct']);
    Route::post('/products/remove-image', [ProductsController::class, 'removePhoto']);
    Route::post('/products/product/material/save', [ProductsController::class, 'saveProductMaterial']);
    Route::post('/products/product/material/delete', [ProductsController::class, 'deleteProductionMaterial']);
    Route::post('/products/product/intermediate-good/delete', [ProductsController::class, 'deleteProductIntermediateGood']);
    Route::get('/product-cost/{id}', [ProductsController::class, 'getProductMaterialCost']);

    //Intermediate Goods
    Route::get('/intermediate-goods', [IntermediateGoodsController::class, 'getIntermediateGoods']);
    Route::get('/intermediate-good/{id}', [IntermediateGoodsController::class, 'getIntermediateGood']);
    Route::get('/intermediate-good/{slug}/view', [IntermediateGoodsController::class, 'getFullIntermediateGood']);
    Route::post('/intermediate-goods/save', [IntermediateGoodsController::class, 'saveIntermediateGood']);
    Route::post('/intermediate-goods/duplicate', [IntermediateGoodsController::class, 'duplicateIntermediateGood']);
    Route::post('/intermediate-goods/delete', [IntermediateGoodsController::class, 'deleteIntermediateGood']);
    Route::post('/intermediate-goods/remove-image', [IntermediateGoodsController::class, 'removePhoto']);
    Route::post('/intermediate-goods/intermediate-good/material/save', [IntermediateGoodsController::class, 'saveIntermediateGoodMaterial']);
    Route::post('/intermediate-goods/intermediate-good/material/delete', [IntermediateGoodsController::class, 'deleteProductionMaterial']);

    //Sales
    Route::get('/sales', [SalesController::class, 'getSales']);
    Route::get('/sales/{id}', [SalesController::class, 'getSaleById']);
    Route::post('/sales/save', [SalesController::class, 'saveSale']);
    Route::post('/sales/delete', [SalesController::class, 'deleteSale']);

    //Sales and Customers
    Route::get('/customers', [CustomersController::class, 'getCustomers']);
    Route::get('/customers/{id}', [CustomersController::class, 'getCustomerById']);
    Route::post('/customers/save', [CustomersController::class, 'saveCustomer']);
    Route::post('/customers/delete', [CustomersController::class, 'deleteCustomer']);

    //Audit
    Route::get('/reconciliations', [AuditController::class, 'getReconciliations']);
    Route::get('/reconciliations/{id}', [AuditController::class, 'getReconciliation']);
    Route::post('/reconciliations/save', [AuditController::class, 'saveReconciliation']);
    Route::post('/reconciliations/data/save', [AuditController::class, 'saveReconciliationData']);
    Route::post('/reconciliations/delete', [AuditController::class, 'deleteReconciliation']);

    //Feedback
    Route::post('/submit-feedback', [\App\Http\Controllers\Api\FeedbackController::class, 'saveFeedback']);

    //Test routes
    Route::get('/test-slack-notification', function (Request $request) {
        // return $request->user()->notify(new UserWelcomeNotification($request->user()));
        return $request->user()->notify(new ActivityErrorNotification($request->user(),'Error notification'));
    });

    //Test routes
    Route::get('/testing', function (Request $request) {
        $production = Production::find(26);
        $prod = new ProductionController();

        // return $production;
        return $prod->checkForMaterialsLevel($production);
        // return $request->user()->notify(new UserWelcomeNotification($request->user()));
        // return $request->user()->notify(new ActivityErrorNotification($request->user(),'Error notification'));
    });
    Route::get('/test', function () {
        $testing = new DashboardController();
        return $testing->saleChannelRatePlotValues('2024-07-01');
    });

});

    Route::get('/test2', function (Request $request) {
        // $materials = DB::table('intermediate_goods_materials')
        //                 ->join('intermediate_goods', 'intermediate_goods.id','=', 'intermediate_goods_materials.intermediate_good_id')
        //                 ->join('materials', 'materials.id','=', 'intermediate_goods_materials.material_id')
        //                 ->select('materials.cost_per_unit','materials.id','intermediate_goods_materials.quantity')
        //                 // ->select(DB::raw('SUM(materials.cost_per_unit) as total_cost_per_unit'))
        //                 ->where('intermediate_goods_materials.intermediate_good_id',1)
        //                 ->orderBy('materials.id')
        //                 ->get();
        //                 return $materials;

        
        $materials = DB::table('intermediate_goods_materials')
        ->join('intermediate_goods', 'intermediate_goods.id','=', 'intermediate_goods_materials.intermediate_good_id')
        ->join('materials', 'materials.id','=', 'intermediate_goods_materials.material_id')
        ->select('materials.cost_per_unit','materials.id','intermediate_goods_materials.quantity')
        ->where('intermediate_goods_materials.intermediate_good_id',3)
        ->orderBy('materials.id')
        ->get();

        //Loop through to calculate the total cost per unit for each intermediate good
        $total_materials_unit_cost = 0;
        foreach($materials as $material) {
            $total_materials_unit_cost += $material->quantity * $material->cost_per_unit;
        }
        return $total_materials_unit_cost;


        // return $request->bearerToken();
        // $product = Product::find(29);
        // return $product->intermediateGoods;
        // return ProductFullResource::make($product);
        // return IntermediateGoodVitalResource::collection(IntermediateGood::whereIn('id',$product->intermediateGoods->pluck('intermediate_good_id')->toArray())->get());
        // return $request->user()->pendingEmailChange();
    });

Route::get('/slack-testing', function () {
    $data = "Awesome";
    Log::channel('slack_gauge_feedback')->info($data);
    return "Done";
});

Route::get('/php', function() {
    return phpinfo();
});

Route::get('/get-options', function (Request $request) {
    $data = null;
    if($data = config('options.'.$request->option_type)) {
        return response()->json($data);
    }
    return response()->json($data);
});
