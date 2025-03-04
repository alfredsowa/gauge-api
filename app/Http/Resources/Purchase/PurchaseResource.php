<?php

namespace App\Http\Resources\Purchase;

use App\Http\Resources\Business\BusinessResource;
use App\Http\Resources\Material\MaterialResource;
use App\Http\Resources\Supplier\SupplierResource;
use App\Http\Resources\User\UserResource;
use App\Models\Material;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
            // 'business'=>BusinessResource::make($this->business),
            'supplier'=>SupplierResource::make($this->supplier),
            'material'=>MaterialResource::make(Material::withTrashed()->where('id', '=', $this->material)->first()),
            'addedBy'=>User::withTrashed()->select('name','firstname')->where('id',$this->added_by)->first(),
        ];
    }
}
