<?php

namespace App\Http\Resources\Supplier;

use App\Http\Resources\Purchase\PurchaseBasicResource;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contact_person' => $this->contact_person,
            'company_name' => $this->company_name,
            'contact_detail' => $this->contact_detail,
            'location' => $this->location,
            'total_spending' => $this->total_spending,
            'total_orders' => $this->total_orders,
            'last_order' => $this->last_order,
            'note' => $this->note,
            'purchases' => PurchaseBasicResource::collection(Purchase::where('supplier_id', $this->id)->where('business_id', $this->business_id)->get()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
