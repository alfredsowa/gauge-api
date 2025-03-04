<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Product\ProductBasicResource;
use App\Http\Resources\Product\ProductFullResource;
use App\Http\Resources\Product\ProductIntermediateGoodVitalResource;
use App\Models\Business;
use App\Models\IntermediateGood;
use App\Models\Material;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\ProductsIntermediateGood;
use App\Models\ProductsMaterial;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class ProductsController extends Controller
{

    use ApiResponseHelpers;
    public function getProducts(Request $request) {
        $products = ProductBasicResource::collection(Product::where('business_id', $request->user()->business_id)->orderBy('name')->get());

        return $this->respondWithSuccess(['data'=>$products]);
    }

    public function getProduct(Request $request,$id) {
        $product = ProductBasicResource::make(Product::where('business_id', $request->user()->business_id)
        ->where('id',$id)->first());

        return $this->respondWithSuccess($product);
    }

    public function getFullProductById(Request $request,$id) {
        $product = ProductFullResource::make(Product::where('business_id', $request->user()->business_id)
        ->where('id',$id)->first());

        return $this->respondWithSuccess($product);
    }

    public function getFullProduct(Request $request,$slug) {
        $product = ProductFullResource::make(Product::where('business_id', $request->user()->business_id)
        ->where('slug',$slug)->first());

        return $this->respondWithSuccess($product);
    }

    public function saveProduct(Request $request) {

        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'bail','required','min:2',
                Rule::unique('products')->where(function ($query) use ($request,$business_id) {
                    return $query->where('name', $request->name)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ],
            'sku' => [
                'bail','nullable','min:4',
                Rule::unique('products')->where(function ($query) use ($request,$business_id) {
                    return $query->where('sku', $request->sku)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ],
            'file' => ['bail','nullable',File::image()->max(5024),'mimes:jpg,jpeg,png'],
            'price' => [
                'bail','nullable','min:0','numeric',
            ],
            'wholesale_price' => [
                'bail','nullable','min:0','numeric',
            ],
            'labour_cost' => [
                'bail','nullable','min:0','numeric',
            ],
            'discount_price' => [
                'bail','nullable','min:0','numeric',
            ],
            'stock_quantity' => [
                'bail','nullable','min:0','numeric',
            ],
            'min_stock_quantity' => [
                'bail','nullable','min:0','numeric',
            ],
            'description' => [
                'bail','nullable','string','min:5',
            ],
        ],[
            'name.required' => 'Product name is required',
            'name.min' => 'Product name should be at least 3 characters',
            'name.unique' => 'Product name already exists',
            'sku.min' => 'SKU should be at least 6 characters',
            'sku.unique' => 'SKU already exists',
            'price.min' => 'Retail price must be 0 and above',
            'price.numeric' => 'Retail price must be a number',
            'labour_cost.numeric' => 'Labour cost must be a number',
            'wholesale_price.min' => 'Wholesale price must be 0 and above',
            'wholesale_price.numeric' => 'Wholesale price must be a number',
            'discount_price.min' => 'Discount must be 0 and above',
            'discount_price.numeric' => 'Discount must  must be a number',
            'stock_quantity.min' => 'Stock Quantity must be 0 and above',
            'stock_quantity.numeric' => 'Stock Quantity must  must be a number',
            'min_stock_quantity.min' => 'Minimum Stock Quantity must be 0 and above',
            'min_stock_quantity.numeric' => 'Minimum Stock Quantity must  must be a number',
            'file.mimes' => 'Image must be of types JPG, PNG, JPEG',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        if($request->id){
            $product = Product::find($request->id);
            $product->business_id = $business_id;

            if($product->name != $request->name) {
                $product->slug = Str::slug( $request->name);
            }
        }
        else {
            $product = new Product();
            $product->business_id = $business_id;
            $product->slug = Str::slug( $request->name);
            $product->is_active = true;
            $product->is_produced = true;
        }

        if(!$product){
            return $this->respondError('Invalid Product');
        }

        $product->user_id = $request->user()->id;
        $product->name = $request->name;
        $product->sku = $request->sku;
        $product->price = $request->price;
        $product->wholesale_price = $request->wholesale_price;
        $product->wholesale_markup = $request->wholesale_markup;
        $product->retail_markup = $request->retail_markup;
        $product->use_manual_pricing = ($request->use_manual_pricing == 'true')?1:0;
        $product->labour_cost = $request->labour_cost;
        $product->discount_price = $request->discount_price;
        $product->stock_quantity = $request->stock_quantity;
        $product->min_stock_quantity = $request->min_stock_quantity;
        $product->is_produced = ($request->is_produced == 'true')?1:0;
        $product->is_active = ($request->is_active == 'true')?1:0;
        $product->description = $request->description;

        if($request->hasFile('file')) {
            $path = $request->file('file')->store('product/'.$request->user()->business_id,'public');
            $product->image = env('APP_URL').'storage/'.$path;
        }

        if($product->save()) {
            return $this->respondWithSuccess(['saved' =>true,'message' =>'Product was successfully updated',
            'product_slug'=>$product->slug]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function duplicateProduct(Request $request) {
        $business_id = $request->user()->business_id;

        $product = Product::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$product) {
            return $this->respondError('Product not found');
        }

        $name = $product->name;
        $existing_names = Product::where('name', 'like' ,"$name%")->where('business_id',$business_id)->count();
        if($existing_names > 0) {
            $name = $product->name.' Copy ('.$existing_names.')';
        }

        $sku = $product->sku;
        if(!isNull($sku)) {
            $existing_sku = Product::where('sku', 'like' ,"$sku%")->where('business_id',$business_id)->count();
            if($existing_sku > 0) {
                $sku = $product->sku.'-'.$existing_sku;
            }
        }

        $newProduct = new Product();
        $newProduct->business_id = $business_id;
        $newProduct->user_id = $request->user()->id;
        $newProduct->name = $name;
        $newProduct->slug = Str::slug($name);
        $newProduct->sku = $sku;
        $newProduct->price = $product->price;
        $newProduct->wholesale_price = $product->wholesale_price;
        $newProduct->wholesale_markup = $product->wholesale_markup;
        $newProduct->retail_markup = $product->retail_markup;
        $newProduct->use_manual_pricing = $product->use_manual_pricing;
        $newProduct->labour_cost = $product->labour_cost;
        $newProduct->discount_price = $product->discount_price;
        $newProduct->stock_quantity = $product->stock_quantity;
        $newProduct->min_stock_quantity = $product->min_stock_quantity;
        $newProduct->is_produced = $product->is_produced;
        $newProduct->is_active = false;
        $newProduct->description = $product->description;
        $newProduct->image = $product->image;

        if($newProduct->save()) {
            foreach($product->materials as $material) {
                $newMaterial = new ProductsMaterial();
                $newMaterial->product_id = $newProduct->id;
                $newMaterial->material_id = $material->material_id;
                $newMaterial->quantity = $material->quantity;
                $newMaterial->cost = $material->cost;
                $newMaterial->save();
            }
            foreach($product->intermediateGoods as $material) {
                $newIntermediateGood = new ProductsIntermediateGood();
                $newIntermediateGood->product_id = $newProduct->id;
                $newIntermediateGood->intermediate_good_id = $material->intermediate_good_id;
                $newIntermediateGood->quantity = $material->quantity;
                $newIntermediateGood->save();
            }
            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Product was successfully duplicated',
            'data'=>ProductBasicResource::make($newProduct)]);
        }
        else {
            return $this->respondError('Sorry! Please try again.');
        }
    }

    public function deleteProduct(Request $request) {
        $business_id = $request->user()->business_id;

        $product = Product::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$product) {
            return $this->respondError('Purchase not found');
        }

        if($product->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Product has been deleted.']);
        }
        else {
            return $this->respondError('Product was not deleted.');
        }

    }

    public function removePhoto(Request $request){
        try {
            $product = Product::where('id',$request->id)->where('business_id',$request->user()->business_id)->first();
            $product->image = null;
            $product->save();

            return $this->respondWithSuccess([
            'message'=>'Product image removed',
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    public function saveProductMaterial(Request $request) {

        $validator = Validator::make($request->all(), [
            'product_id' => ['bail','required','integer','min:1'],
            'material_type' => ['bail','required','string','in:Intermediate,Material'],
            'material_id' => ['bail','required','integer','min:1'],
            'quantity' => ['bail','nullable','min:0'],
        ],[
            'product_id.required' => 'Invalid production',
            'material_id.required' => 'Material is required',
            'material_id.min' => 'Invalid material selected',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $product = Product::where('id',$request->product_id)->where('business_id',request()->user()->business_id)->first();
        if(!$product) return $this->respondError('Invalid product in use.');

        if($request->material_type == 'Material') {
            $material = Material::where('id',$request->material_id)->where('business_id',request()->user()->business_id)->first();
            if(!$material) return $this->respondError('Invalid material in selected.');

            $material_saved = ProductsMaterial::where('product_id',$request->product_id)
            ->where('material_id',$request->material_id)
            ->first();

            if(!$material_saved) {
                $material_saved = new ProductsMaterial();
                $material_saved->product_id = $request->product_id;
                $material_saved->material_id = $request->material_id;
            }

            $material_saved->quantity = $request->quantity;
        }
        else {
            $material = IntermediateGood::where('id',$request->material_id)->where('business_id',request()->user()->business_id)->first();
            if(!$material) return $this->respondError('Invalid intermediate goods selected.');

            $material_saved = ProductsIntermediateGood::where('product_id',$request->product_id)
            ->where('intermediate_good_id',$request->material_id)
            ->first();

            if(!$material_saved) {
                $material_saved = new ProductsIntermediateGood();
                $material_saved->product_id = $request->product_id;
                $material_saved->intermediate_good_id = $request->material_id;
            }

            $material_saved->quantity = $request->quantity;
        }

        if($material_saved->save()) {

            if($request->material_type == 'Material') {
                $materials = DB::table('products_materials')
                ->join('products', 'products.id','=', 'products_materials.product_id')
                ->join('materials', 'materials.id','=', 'products_materials.material_id')
                ->select('products_materials.id', 'materials.name', 'materials.current_stock_level','materials.cost_per_unit',
                'materials.image','materials.unit_of_measurement','products_materials.quantity','products_materials.cost')
                ->where('products_materials.product_id',$request->product_id)
                ->orderBy('materials.name')
                ->get();
            }
            else {
                $materials = ProductIntermediateGoodVitalResource::collection($product->intermediateGoods);
            }

            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Production material has been saved.',
            'data' => $materials
            ]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }

    }

    public function deleteProductionMaterial(Request $request) {
        $business_id = $request->user()->business_id;

        $productMaterial = ProductsMaterial::where('product_id', $request->product_id)->where('id',$request->id)->first();
        $product = Product::where('id', $request->product_id)->where('business_id',$business_id)->first();

        if(!$productMaterial) {
            return $this->respondError('Material not found');
        }

        if(!$product) {
            return $this->respondError('Product not found');
        }

        if($productMaterial->delete()){

            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Material has been deleted.']);
        }
        else {
            return $this->respondError('Material was not deleted.');
        }

    }

    public function deleteProductIntermediateGood(Request $request) {
        $business_id = $request->user()->business_id;

        $productMaterial = ProductsIntermediateGood::where('product_id', $request->product_id)->where('id',$request->id)->first();
        $product = Product::where('id', $request->product_id)->where('business_id',$business_id)->first();

        if(!$productMaterial) {
            return $this->respondError('Intermediate Good not found');
        }

        if(!$product) {
            return $this->respondError('Product not found');
        }

        if($productMaterial->delete()){

            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Material has been deleted.']);
        }
        else {
            return $this->respondError('Material was not deleted.');
        }

    }

    public function getProductMaterialCostArray(Request $request,$id) {

        $product = Product::where('id',$id)->where('business_id',request()->user()->business_id)->first();
        if(!$product) return $this->respondError('Invalid product.');

        $materials_cost = Material::where('business_id',request()->user()->business_id)->pluck('cost_per_unit','id')->toArray();

        $cost = 0;

        foreach($product->materials as $material) {
            if(array_key_exists($material->material_id, $materials_cost)) {
                $cost += $materials_cost[$material->material_id] * $material->quantity;
            }
        }

        foreach($product->intermediateGoods as $good) {
            $used_goods_total = 0;
            $used_goods = DB::table('materials')
            ->join('intermediate_goods_materials', 'materials.id','=','intermediate_goods_materials.material_id')
            ->join('intermediate_goods', 'intermediate_goods.id','=','intermediate_goods_materials.intermediate_good_id')
            ->join('products_intermediate_goods', 'products_intermediate_goods.intermediate_good_id','=','intermediate_goods.id')
            ->select(['materials.cost_per_unit as material_unit_cost','intermediate_goods_materials.quantity as material_quantity_used',
            'products_intermediate_goods.quantity as goods_quantity_used','intermediate_goods.labour_cost as good_per_unit_labour'])
            ->where('intermediate_goods.id','=',$good->intermediate_good_id)
            ->get();

            $labour = 0;
            foreach($used_goods as $used_good) {
                $labour = $used_good->good_per_unit_labour;
                $used_goods_total += $used_good->material_unit_cost * $used_good->material_quantity_used;
            }

            $used_goods_total = ($labour + $used_goods_total)  * $good->quantity;

            $cost += $used_goods_total;
        }

        $overheads = Overhead::where('business_id','=',$request->user()->business_id)->sum('cost');
        $average_goods_monthly = $request->user()->business->average_goods_monthly;

        $overhead_cost = 0;
        // if($average_goods_monthly > 0) {
        //     $overhead_cost = round($overheads / $average_goods_monthly, 2);
        // }

        return [
            'materials_cost' => $cost,
            'materials_cost_with_product_labour' => $cost+$product->labour_cost,
            'overheaad' => $overhead_cost,
            'total_cost_of_goods' => $cost + $product->labour_cost + $overhead_cost,
        ];
    }

    public function getProductMaterialCost(Request $request,$id) {
        return $this->respondWithSuccess($this->getProductMaterialCostArray($request,$id));
    }
}
