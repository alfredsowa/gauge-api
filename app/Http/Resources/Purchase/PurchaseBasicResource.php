<?php

namespace App\Http\Resources\Purchase;

use App\Models\Material;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseBasicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $supplier = Supplier::select('company_name','contact_detail')->where('id',$this->supplier_id)->first();
        $material = Material::select('name','image','unit_of_measurement')->where('id',$this->material_id)->first();
        return [
            'id'=>$this->id,
            'business_id'=>$this->business_id,
            'supplier_id'=>$this->supplier_id,
            'material_id'=>$this->material_id,
            'added_by'=>$this->added_by,
            'status'=>$this->status,
            'purchase_date'=>$this->purchase_date,
            'purchase_details'=>$this->purchase_details,
            'quantity'=>$this->quantity,
            'actual_quantity'=>$this->actual_quantity,
            'unit_price'=>$this->unit_price,
            'amount_paid'=>$this->amount_paid,
            'tax'=>$this->tax,
            'discounts'=>$this->discounts,
            'shipping'=>$this->shipping,
            'invoice_number'=>$this->invoice_number,
            'invoice_upload'=>$this->invoice_upload,
            'notes'=>$this->notes,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'addedBy'=>User::select('name','firstname')->where('id',$this->added_by)->first(),
            'material_name'=>$material['name'],
            'material_image'=>$material['image'],
            'tracking_unit'=>$material['unit_of_measurement'],
            'supplier_name'=>$supplier['company_name'],
            'supplier_contact'=>$supplier['contact_detail'],
        ];
    }
}
