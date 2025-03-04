<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IntermediateGood\IntermediateGoodBasicResource;
use App\Http\Resources\IntermediateGood\IntermediateGoodFullResource;
use App\Models\Business;
use App\Models\Material;
use App\Models\IntermediateGood;
use App\Models\IntermediateGoodsMaterial;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class IntermediateGoodsController extends Controller
{
    use ApiResponseHelpers;

    public function getIntermediateGoods(Request $request) {
        $intermediateGoods = IntermediateGoodBasicResource::collection(IntermediateGood::where('business_id', $request->user()->business_id)->orderBy('name')->get());

        return $this->respondWithSuccess(['data'=>$intermediateGoods]);
    }

    public function getIntermediateGood(Request $request,$id) {
        $intermediateGood = IntermediateGoodBasicResource::make(IntermediateGood::where('business_id', $request->user()->business_id)
        ->where('id',$id)->first());

        return $this->respondWithSuccess($intermediateGood);
    }

    public function getFullIntermediateGood(Request $request,$slug) {
        $intermediateGood = IntermediateGoodFullResource::make(IntermediateGood::where('business_id', $request->user()->business_id)
        ->where('slug',$slug)->first());

        return $this->respondWithSuccess($intermediateGood);
    }

    public function saveIntermediateGood(Request $request) {

        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'bail','required','min:2',
                Rule::unique('intermediate_goods')->where(function ($query) use ($request,$business_id) {
                    return $query->where('name', $request->name)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
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
            'file' => ['bail','nullable',File::image()->max(5024),'mimes:jpg,jpeg,png'],
        ],[
            'name.required' => 'Intermediate Good name is required',
            'name.min' => 'Intermediate Good name should be at least 3 characters',
            'name.unique' => 'Intermediate Good name already exists',
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
            $intermediateGood = IntermediateGood::find($request->id);
            $intermediateGood->business_id = $business_id;

            if($intermediateGood->name != $request->name) {
                $intermediateGood->slug = Str::slug( $request->name);
            }
        }
        else {
            $intermediateGood = new IntermediateGood();
            $intermediateGood->business_id = $business_id;
            $intermediateGood->slug = Str::slug( $request->name);
        }

        if(!$intermediateGood){
            return $this->respondError('Invalid IntermediateGood');
        }

        $intermediateGood->user_id = $request->user()->id;
        $intermediateGood->name = $request->name;
        $intermediateGood->stock_quantity = $request->stock_quantity;
        $intermediateGood->min_stock_quantity = $request->min_stock_quantity;
        $intermediateGood->intermediate_goods_category_id = $request->category;
        $intermediateGood->status = $request->status?1:0;
        $intermediateGood->is_reusable_after_damaged = $request->is_reusable_after_damaged?1:0;
        $intermediateGood->labour_cost = $request->labour_cost;
        $intermediateGood->description = $request->description;

        if($request->hasFile('file')) {
            $path = $request->file('file')->store('intermediate-goods/'.$request->user()->business_id,'public');
            $intermediateGood->image = env('APP_URL').'storage/'.$path;
        }

        if($intermediateGood->save()) {
            return $this->respondWithSuccess(['saved' =>true,'message' =>'Intermediate Good was successfully updated',
            'intermediate_good_slug'=>$intermediateGood->slug]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function duplicateIntermediateGood(Request $request) {
        $business_id = $request->user()->business_id;

        $intermediateGood = IntermediateGood::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$intermediateGood) {
            return $this->respondError('Intermediate Good not found');
        }

        $name = $intermediateGood->name;
        $existing_names = IntermediateGood::where('name', 'like' ,"$name%")->where('business_id',$business_id)->count();
        if($existing_names > 0) {
            $name = $intermediateGood->name.' Copy ('.$existing_names.')';
        }

        $sku = $intermediateGood->sku;
        if(!isNull($sku)) {
            $existing_sku = IntermediateGood::where('sku', 'like' ,"$sku%")->where('business_id',$business_id)->count();
            if($existing_sku > 0) {
                $sku = $intermediateGood->sku.'-'.$existing_sku;
            }
        }

        $newIntermediateGood = new IntermediateGood();
        $newIntermediateGood->business_id = $business_id;
        $newIntermediateGood->user_id = $request->user()->id;
        $newIntermediateGood->name = $name;
        $newIntermediateGood->slug = Str::slug($name);
        $newIntermediateGood->sku = $sku;
        $newIntermediateGood->price = $intermediateGood->price;
        $newIntermediateGood->wholesale_price = $intermediateGood->wholesale_price;
        $newIntermediateGood->discount_price = $intermediateGood->discount_price;
        $newIntermediateGood->stock_quantity = $intermediateGood->stock_quantity;
        $newIntermediateGood->min_stock_quantity = $intermediateGood->min_stock_quantity;
        $newIntermediateGood->is_produced = $intermediateGood->is_produced;
        $newIntermediateGood->is_reusable_after_damaged = $intermediateGood->is_reusable_after_damaged;
        $newIntermediateGood->is_active = false;
        $newIntermediateGood->description = $intermediateGood->description;
        $newIntermediateGood->image = $intermediateGood->image;

        if($newIntermediateGood->save()) {
            foreach($intermediateGood->materials as $material) {
                $newMaterial = new IntermediateGoodsMaterial();
                $newMaterial->intermediate_good_id = $newIntermediateGood->id;
                $newMaterial->material_id = $material->material_id;
                $newMaterial->quantity = $material->quantity;
                $newMaterial->cost = $material->cost;
                $newMaterial->save();
            }
            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'IntermediateGood was successfully duplicated',
            'data'=>IntermediateGoodBasicResource::make($newIntermediateGood)]);
        }
        else {
            return $this->respondError('Sorry! Please try again.');
        }
    }

    public function deleteIntermediateGood(Request $request) {
        $business_id = $request->user()->business_id;

        $intermediateGood = IntermediateGood::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$intermediateGood) {
            return $this->respondError('Purchase not found');
        }

        if($intermediateGood->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'IntermediateGood has been deleted.']);
        }
        else {
            return $this->respondError('IntermediateGood was not deleted.');
        }

    }

    public function removePhoto(Request $request){
        try {
            $intermediateGood = IntermediateGood::where('id',$request->id)->where('business_id',$request->user()->business_id)->first();
            $intermediateGood->image = null;
            $intermediateGood->save();

            return $this->respondWithSuccess([
            'message'=>'IntermediateGood image removed',
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    public function saveIntermediateGoodMaterial(Request $request) {

        $validator = Validator::make($request->all(), [
            'intermediate_good_id' => ['bail','required','integer','min:1'],
            'material_id' => ['bail','required','integer','min:1'],
            'quantity' => ['bail','nullable','min:0'],
        ],[
            'intermediate_good_id.required' => 'Invalid intermediate good',
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

        $intermediate_good = IntermediateGood::where('id',$request->intermediate_good_id)->where('business_id',request()->user()->business_id)->first();
        if(!$intermediate_good) return $this->respondError('Invalid intermediate good in use.');

        $material = Material::where('id',$request->material_id)->where('business_id',request()->user()->business_id)->first();
        if(!$material) return $this->respondError('Invalid material in selected.');

        $material_saved = IntermediateGoodsMaterial::where('intermediate_good_id',$request->intermediate_good_id)
        ->where('material_id',$request->material_id)
        ->first();

        if(!$material_saved) {
            $material_saved = new IntermediateGoodsMaterial();
            $material_saved->intermediate_good_id = $request->intermediate_good_id;
            $material_saved->material_id = $request->material_id;
        }

        $material_saved->quantity = $request->quantity;

        if($material_saved->save()) {

            $materials = DB::table('intermediate_goods_materials')
            ->join('intermediate_goods', 'intermediate_goods.id','=', 'intermediate_goods_materials.intermediate_good_id')
            ->join('materials', 'materials.id','=', 'intermediate_goods_materials.material_id')
            ->select('intermediate_goods_materials.id', 'materials.name', 'materials.current_stock_level', 'materials.cost_per_unit',
            'materials.image','materials.unit_of_measurement','intermediate_goods_materials.quantity','intermediate_goods_materials.cost')
            ->where('intermediate_goods_materials.intermediate_good_id',$request->intermediate_good_id)
            ->orderBy('materials.name')
            ->get();

            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Intermediate good material has been saved.',
            'data' => $materials
            ]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }

    }

    public function deleteProductionMaterial(Request $request) {
        $business_id = $request->user()->business_id;

        $intermediateGoodMaterial = IntermediateGoodsMaterial::where('intermediate_good_id', $request->intermediate_good_id)->where('id',$request->id)->first();
        $intermediateGood = IntermediateGood::where('id', $request->intermediate_good_id)->where('business_id',$business_id)->first();

        if(!$intermediateGoodMaterial) {
            return $this->respondError('Material not found');
        }

        if(!$intermediateGood) {
            return $this->respondError('IntermediateGood not found');
        }

        if($intermediateGoodMaterial->delete()){

            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Material has been deleted.']);
        }
        else {
            return $this->respondError('Material was not deleted.');
        }

    }
}
