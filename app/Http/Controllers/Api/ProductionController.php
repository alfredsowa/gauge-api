<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Production\ProductionBasicResource;
use App\Http\Resources\Production\ProductionHistoryResource;
use App\Http\Resources\Production\ProductionResource;
use App\Models\IntermediateGood;
use App\Models\Material;
use Illuminate\Support\Str;
use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionHistory;
use App\Models\ProductionMaterial;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductionController extends Controller
{
    use ApiResponseHelpers;

    public function getProductions(Request $request) {

        $business_id = $request->user()->business_id;

        if(isset($request->business_id)) {
            $business_id = $request->business_id;
        }

        return $this->respondWithSuccess(['data' => ProductionBasicResource::collection(Production::where('business_id',$business_id)->orderBy('created_at','desc')->get())]);
    }

    public function getBasicProduction(Request $request,$id) {

        $business_id = $request->user()->business_id;

        $production = Production::where('business_id',$business_id)->where('id',$id)->first();

        return $this->respondWithSuccess(ProductionBasicResource::make($production));
    }

    public function getFullProduction(Request $request,$id) {

        $business_id = $request->user()->business_id;

        $production = Production::where('business_id',$business_id)->where('id',$id)->first();

        return $this->respondWithSuccess(ProductionResource::make($production));
    }

    public function updateProductionStatus(Request $request,$id) {

        $validator = Validator::make($request->all(), [
            'status' => ['bail','required','string','min:3',Rule::in(config('options.production_status'))],
            'description' => ['bail','nullable','string','min:5'],
        ],[
            'status.required' => 'Status is required',
            'status.min' => 'Invalid status selected',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $business_id = $request->user()->business_id;

        $production = Production::with(['materials'])->where('business_id',$business_id)->where('id',$id)->first();

        if ($request->status == $production->status) {
            return $this->respondError('Production is already in this status.');
        }

        $total_cost = 0;
        $product = null;
        $intermediate_good = null;
        $message = 'Production status updated';

        if ($production->category == 'product') {

            if ($production->type == 'product') {

                //Get Product details
                $product = Product::where('business_id',$business_id)->where('id',$production->product_id)->first();

                //Check if product is available. Return eror if product is not available
                if(!$product) {
                    return $this->respondError('Product not found');
                }
                
            }
            else {

                //Get Product details
                $intermediate_good = IntermediateGood::where('business_id',$business_id)->where('id',$production->intermediate_good_id)->first();

                //Check if product is available. Return eror if product is not available
                if(!$intermediate_good) {
                    return $this->respondError('Intermediate Good not found');
                }

            }

        }

        $production->status = $request->status;

        //Check if product is completed or damaged
        if ($request->status == 'cancel' || $request->status == 'damaged') {

            //Get all materials used for production
            $materials_used = $production->materials;

            //Check if materials are used
            if($materials_used->count() > 0) {

                //Loop through materials used and calculate total cost and
                //save production into materials if production type is "Component" and or marked as "New Material
                // $total_cost = $this->loopThroughMaterialsUsed($materials_used, $total_cost,$business_id,$production);
                
                if(!$this->clearProductionMaterials($request,$production)) {
                    return $this->respondError('Unable to clear production materials. Please try again.');
                }
            }
            $message = "Production has been cancelled";

            if($production->status == 'damaged'){
                $message = "Production has been damaged";
            }

        }

        if($request->status == 'completed') {

            //Perform operation when production status is completed and is a product
            if ($production->type == 'product') {
                $product->stock_quantity += $production->quantity;
                $product->save();
            }

            if($production->type == 'intermediate_good') {

                $intermediate_good->stock_quantity += $production->quantity;
                $intermediate_good->save();
            }

            $message = "Production has been completed successfully";
            $production->completed_at = date(now());
        }

        if($production->save()){

            $this->logProductionHistory($production, $request->note);

            return $this->respondWithSuccess([
                'saved'     =>  true,
                'message'   =>  $message,
                'data'      =>  ProductionHistoryResource::collection(ProductionHistory::where('production_id',$production->id)->orderBy('created_at','desc')->get())
            ]);
        }
        else {
            if($product) {
                $product->stock_quantity -= $production->quantity;
                $product->save();
            }
            return $this->respondError('Information was not saved.');
        }

    }

    public function loopThroughMaterialsUsed($materials_used, $total_cost,$business_id,$production,$force=true) {

        //Go through all materials used
        foreach($materials_used as $material_used) {


            //Get the details for the used material
            $material = Material::where('business_id',$business_id)->where('id',$material_used->material_id)->first();

            //Return if material is not found
            if(!$material) {
                continue;
                // return $this->respondError('Material not found');
            }

            //Check if material is already accounted for. If it has then skip
            if ($material_used->is_accounted && $force) {
                continue;
            }

            //Skip if material is not a component and is reusable after damaged
            if ($production->status == 'damaged' && $material->is_reusable_after_damaged) {
                continue;
            }

            //Calculate the cost of the materials for a unit production
            $total_cost += $material_used->cost;

            //Substract the (production quantity * material used quantity) from the current stock level.
            if($material->current_stock_level >= ($material_used->quantity * $production->quantity)) {
                $material->current_stock_level -= ($material_used->quantity * $production->quantity);
            }
            else{
                $material->current_stock_level = 0;
            }

            $material->save();

            //Record that this material has been accounted for to prevent it from being used again
            $material_used->is_accounted = true;
            $material_used->save();

        }

        return $total_cost;
    }

    public function updateProductionStatusOld(Request $request,$id) {

        $validator = Validator::make($request->all(), [
            'status' => ['bail','required','string','min:3',Rule::in(config('options.production_status'))],
            'description' => ['bail','nullable','string','min:5'],
        ],[
            'status.required' => 'Status is required',
            'status.min' => 'Invalid status selected',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $business_id = $request->user()->business_id;

        $production = Production::with(['materials'])->where('business_id',$business_id)->where('id',$id)->first();

        if ($request->status == $production->status) {
            return $this->respondError('Production is already in this status.');
        }

        $total_cost = 0;
        $product = null;
        $intermediate_good = null;

        if ($production->category == 'product') {

            if ($production->type == 'product') {

                //Get Product details
                $product = Product::where('business_id',$business_id)->where('id',$production->product_id)->first();

                //Check if product is available. Return eror if product is not available
                if(!$product) {
                    return $this->respondError('Product not found');
                }
                
            }
            else {

                //Get Product details
                $intermediate_good = IntermediateGood::where('business_id',$business_id)->where('id',$production->intermediate_good_id)->first();

                //Check if product is available. Return eror if product is not available
                if(!$intermediate_good) {
                    return $this->respondError('Intermediate Good not found');
                }

            }

        }

        $production->status = $request->status;

        //Check if product is completed or damaged
        if ($request->status == 'completed' || $request->status == 'damaged') {

            //Get all materials used for production
            $materials_used = $production->materials;

            //Check if materials are used
            if($materials_used->count() > 0) {

                //Loop through materials used and calculate total cost and
                //save production into materials if production type is "Component" and or marked as "New Material
                $total_cost = $this->loopThroughMaterialsUsed($materials_used, $total_cost,$business_id,$production);
            }

            if($request->status == 'completed') {

                //Perform operation when production status is completed and is a product
                if ($production->type == 'product') {
                    $product->stock_quantity += $production->quantity;
                    $product->production_cost = $total_cost + $production->labour_cost;
                    $product->save();
                }

                if($production->type == 'intermediate_good' && $production->is_material) {

                    $material = null;

                    // if material used is only 1 check if the material is also a component. if true update the material.
                    if($materials_used->count() == 1) {

                        //Get Material data
                        foreach($materials_used as $material_used) {
                            $material = Material::where('business_id',$business_id)->where('id',$material_used->material_id)->where('is_component',true)->first();
                        }
                    }

                    if(is_null($material)) {
                        $material = new Material();
                        $material->name = $production->title;
                        $material->code = 'PROD'.$production->id;
                        $material->type = 'in-house';
                        $material->material_category_id = 1;
                        $material->business_id = $business_id;
                        $material->unit_of_measurement = 'Pieces';
                        $material->production_id = $production->id;
                        $material->added_by = $request->user()->id;
                        $material->is_component = true;
                    }

                    $material->current_stock_level += $production->quantity;
                    $material->total_items += $production->quantity;
                    $material->cost_per_unit = $total_cost;
                    $material->total_cost += $total_cost*$production->quantity + $production->labour_cost;
                    $material->save();
                }

                $production->completed_at = date(now());
            }

        }

        if($production->save()){

            $this->logProductionHistory($production, $request->note);

            return $this->respondWithSuccess([
                'saved'     =>  true,
                'message'   =>  'Production status updated',
                'data'      =>  ProductionHistoryResource::collection(ProductionHistory::where('production_id',$production->id)->orderBy('created_at','desc')->get())
            ]);
        }
        else {
            if($product) {
                $product->stock_quantity -= $production->quantity;
                $product->save();
            }
            return $this->respondError('Information was not saved.');
        }

    }

    public function createProduction(Request $request) {
        $business_id = $request->user()->business_id;

        if(!$business_id) {
            return $this->respondError('Invalid request. Try again.');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['bail','required','string','min:2'],
            'category' => ['bail','required','string','min:3']
        ]);

        if ($validator->fails()) {
            $errorList = [];
            foreach($validator->errors()->messages() as $key=>$error) {
                $errorList = $error[0];
            }
            return $this->respondError($errorList);
        }

        $production = Production::create([
            'title' => $request->title,
            'category' => $request->category,
            'business_id' => $business_id,
            'user_id' => $request->user()->id,
            'assignee_id' => $request->user()->id,
            'priority' => 'normal',
            'status' => 'backlog',
        ]);

        if($production) {
            $this->logProductionHistory($production, "A new production with title ".$request->title." has been created. ");

            return $this->respondWithSuccess([
                'saved' =>true,
                'message' => 'New production created.',
                'data' => ProductionBasicResource::make($production)
            ]);
        }
        else {
            return $this->respondError('Information was not saved. Please try again');
        }

    }

    public function saveProduction(Request $request) {

        $business_id = $request->user()->business_id;

        if(!$business_id) {
            return $this->respondError('Invalid request. Try again.');
        }

        $validator = Validator::make($request->all(), [
            'title' => ['bail','required','string','min:2'],
            'priority' => ['bail','required','string','min:3'],
            'quantity' => ['bail','nullable','min:0','numeric'],
            'labour_cost' => ['bail','nullable','min:0','numeric'],
            'type' => ['bail','nullable','min:3','string'],
            'assignee' => ['bail','required','min:1','numeric'],
            'deadline_date' => ['bail','nullable','date'],
            'start_date' => ['bail','nullable','date'],
            'end_date' => ['bail','nullable','date'],
            'estimated_hours' => ['bail','nullable','numeric'],
            'description' => ['bail','nullable','string','min:5'],
        ],[
            'title.required' => 'Title is required',
            'title.min' => 'Title is too short',
            'priority.required' => 'Priority is required',
            'priority.min' => 'Invalid priority selected',
            // 'type.required' => 'Type is required',
            'type.min' => 'Invalid type selected',
            'assignee.required' => 'Assignee is required',
            'assignee.min' => 'Invalid assignee selected',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $previous_type = '';
        $changeMaterials = false;
        $previous_production_quantity = 0;


        //Check if production already exists if not create new one
        if($request->id){
            $production = Production::find($request->id);
            $previous_type = $production->type;
            $previous_production_quantity = $production->quantity;
        }
        else{
            $production = new Production();
            $production->status = 'backlog';
        }

        // Check if there is a change in the production type then set changeMaterial to true
        if($request->type != $previous_type) {
            $changeMaterials = true;
        }

        $labour = $request->labour_cost;

        // Get the product in production
        $product = null;
        if ($request->type == 'product') {

            $product = Product::with(['materials'])->where('business_id',$business_id)->where('id',$request->product)->first();

            if(!$product) {
                return $this->respondError('Product not found');
            }

            if($production->product_id != $request->product) {
                $changeMaterials = true;
            }
            $labour = $product->labour_cost;

        }

        // Get the intermediate good in production
        $intermediate_good = null;
        if ($request->type == 'intermediate_good') {

            $intermediate_good = IntermediateGood::with(['materialsUsed'])->where('business_id',$business_id)->where('id',$request->intermediate_good_id)->first();

            if(!$intermediate_good) {
                return $this->respondError('Intermediate Good not found '.$request->intermediate_good_id);
            }

            if($production->intermediate_good_id != $request->intermediate_good_id) {
                $changeMaterials = true;
            }
            $labour = $intermediate_good->labour_cost;

        }

        $production->business_id = $business_id;
        $production->title = $request->title;
        $production->priority = $request->priority;
        $production->quantity = $request->quantity;
        $production->labour_cost = $labour;
        $production->type = $request->type;
        $production->assignee_id = $request->assignee;

        if($production->category == 'product') {
            $production->product_id = ($request->type == 'product')?$request->product:null;
            $production->intermediate_good_id = ($request->type == 'intermediate_good')?$request->intermediate_good_id:null;
        }
        
        $production->deadline_date = ($request->deadline_date) ? date('Y-m-d',strtotime($request->deadline_date)):null;
        $production->start_date = ($request->start_date) ? date('Y-m-d',strtotime($request->start_date)):null;
        $production->end_date = ($request->end_date) ? date('Y-m-d',strtotime($request->end_date)):null;
        $production->estimated_hours = $request->estimated_hours;
        $production->description = $request->description;
        $production->user_id = $request->user()->id;

        if($production->save()) {

            $notice = $this->checkForMaterialsLevel($production);
            if(count($notice) > 0) {
                return response()->json([
                    'saved' => false,
                    'message' => $notice,
                ]);
            }

            if($changeMaterials) {

                ProductionMaterial::where('production_id',$production->id)->delete();

                if ($product) {
                    foreach($product->materials as $product_material) {
                        ProductionMaterial::create([
                            'production_id' => $production->id,
                            'material_id' => $product_material->material_id,
                            'quantity' => $product_material->quantity,
                            'cost' => $product_material->cost,
                        ]);
                    }
                }
            }

            //Check if the quantity of the production has been changed. If changed, update the materials inventory and cost accordingly.
            if($previous_production_quantity != $production->quantity && $request->id && $production->status != 'backlog') {
                
                foreach($production->materials as $material) {
                    // Get previous total quantity of this material used
                    $previous_direct_material_used = $material->quantity * $previous_production_quantity;
                    $new_direct_material_used = $material->quantity * $production->quantity;

                    if($material->material_id && $material->is_accounted) {
                        //Update the quantity of material in inventory
                        $getMaterial = Material::find($material->material_id);

                        if($getMaterial) {
                            $getMaterial->current_stock_level += $previous_direct_material_used - $new_direct_material_used;
                            $getMaterial->save();
                        }
                    }
                    elseif($material->intermediate_good_id && $material->is_accounted) {
                        //Update the quantity of intermediate good in inventory
                        $getMaterial = IntermediateGood::find($material->intermediate_good_id);
                        
                        if($getMaterial) {
                            $getMaterial->stock_quantity += $previous_direct_material_used - $new_direct_material_used;
                            $getMaterial->save();
                        }
                    }
                    
                }
                $note = "The production quantity was updated from ".easyCount($previous_production_quantity)." to ".easyCount($production->quantity);
                $this->logProductionHistory($production, $note);
            }

            if($request->id){

            }
            else {
                $note = "A new production with title ".$request->title." has been created. ";
                $this->logProductionHistory($production, $note);
            }

            return $this->respondWithSuccess(
                [
                    'saved' =>true,
                    'message' =>'Production has been saved.',
                    'data' => ProductionResource::make($production)
                ]
            );
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function duplicateProduction(Request $request) {
        $business_id = $request->user()->business_id;

        if(!$business_id) {
            return $this->respondError('Invalid request. Try again.');
        }

        $validator = Validator::make($request->all(), [
            'id' => ['bail','required','integer','min:1'],
        ],[
            'id.required' => 'Invalid production',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        $production = Production::find($request->id);
        if(!$production) return $this->respondError('Invalid production in use.');

        $title = $production->title;
        $existing_names = Production::where('title', 'like' ,"$title%")->where('business_id',$business_id)->count();
        if($existing_names > 0) {
            $title = $title.' - Copy ('.$existing_names.')';
        }

        $new_production = new Production();
        $new_production->business_id = $business_id;
        $new_production->title = $title;
        $new_production->priority = $production->priority;
        $new_production->category = $production->category;
        $new_production->status = 'backlog';
        $new_production->assignee_id = $production->assignee_id;
        $new_production->product_id = $production->product_id;
        $new_production->intermediate_good_id = $production->intermediate_good_id;
        $new_production->quantity = $production->quantity;
        $new_production->labour_cost = $production->labour_cost;
        $new_production->type = $production->type;
        $new_production->estimated_hours = $production->estimated_hours;
        $new_production->description = $production->description;
        $new_production->is_material = $production->is_material;
        $new_production->user_id = $production->user_id;

        if($new_production->save()) {

            if($production->materials) {
                foreach($production->materials as $material) {
                    ProductionMaterial::create([
                        'production_id' => $new_production->id,
                        'material_id' => $material->material_id,
                        'intermediate_good_id' => $material->intermediate_good_id,
                        'quantity' => $material->quantity,
                        'cost' => $material->cost,
                        'is_accounted' => false,
                    ]);
                }
            }

            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Production was successfully duplicated',
            'data'=>ProductionBasicResource::make($new_production)]);
        }
        else {
            return $this->respondError('Sorry! Please try again.');
        }
    }

    public function logProductionHistory(Production $production, $note) {
        ProductionHistory::create([
            'user_id' => request()->user()->id,
            'production_id' => $production->id,
            'note' => $note,
            'status' => $production->status,
        ]);
    }

    public function checkForMaterialsLevel(Production $production) {

        $notice = [];

        if ($production->type == 'product') {

            $product = Product::with(['materials','intermediateGoods'])->where('id',$production->product_id)->first();

            if(!$product) return $notice;

            foreach($product->materials as $used_material) {
                $material = Material::find($used_material->material_id);
                if($material->current_stock_level < $used_material->quantity * $production->quantity) {
                    $notice[] = 'Material: '.$material->name.' is low. Current level: '.$material->current_stock_level.', Required: '.$used_material->quantity * $production->quantity;
                }
            }

            foreach($product->intermediateGoods as $used_intermediate_good) {
                $intermediate_good = IntermediateGood::find($used_intermediate_good->intermediate_good_id);
                if($intermediate_good->stock_quantity < $used_intermediate_good->quantity * $production->quantity) {
                    $notice[] = 'Intermediate Good: '.$intermediate_good->name.' is low. Current level: '.$intermediate_good->stock_quantity.', Required: '.$used_intermediate_good->quantity * $production->quantity;
                }
            }
        }
        elseif ($production->type == 'intermediate_good') {

            $intermediate_good = IntermediateGood::with(['materialsUsed'])->where('id',$production->intermediate_good_id)->first();

            if(!$intermediate_good) return $notice;

            foreach($intermediate_good->materialsUsed as $used_material) {
                $material = Material::find($used_material->material_id);
                if($material->current_stock_level < $used_material->quantity * $production->quantity) {
                    $notice[] = 'Material: '.$material->name.' is low. Current level: '.$material->current_stock_level.', Required: '.$used_material->quantity * $production->quantity;
                }
            }
        }
        else {
            $used_materials = ProductionMaterial::where('production_id',$production->id)->get();
            foreach($used_materials as $used_material) {
                $material = Material::find($used_material->material_id);
                if($material->current_stock_level < $used_material->quantity * $production->quantity) {
                    $notice[] = 'Material: '.$material->name.' is low. Current level: '.$material->current_stock_level.', Required: '.$used_material->quantity * $production->quantity;
                }
            }
        }
        
        return $notice;
    }

    public function isMaterialsSufficientForProduction($production) {
        $materials = DB::table('production_materials')
            ->join('productions', 'productions.id','=', 'production_materials.production_id')
            ->join('materials', 'materials.id','=', 'production_materials.material_id')
            ->select('production_materials.id', 'materials.name', 'materials.current_stock_level',
            'materials.image','materials.unit_of_measurement','production_materials.quantity','production_materials.cost')
            ->where('production_materials.production_id',$production->id)
            ->get();

        $insufficient_materials = false;

        if ($production->type == 'product') {

            $product = Product::with(['materials','intermediateGoods'])->where('id',$production->product_id)->first();

            if($product) {
                foreach($product->materials as $used_material) {
                    $material = Material::find($used_material->material_id);
                    if($material->current_stock_level < $used_material->quantity * $production->quantity) {
                        $insufficient_materials = true; break;
                    }
                }

                foreach($product->intermediateGoods as $used_intermediate_good) {
                    $intermediate_good = IntermediateGood::find($used_intermediate_good->intermediate_good_id);
                    if($intermediate_good->stock_quantity < $used_intermediate_good->quantity * $production->quantity) {
                        $insufficient_materials = true; break;
                    }
                }
            }

            
        }
        elseif ($production->type == 'intermediate_good') {

            $intermediate_good = IntermediateGood::with(['materialsUsed'])->where('id',$production->intermediate_good_id)->first();

            if($intermediate_good) {

                foreach($intermediate_good->materialsUsed as $used_material) {
                    $material = Material::find($used_material->material_id);
                    if($material->current_stock_level < $used_material->quantity * $production->quantity) {
                        $insufficient_materials = true; break;
                    }
                }
            }

        }
        else {
            $used_materials = ProductionMaterial::where('production_id',$production->id)->get();
            foreach($used_materials as $used_material) {
                $material = Material::find($used_material->material_id);
                if($material->current_stock_level < $used_material->quantity * $production->quantity) {
                    $insufficient_materials = true; break;
                }
            }
        }

        return $insufficient_materials;
    }

    public function startProduction(Request $request) {

        if($this->transferProductOrIntermediateGoodsMaterials($request)) {
            // return $this->transferProductOrIntermediateGoodsMaterials($request);
            $production = Production::find($request->production_id);
            $production->status ='in_progress';
            $production->save();

            $note = "Production of ".$production->title." has been started. ".$request->note;
            $this->logProductionHistory($production, $note);
            return $this->respondWithSuccess([
                'saved'     =>  true,
                'message'   =>  'Production has been started',
                'data'      =>  ProductionHistoryResource::collection(ProductionHistory::where('production_id',$production->id)->orderBy('created_at','desc')->get())
            ]);
            // return $this->respondWithSuccess(['saved' =>true,'message' =>'Production has been started.']);
        }
        else {
            return $this->respondError('Failed to start production.');
        }
    }

    public function transferProductOrIntermediateGoodsMaterials(Request $request) {
        $validator = Validator::make($request->all(), [
            'production_id' => ['bail','required','integer','min:1'],
        ],[
            'production_id.required' => 'Invalid production',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];
                return $this->respondError($errorList);
            }
        }

        $transfered = false;

        $production = Production::find($request->production_id);
        if(!$production) return $this->respondError('Invalid production in use.');

        DB::beginTransaction();

        try {
            ProductionMaterial::where('production_id', $production->id)->update([
                'control' => false
            ]);

            if ($production->type == 'product') {

                $product = Product::with(['materials','intermediateGoods'])->where('id',$production->product_id)->first();

                if(!$product) return $this->respondError('Invalid product in use.');

                //Perform production insertion on product materials per unit item
                foreach($product->materials as $used_material) {

                    $material = Material::find($used_material->material_id);
                    $new_total_quantity = $used_material->quantity * $production->quantity;

                    if($material) {

                        $getProductionMaterial = ProductionMaterial::where('material_id',$material->id)
                        ->where('production_id','=',$production->id)->first();

                        if($getProductionMaterial) {
                            $previous_quantity = $getProductionMaterial->quantity * $production->quantity;

                            $getProductionMaterial->quantity = $used_material->quantity;
                            $getProductionMaterial->cost = $material->cost_per_unit * $used_material->quantity;
                            
                            
                            if($getProductionMaterial->is_accounted) {
                                $material->current_stock_level += $previous_quantity - $new_total_quantity;
                            }
                            else{
                                $material->current_stock_level -= $new_total_quantity;
                            }

                            $getProductionMaterial->is_accounted = true;
                            $getProductionMaterial->control = true;
                            $getProductionMaterial->save();
                            
                        }
                        else {
                            $getProductionMaterial = ProductionMaterial::create([
                                'production_id' => $request->production_id,
                                'material_id' => $material->id,
                                'quantity'  => $used_material->quantity,
                                'cost'  => $material->cost_per_unit * $used_material->quantity,
                                'is_accounted'  => true,
                                'control'  => true
                            ]);

                            $new_total_quantity = $getProductionMaterial->quantity * $production->quantity;
                            $material->current_stock_level -= $new_total_quantity;
                        }

                        if($production->status != 'backlog') {
                            $getProductionMaterial->is_accounted = true;
                        }
                        
                        $material->save();

                    }
                }

                //Perform production insertion on product intermediate goods per unit item
                foreach($product->intermediateGoods as $used_intermediate_good) {

                    $intermediate_good = IntermediateGood::find($used_intermediate_good->intermediate_good_id);

                    $new_total_intermediate_good_quantity = $used_intermediate_good->quantity * $production->quantity;

                    if($intermediate_good) {
                    
                        if($intermediate_good->stock_quantity < $new_total_intermediate_good_quantity) {
                            $notice[] = 'Intermediate Good: '.$intermediate_good->name.' is low. Current level: '.$intermediate_good->stock_quantity.', Required: '.$new_total_intermediate_good_quantity;
                        }

                        //Get all the materials and their unit cost.
                        $materials = DB::table('intermediate_goods_materials')
                        ->join('intermediate_goods', 'intermediate_goods.id','=', 'intermediate_goods_materials.intermediate_good_id')
                        ->join('materials', 'materials.id','=', 'intermediate_goods_materials.material_id')
                        ->select('materials.cost_per_unit','materials.id','intermediate_goods_materials.quantity')
                        ->where('intermediate_goods_materials.intermediate_good_id',$intermediate_good->id)
                        ->orderBy('materials.id')
                        ->get();

                        //Loop through to calculate the total cost per unit for each intermediate good
                        $total_materials_unit_cost = 0;
                        foreach($materials as $material) {
                            $total_materials_unit_cost += $material->quantity * $material->cost_per_unit;
                        }
                        
                        $getProductionMaterial = ProductionMaterial::where('intermediate_good_id',$intermediate_good->id)
                        ->where('production_id','=',$production->id)->first();

                        if($getProductionMaterial) {

                            $previous_total_quantity = $getProductionMaterial->quantity * $production->quantity;

                            $getProductionMaterial->quantity = $used_intermediate_good->quantity;
                            $getProductionMaterial->cost = $total_materials_unit_cost;

                            if($getProductionMaterial->is_accounted) {
                                $intermediate_good->stock_quantity += $previous_total_quantity - $new_total_intermediate_good_quantity;
                            }
                            else{
                                $intermediate_good->stock_quantity -= $new_total_intermediate_good_quantity;
                            }

                            $getProductionMaterial->is_accounted = true;
                            $getProductionMaterial->control = true;
                            $getProductionMaterial->save();

                        }
                        else {
                            $getProductionMaterial = ProductionMaterial::create([
                                'production_id' => $request->production_id,
                                'intermediate_good_id' => $intermediate_good->id,
                                'quantity'  => $used_intermediate_good->quantity,
                                'cost'  => $total_materials_unit_cost,
                                'is_accounted'  => true,
                                'control'  => true
                            ]);
                            $intermediate_good->stock_quantity -= $new_total_intermediate_good_quantity;
                        }

                        if($production->status != 'backlog') {
                            $getProductionMaterial->is_accounted = true;
                        }

                        $intermediate_good->save();

                    }
                }

                $transfered = true;
            }
            elseif ($production->type == 'intermediate_good') {

                $intermediate_good = IntermediateGood::with(['materialsUsed'])->where('id',$production->intermediate_good_id)->first();
                    
                if($intermediate_good) {

                    foreach($intermediate_good->materialsUsed as $used_material) {

                        $material = Material::find($used_material->material_id);
                        $new_total_material_quantity = $used_material->quantity * $production->quantity;
    
                        if($material) {
    
                            if ($material->current_stock_level < $new_total_material_quantity) {
                                return $this->respondError('Low stock level for '.$material->name.'. Quantity required is '.$new_total_material_quantity);
                            }
                        
                            $getProductionMaterial = ProductionMaterial::where('material_id',$material->id)
                            ->where('production_id','=',$production->id)->first();

                            if($getProductionMaterial) {
                                $previous_total_material_quantity = $getProductionMaterial->quantity * $production->quantity;

                                $getProductionMaterial->quantity = $used_material->quantity;
                                $getProductionMaterial->cost = $material->cost_per_unit * $used_material->quantity;
                                
                                
                                if($getProductionMaterial->is_accounted) {
                                    $material->current_stock_level += $previous_total_material_quantity - $new_total_material_quantity;
                                }
                                else{
                                    $material->current_stock_level -= $new_total_material_quantity;
                                }

                                $getProductionMaterial->is_accounted = true;
                                $getProductionMaterial->control = true;
                                $getProductionMaterial->save();
                                
                            }
                            else {
                                $getProductionMaterial = ProductionMaterial::create([
                                    'production_id' => $request->production_id,
                                    'material_id' => $material->id,
                                    'quantity'  => $used_material->quantity,
                                    'cost'  => $material->cost_per_unit * $used_material->quantity,
                                    'is_accounted'  => true,
                                    'control'  => true
                                ]);
                                $material->current_stock_level -= $new_total_material_quantity;
                            }

                            if($production->status != 'backlog') {
                                $getProductionMaterial->is_accounted = true;
                            }
    
                            $material->save();
    
                        }
                    }

                }
                
                $transfered = true;
            }
            else {

                $materialsUsed = ProductionMaterial::where('production_id','=',$production->id)->get();

                foreach($materialsUsed as $used_material) {

                    $material = Material::find($used_material->material_id);
                    $new_total_material_used = $used_material->quantity * $production->quantity;

                    if($material) {

                        if ($material->current_stock_level < $new_total_material_used) {
                            return $this->respondError('Low stock level for '.$material->name.'. Quantity required is '.$new_total_material_used);
                        }
                        
                        if($used_material->is_accounted) {
                            $material->current_stock_level += $used_material->quantity * $production->quantity - $new_total_material_used;
                        }
                        else {
                            $material->current_stock_level -= $new_total_material_used;
                        }
                        $used_material->is_accounted = true;
                        $used_material->control = true;
                        $used_material->save();

                        $material->save();

                    }
                }

                $transfered = true;
            }

            ProductionMaterial::where('production_id', $production->id)->where('control',false)->delete();

            DB::commit();
            return $transfered;
        }
        catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function restoreProductionMaterials(Request $request) {
        if($this->transferProductOrIntermediateGoodsMaterials($request)) {
            $production = Production::find($request->production_id);
            $note = "All default materials were restored.";
            $this->logProductionHistory($production, $note);
            return $this->respondWithSuccess([
                'saved'     =>  true,
                'message'   =>  'Materials were restored successfully',
                'data'      =>  ProductionHistoryResource::collection(ProductionHistory::where('production_id',$production->id)->orderBy('created_at','desc')->get())
            ]);
        }
        else {
            return $this->respondError('Failed to start production.');
        }
    }

    public function saveProductionMaterial(Request $request) {

        $validator = Validator::make($request->all(), [
            'production_id' => ['bail','required','integer','min:1'],
            'material_id' => ['bail','required','integer','min:1'],
            'quantity' => ['bail','nullable','min:0'],
            'cost' => ['bail','nullable','min:0'],
        ],[
            'production_id.required' => 'Invalid production',
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

        $production = Production::find($request->production_id);
        if(!$production) return $this->respondError('Invalid production in use.');

        $material_saved = ProductionMaterial::where('production_id',$request->production_id)
        ->where('material_id',$request->material_id)
        ->first();

        if(!$material_saved) {
            $material_saved = new ProductionMaterial();
            $material_saved->production_id = $request->production_id;
            $material_saved->material_id = $request->material_id;
        }

        $previous_quantity = $material_saved->quantity;

        $material_saved->quantity = $request->quantity;
        $material_saved->cost = $request->cost;

        if($material_saved->save()) {

            //Perform a direct reduction update on stock level
            if($production->status != 'backlog') {
            // if(in_array($production->status,['completed','damaged'])) {

            if($production->material_id) {
                
                $material = Material::where('id',$request->material_id)->first();

                if ($material->is_reusable_after_damaged && $production->status == 'damaged'){

                }
                else {
                    $value = $material->current_stock_level - ($request->quantity * $production->quantity) + ($previous_quantity * $production->quantity);
                    $material->current_stock_level = ($value > 0) ? $value : 0;
                    $material->save();
                }
            }
            else if ($production->intermediate_good_id) {

                $intermediate_good = IntermediateGood::where('id',$request->intermediate_good_id)->first();
                $value = $intermediate_good->stock_quantity - ($request->quantity * $production->quantity) + ($previous_quantity * $production->quantity);
                $intermediate_good->stock_quantity = ($value > 0) ? $value : 0;
                $intermediate_good->save();
            }
            }

            $materials = DB::table('production_materials')
            ->join('productions', 'productions.id','=', 'production_materials.production_id')
            ->join('materials', 'materials.id','=', 'production_materials.material_id')
            ->select('production_materials.id', 'materials.name', 'materials.current_stock_level',
            'materials.image','materials.unit_of_measurement','production_materials.quantity','production_materials.cost')
            ->where('production_materials.production_id',$request->production_id)
            ->get();

            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Production material has been saved.',
            'data' => $materials
            ]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }

    }

    public function deleteProduction(Request $request) {
        $business_id = $request->user()->business_id;

        $production = Production::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$production) {
            return $this->respondError('Production not found');
        }

        if($production->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Production has been deleted.']);
        }
        else {
            return $this->respondError('Production was not deleted.');
        }

    }

    public function clearProductionMaterials(Request $request, Production $production) {

        //Get all materials from the production
        $usedMaterials = ProductionMaterial::where('production_id', $production->id)->get();

        DB::beginTransaction();
        try {
            foreach($usedMaterials as $usedMaterial) {
                //Check if it is a raw material
                if($usedMaterial->material_id) {

                    $should_delete = true;

                    //Check if the quantity of the material has been deducted from inventory
                    if($usedMaterial->is_accounted == true) {

                        // Get the actual raw material
                        $material = Material::where('id',$usedMaterial->material_id)->first();
                        $is_reusable = $material->is_reusable_after_damaged; //Is the material reusable after damage?

                        //Perform actions if materials exists
                        if($material) {
                            if($production->status == 'damaged' && !$is_reusable) {
                                $should_delete = false;
                            }
                            else{
                                $material->current_stock_level += $usedMaterial->quantity * $production->quantity;
                                $material->save();
                            }
                            
                        }
                    }

                    if($should_delete) {
                        $usedMaterial->delete();
                    }
                }
                elseif($usedMaterial->intermediate_good_id) {

                    $should_delete = true;

                    //Check if the quantity of the material has been deducted from inventory
                    if($usedMaterial->is_accounted == true) {

                        // Get the intermediate good 
                        $intermediate_good = IntermediateGood::where('id',$usedMaterial->intermediate_good_id)->first();
                        $is_reusable = $intermediate_good->is_reusable_after_damaged; //Is the intermediate good reusable after damage?

                        //Perform actions if intermediate good 
                        if($intermediate_good) {
                            if($production->status == 'damaged' && !$is_reusable) {
                                $should_delete = false;
                            }
                            else{
                                $intermediate_good->stock_quantity += $usedMaterial->quantity * $production->quantity;
                                $intermediate_good->save();
                            }
                            
                        }
                    }

                    if($should_delete) {
                        $usedMaterial->delete();
                    }
                }
            }

            DB::commit();
            return true;
        }
        catch(\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function deleteProductionMaterial(Request $request) {
        $business_id = $request->user()->business_id;

        $productionMaterial = ProductionMaterial::where('production_id', $request->production_id)->where('id',$request->id)->first();
        $production = Production::where('id', $request->production_id)->first();

        if(!$productionMaterial) {
            return $this->respondError('Material not found');
        }

        if(!$production) {
            return $this->respondError('Production not found');
        }

        if($productionMaterial->delete()){

            //Perform a direct reduction update on stock level
            if(in_array($production->status,['completed','damaged'])) {
                $material = Material::where('id',$productionMaterial->material_id)->first();

                if ((!$material->is_reusable_after_damaged && $production->status == 'damaged') || $production->status == 'completed') {

                    $value = $material->current_stock_level + $productionMaterial->quantity * $production->quantity;
                    $material->current_stock_level = ($value > 0) ? $value : 0;
                    $material->save();

                    //Get total quantity cost
                    $cost = $productionMaterial->cost;

                    if($production->type == 'component' && $production->is_material) {
                        $parent_material = Material::where('production_id',$production->id)->first();
                        if($parent_material) {
                            $parent_material->cost_per_unit -= $cost;
                            $parent_material->total_cost -= $cost;
                            $parent_material->save();
                        }
                    }

                    if(in_array($production->type,['product'])) {
                        $product = Product::where('id',$production->product_id)->first();
                        $product->production_cost -= $cost;
                        $product->save();
                    }
                }
            }

            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Material has been deleted.']);
        }
        else {
            return $this->respondError('Material was not deleted.');
        }

    }
}
