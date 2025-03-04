<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Material\MaterialResource;
use App\Http\Resources\Material\MaterialsBasicResource;
use App\Http\Resources\Material\MaterialViewResource;
use App\Models\Business;
use App\Models\Material;
use App\Models\MaterialCategory;
use App\Models\Supplier;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class MaterialsController extends Controller
{
    use ApiResponseHelpers;

    public function getMaterial(Request $request,$id) {
        $business_id = $request->user()->business_id;

        $material = Material::where('id','=', $id)
            ->where('business_id','=', $business_id)->first();

        return $this->respondWithSuccess(MaterialResource::make($material));

    }

    public function viewMaterial(Request $request,$id) {
        $business_id = $request->user()->business_id;

        $material = Material::with(['category','purchases'])->where('id','=', $id)
            ->where('business_id','=', $business_id)->first();

            return $this->respondWithSuccess(MaterialViewResource::make($material));

    }

    public function getMaterialsForOptions(Request $request) {
        $business_id = $request->user()->business_id;
        if(isset($request->business_id)) {
            $business_id = $request->business_id;
        }

        if ($request->withoutComponents == 'true' or $request->withoutComponents == true) {
            return $this->respondWithSuccess(MaterialsBasicResource::collection(
                Material::where('business_id',$business_id)
                ->where('is_component',false)
                ->orderBy('name')
                ->get()));
        }
        else {
            return $this->respondWithSuccess(MaterialsBasicResource::collection(Material::where('business_id',$business_id)->orderBy('name')->get()));
        }
    }

    public function getMaterials(Request $request) {
        $business_id = $request->user()->business_id;
        if(isset($request->business_id)) {
            $business_id = $request->business_id;
        }
        $per_page = 10;
        if(isset($request->per_page)) {
            $per_page = $request->per_page;
        }

        $page = 1;
        if(isset($request->page)) {
            $page = $request->page;
        }

        return MaterialResource::collection(
            Material::with('category')
            ->where('business_id',$business_id)
            ->orderBy('name','asc')
            ->paginate($per_page)
        );
    }

    public function getBasicMaterials(Request $request) {
        $business_id = $request->user()->business_id;
        if(isset($request->business_id)) {
            $business_id = $request->business_id;
        }

        return $this->respondWithSuccess(MaterialsBasicResource::collection(Material::where('business_id',$business_id)->orderBy('name','asc')->get()));
    }

    public function newMatrial(Request $request) {
        $business_id = $request->user()->business_id;
        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'bail','required','min:3',
                Rule::unique('materials')->where(function ($query) use ($request,$business_id) {
                    return $query->where('name', $request->name)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ],
            'image' => ['bail','nullable',File::image()->max(5024),'mimes:jpg,jpeg,png'],
            'code' => [
                'bail','nullable','min:3',
                Rule::unique('materials')->where(function ($query) use ($request,$business_id) {
                    return $query->where('name', $request->name)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ]
        ],[
            'name.required' => 'Material name is required',
            'name.min' => 'Material name should be at least 3 characters',
            'name.unique' => 'Material name already exists',
//            'code.required' => 'Code is required',
            'code.min' => 'Code should be at least 3 characters',
            'code.unique' => 'Code already exists',
            'image.max' => 'Image must be 5mb or less',
            'image.mimes' => 'Image must be of types JPG, PNG, JPEG',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $material = new Material();
        $material->name = $request->name;
        $material->code = $request->code;
        $material->type = $request->type;
        $material->material_category_id = $request->material_category_id;
        $material->added_by = $request->user()->id;
        $material->business_id = $business_id;
        $material->description = $request->description;
        $material->current_stock_level = $request->current_stock_level;
        $material->minimum_stock_level = $request->minimum_stock_level;
        $material->unit_of_measurement = $request->unit_of_measurement;
        $material->cost_per_unit = $request->cost_per_unit;
        $material->is_reusable_after_damaged = ($request->is_reusable_after_damaged == 'false' || $request->is_reusable_after_damaged == false) ? false : true;

        if($material->save()) {
            if ($request->image) {
                $path = $request->file('image')->store('material/'.$request->user()->business_id,'public');
                $material->image = env('APP_URL').'storage/'.$path;
                $material->save();
            }

            return $this->respondWithSuccess(['saved' =>true,'message' =>'Material was successfully created']);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function updateBasicInfo(Request $request) {

        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'name' => [
                'bail','required','min:3',
                // Rule::unique('materials','name')->ignore($request->id),
                Rule::unique('materials')->where(function ($query) use ($request,$business_id) {
                    return $query->where('name', $request->name)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ],
            'code' => [
                'bail','nullable','min:3',
                Rule::unique('materials')->where(function ($query) use ($request,$business_id) {
                    return $query->where('code', $request->code)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ],
            'type' => [
                'bail','required','min:2','string',
                Rule::in(config('options.material_type')),
            ]
        ],[
            'name.required' => 'Material name is required',
            'name.min' => 'Material name should be at least 3 characters',
            'name.unique' => 'Material name already exists',
            'code.min' => 'Code should be at least 3 characters',
            'code.unique' => 'Code already exists',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $material = Material::find($request->id);

        if(!$material){
            return $this->respondError('Invalid Material');
        }

        $material->name = $request->name;
        $material->code = $request->code;
        $material->type = $request->type;
        $material->unit_of_measurement = $request->unit_of_measurement;
        $material->cost_per_unit = $request->cost_per_unit;
        $material->minimum_stock_level = $request->minimum_stock_level;
        $material->is_reusable_after_damaged = ($request->is_reusable_after_damaged == 'false' || $request->is_reusable_after_damaged == false) ? false : true;
        $material->material_category_id = $request->material_category_id;
        $material->description = $request->description;

        if($material->save()) {
            return $this->respondWithSuccess(['saved' =>true,'message' =>'Material was successfully updated']);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function savePhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'file' => ['bail','required',File::image()->max(2024),'mimes:jpg,jpeg,png'],
        ],[
            'file.required' => 'Image is required!',
            'file.mimes' => 'Image must be of types JPG, PNG, JPEG',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }

            return $this->respondError($errorList);
        }

        try {
            $material = Material::where('id',$request->id)->where('business_id',$request->user()->business_id)->first();
            $path = $request->file('file')->store('material/'.$request->user()->business_id,'public');
            $material->image = env('APP_URL').'storage/'.$path;
            $material->save();

            return $this->respondWithSuccess([
            'message'=>'Material image updated',
            'path'=>$material->image,
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }

    }

    public function removePhoto(Request $request){
        try {
            $material = Material::where('id',$request->id)->where('business_id',$request->user()->business_id)->first();
            $material->image = null;
            $material->save();

            return $this->respondWithSuccess([
            'message'=>'Material image removed',
            'saved' =>  true]);
        } catch (\Throwable $th) {
            return $this->respondError($th->getMessage());;
        }
    }

    /**
     * Delete a material from the database.
     *
     * @param Request $request The request object containing the material id.
     * @return \Illuminate\Http\JsonResponse The response with success or error message.
     * @throws \Throwable If an error occurs during the deletion process.
     */
    public function deleteMaterial(Request $request){
        try {
            // Check if the material exists and belongs to the user's business
            if(Material::where('id',$request->id)->where('business_id',$request->user()->business_id)->delete()) {
                // If the material is deleted successfully, return a success response
                return $this->respondWithSuccess([
                    'message'=>'Material has been deleted',
                    'removed' =>  true]);
            }

            // If the material does not exist or does not belong to the user's business, return an error response
            return $this->respondError('Something went wrong. Please try again');

        } catch (\Throwable $th) {
            // If an error occurs during the deletion process, return an error response with the error message
            return $this->respondError($th->getMessage());
        }
    }

    /**
     * Save or update a material category.
     *
     * @param Request $request The request object containing the category data.
     * @return \Illuminate\Http\JsonResponse The response with success or error message.
     */
    public function saveCategory(Request $request) {

        $helper = new HelperOptionsController();

        // Get the business id associated with the user
        $business_id = $request->user()->business_id;

        // Check if the user is associated with a business
        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'title' => [
                'bail','required','min:3',
                Rule::unique('material_categories')->where(function ($query) use ($request,$business_id) {
                    return $query->where('title', $request->title)
                       ->where('business_id', $business_id);
                 })->ignore($request->id),
            ],
        ],[
            'title.required' => 'Category name is required',
            'title.min' => 'Category name should be at least 3 characters',
            'title.unique' => 'Category name already exists',
        ]);

        // If validation fails, return an error response with the validation errors
        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }
            return $this->respondError($errorList);
        }

        // If the request contains an id, update the existing category
        if ($request->id) {
            $category = MaterialCategory::find($request->id);
            $category->title = $request->title;
            $category->description = $request->description;
            $category->business_id = $business_id;
            if($category->save()) {
                return $this->respondWithSuccess([
                    'saved' =>true,
                    'message' =>'Category was successfully updated',
                    'categories' => $helper->materialCategories($request)
                ]);
            }
            else {
                return $this->respondError('Information was not saved.');
            }
        }
        // If the request does not contain an id, create a new category
        else {
            $category = new MaterialCategory();
            $category->title = $request->title;
            $category->description = $request->description;
            $category->business_id = $business_id;
            if($category->save()) {
                return $this->respondWithSuccess(['saved' =>true,'message' =>'Category was successfully created',
                'categories' => $helper->materialCategories($request)]);
            }
            else {
                return $this->respondError('Information was not saved.');
            }
        }
    }
}
