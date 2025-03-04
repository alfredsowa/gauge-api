<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Audit\ReconciliationResource;
use App\Models\Business;
use App\Models\Material;
use App\Models\Reconciliation;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{
    use ApiResponseHelpers;

    public function getReconciliations(Request $request) {
        $data = Reconciliation::where('business_id', $request->user()->business_id)->get();

        $results = ReconciliationResource::collection($data);
        return $this->respondWithSuccess(['data' => $results]);
    }

    public function getReconciliation(Request $request,$id) {
        $data = Reconciliation::where('business_id', $request->user()->business_id)
        ->where('id', $id)
        ->first();

        $results = ReconciliationResource::make($data);
        return $this->respondWithSuccess($results);
    }

    public function saveReconciliation(Request $request) {
        $business_id = $request->user()->business_id;

        if(!Business::findOrFail($business_id)) {
            return $this->respondError('You must be associated with a business');
        }

        $validator = Validator::make($request->all(), [
            'title' => [
                'bail','required','min:3','string'
            ],
            'type' => [
                'bail','required','string'
            ],
            'period' => [
                'bail','required','string',
            ]
        ],[
            'title.required' => 'Title is required',
            'title.min' => 'Title should be at least 3 characters',
            'type.required' => 'Type is required',
            'period.required' => 'Period is required',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $reconciliation = new Reconciliation();
        $reconciliation->business_id = $business_id;
        $reconciliation->title = $request->title;
        $reconciliation->type = $request->type;
        $reconciliation->period = date('Y-m-d',strtotime($request->period));
        $reconciliation->categories = $request->categories;
        $reconciliation->user_id = $request->user()->id;

        $data = [];
        $categories_affected_array = $request->categories;

        if(sizeof($categories_affected_array) > 0) {
            $categories_affected = Material::with(['category:id,title'])->where('business_id',$business_id)
            ->whereIn('material_category_id',$categories_affected_array)
            ->get();
        }
        else {
            $categories_affected = Material::with(['category:id,title'])->where('business_id',$business_id)
            ->get();
        }

        foreach($categories_affected as $category_affected) {
            $data[] = [
                "category" => $category_affected->category->title,
                "name" => $category_affected->name,
                "unit" => $category_affected->unit_of_measurement,
                "current_stock" => $category_affected->current_stock_level,
                "actual_stock"  =>  null,
                "key"           =>  $category_affected->id,
                "note"           =>  null,
            ];
        }

        // return $categories_affected;

        $reconciliation->data = json_encode($data);
        
        if($reconciliation->save()){
            return $this->respondWithSuccess([
                'saved' => true,
                'message' => 'Reconciliation was successfully created',
                'id'=>$reconciliation->id]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function saveReconciliationData(Request $request) {
        $business_id = $request->user()->business_id;

        $reconciliation = Reconciliation::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$reconciliation) {
            return $this->respondError('Reconciliation not found');
        }

        $reconciliation->data = json_encode($request->data);

        if($request->completed == 'true' || $request->completed == true) {
            $reconciliation->closed = true;
            $reconciliation->closed_on = now();
            $reconciliation->paused = false;
        }

        if($reconciliation->save()){

            if($reconciliation->closed) {
                foreach($request->data as $data) {
                    $material = Material::where('business_id',$business_id)->where('id',$data['key'])->first();
                    if($material) {
                        $material->current_stock_level = $data['actual_stock'];
                        $material->save();
                    }
                }
            }

            $results = ReconciliationResource::make($reconciliation);
            return $this->respondWithSuccess(['saved'=>true,'data'=>$results]);
        }
        else {
            return $this->respondError('Reconciliation did not save. Try again.');
        }
        
    }

    public function deleteReconciliation(Request $request) {
        $business_id = $request->user()->business_id;

        $reconciliation = Reconciliation::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$reconciliation) {
            return $this->respondError('Reconciliation not found');
        }

        if($reconciliation->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Reconciliation has been deleted.']);
        }
        else {
            return $this->respondError('Reconciliation was not deleted.');
        }
        
    }
}
