<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Purchase\PurchaseBasicResource;
use App\Http\Resources\Purchase\PurchaseResource;
use App\Http\Resources\Supplier\SupplierFormResource;
use App\Http\Resources\Supplier\SupplierFullResource;
use App\Http\Resources\Supplier\SupplierResource;
use App\Models\Material;
use App\Models\Purchase;
use App\Models\Supplier;
use F9Web\ApiResponseHelpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PurchasesController extends Controller
{
    use ApiResponseHelpers;

    public function getPurchases(Request $request) {
        $business_id = $request->user()->business_id;

        $getPurchases = PurchaseResource::collection(Purchase::with([
            'supplier','addedBy','material'
            ])->where('business_id', $business_id)->orderBy('purchase_date','desc')->get());
        
        return $this->respondWithSuccess(['data' => $getPurchases]);
    }

    public function getPurchasesBasic(Request $request) {
        $business_id = $request->user()->business_id;

        $getPurchases = PurchaseBasicResource::collection(Purchase::where('business_id', $business_id)->orderBy('purchase_date','desc')->get());
        
        return $this->respondWithSuccess(['data' => $getPurchases]);
    }

    public function getPurchaseBasic(Request $request,$id) {
        $business_id = $request->user()->business_id;

        $getPurchase = PurchaseBasicResource::make(Purchase::where('id', $id)
        ->where('status', 'Draft')
        ->where('business_id', $business_id)
        ->first());
        
        return $this->respondWithSuccess($getPurchase);
    }

    public function getSuppliers(Request $request) {
        $business_id = $request->user()->business_id;

        $getSuppliers = SupplierFormResource::collection(Supplier::where('business_id', $business_id)->orderBy('contact_person')->get());
        
        return $this->respondWithSuccess(['data' => $getSuppliers]);
    }

    public function getSupplierFull(Request $request,$id) {
        $business_id = $request->user()->business_id;

        $getSupplier = SupplierFullResource::make(Supplier::with(['purchases'])->where('id', $id)->where('business_id', $business_id)->first());
        
        return $this->respondWithSuccess($getSupplier);
    }

    public function savePurchase(Request $request) {

        $business_id = $request->user()->business_id;
        // return $this->respondWithSuccess(['data' => $business_id]);

        $material = Material::find($request->material_id);

        if(!$business_id) {
            return $this->respondError('Invalid request. Try again.');
        }

        if(!$material) {
            return $this->respondError('Invalid material selected');
        }

        $validator = Validator::make($request->all(), [
            'material_id' => [
                'bail','required','numeric'
            ],
            'purchase_date' => [
                'bail','required','date'
            ],
            'quantity' => [
                'bail','required','min:0'
            ]
        ],[
            'material_id.required' => 'Material is required',
            'material_id.numeric' => 'Invalid material selected',
            'purchase_date.required' => 'Purchase date is required',
            'purchase_date.date' => 'Invalid purchase date',
            'quantity.required' => 'Quantity per tracking unit is required',
            'actual_quantity.required' => 'Quantity purchased is required',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        if($request->id){
            $purchase = Purchase::find($request->id);
        }
        else{
            $purchase = new Purchase();
        }

        $purchase->business_id = $business_id;
        $purchase->supplier_id = $request->supplier_id;
        $purchase->material_id = $request->material_id;
        $purchase->added_by = $request->user()->id;
        $purchase->purchase_date = date('Y-m-d',strtotime($request->purchase_date));
        $purchase->quantity = $request->quantity;
        $purchase->actual_quantity = $request->actual_quantity;
        $purchase->purchase_details = $request->purchase_details;
        $purchase->unit_price = $request->unit_price;
        $purchase->amount_paid = $request->amount_paid;
        $purchase->discounts = $request->discounts;
        $purchase->shipping = $request->shipping;
        $purchase->invoice_number = $request->invoice_number;
        $purchase->notes = $request->notes;
        $purchase->status = $request->status;

        if($purchase->save()) {

            if($purchase->status == 'Supplied') {
                $material->current_stock_level += $purchase->quantity;
                $material->cost_per_unit = $purchase->unit_price;
                $material->total_items += $purchase->quantity;
                $material->save();
            }
            
            return $this->respondWithSuccess(['saved' =>true,'message' =>'Purchase has been recorded.']);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function deletePurchase(Request $request) {
        $business_id = $request->user()->business_id;

        $purchase = Purchase::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$purchase) {
            return $this->respondError('Purchase not found');
        }

        $quantity = $purchase->quantity;
        $total_items = $purchase->total_items;
        
        $material = Material::find($purchase->material_id);

        if($purchase->status == 'Supplied') {
            $material->current_stock_level -= $quantity;
            $material->total_items -= $total_items;
        }

        if($purchase->delete()){
            $material->save();
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Purchase has been deleted.']);
        }
        else {
            return $this->respondError('Purchase was not deleted.');
        }
        
    }

    public function saveSupplier(Request $request) {

        $business_id = $request->user()->business_id;

        if(!$business_id) {
            return $this->respondError('Invalid request. Try again.');
        }

        $validator = Validator::make($request->all(), [
            'contact_person' => [
                'bail','required','string','min:2'
            ],
            'company_name' => [
                'bail','nullable','string','min:2'
            ],
            'contact_detail' => [
                'bail','required','string','min:7'
            ],
            'location' => [
                'bail','nullable','string','min:2'
            ],
            'note' => [
                'bail','nullable','string','min:5'
            ]
        ],[
            'contact_person.required' => 'Contact person name is required',
            'contact_person.min' => 'Contact person name must be more than 1 character',
            // 'company_name.required' => 'Company name is required',
            'company_name.min' => 'Company name must be more than 1 character',
            'contact_detail.required' => 'Contact information is required',
            'contact_detail.min' => 'Contact information must be more than 6 characters',
            'location.min' => 'Location must be more than 1 character',
            'note.min' => 'Your note must be more than 4 characters',
        ]);

        if ($validator->fails()) {

            $errorList = [];

            foreach($validator->errors()->messages() as $key=>$error) {

                $errorList = $error[0];

            }

            return $this->respondError($errorList);
        }

        if($request->id){
            $supplier = Supplier::find($request->id);
        }
        else{
            $supplier = new Supplier();
        }

        $supplier->business_id = $business_id;
        $supplier->contact_person = $request->contact_person;
        $supplier->company_name = $request->company_name;
        $supplier->contact_detail = $request->contact_detail;
        $supplier->location = $request->location;
        $supplier->note = $request->note;

        if($supplier->save()) {
            
            return $this->respondWithSuccess(['saved' =>true,
            'message' =>'Supplier has been saved.',
            'suppliers' => SupplierFormResource::collection(Supplier::where('business_id', $request->user()->business_id)->get())
        ]);
        }
        else {
            return $this->respondError('Information was not saved.');
        }
    }

    public function deleteSupplier(Request $request) {
        $business_id = $request->user()->business_id;

        $supplier = Supplier::where('business_id', $business_id)->where('id',$request->id)->first();

        if(!$supplier) {
            return $this->respondError('Supplier not found');
        }

        if($supplier->delete()){
            return $this->respondWithSuccess(['deleted' =>true,'message' =>'Supplier has been deleted.']);
        }
        else {
            return $this->respondError('Supplier was not deleted.');
        }
        
    }
}
