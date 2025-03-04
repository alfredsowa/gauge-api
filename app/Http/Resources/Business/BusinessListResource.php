<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessListResource extends JsonResource
{

    public $preserveKeys = true;
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'industry' => $this->industry,
            'business_type' => $this->business_type,
            'business_size' => $this->business_size,
            'contact' => $this->contact,
            'website' => $this->website,
            'city' => $this->city,
            'logo' => $this->logo,
            'country' => $this->country,
            'average_goods_monthly' => $this->average_goods_monthly,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'total_suppliers' => $this->whenCounted('suppliers'),
        ];
    }
}
