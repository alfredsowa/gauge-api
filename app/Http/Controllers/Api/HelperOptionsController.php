<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Material\MaterialCategoryResource;
use App\Http\Resources\Product\ProductCategoryResource;
use App\Http\Resources\Product\ProductTypeResource;
use App\Models\MaterialCategory;
use App\Models\ProductsCategory;
use App\Models\ProductType;
use Illuminate\Http\Request;

class HelperOptionsController extends Controller
{

    /** Get collection of all the material categories for a given user using the business id
     * @var array
     */
    public function materialCategories(Request $request) {
        return MaterialCategoryResource::collection(MaterialCategory::where('business_id','=',$request->user()->business_id)->get());
    }

    /** Get collection of all the product categories for a given user using the business id
     * @var array
     */
    // public function productCategories(Request $request) {
    //     return ProductCategoryResource::collection(ProductCategory::where('business_id','=',$request->user()->business_id)->get());
    // }

    /** Get collection of all the product type for a given user using the business id
     * @var array
     */
    public function productTypes(Request $request) {
        return ProductTypeResource::collection(ProductType::where('business_id','=',$request->user()->business_id)->get());
    }

    public function getGuide(Request $request)
    {
        return response()->json($request->user()->guide);
    }
    public function updateGuide(Request $request)
    {
        $request->user()->guide = json_encode($request->guide);
        $request->user()->save();
    }
}
