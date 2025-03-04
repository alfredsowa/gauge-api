<?php

namespace App\Http\Resources\Business;

use App\Http\Resources\Material\MaterialCategoryResource;
use App\Http\Resources\Product\ProductCategoryResource;
use App\Http\Resources\Product\ProductTypeResource;
use App\Http\Resources\Supplier\SupplierResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
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
            'name' => $this->name,
            'industry' => $this->industry,
            'business_type' => $this->business_type,
            'business_size' => $this->business_size,
            'contact' => $this->contact,
            'website' => $this->website,
            'email' => $this->email,
            'city' => $this->city,
            'tax_identification_number' => $this->tax_identification_number,
            'logo' => $this->logo,
            'country' => $this->country,
            'currency' => $this->currency,
            'currency_symbol' => $this->currency_symbol,
            'language' => $this->language,
            'timezone' => $this->timezone,
            'address' => $this->address,
            'average_goods_monthly' => $this->average_goods_monthly,
            'setup' => $this->setup,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'suppliers' => SupplierResource::collection($this->suppliers),
            'product_categories' => ProductCategoryResource::collection($this->product_categories),
            'product_types' => ProductTypeResource::collection($this->product_types),
            'material_categories' => MaterialCategoryResource::collection($this->material_categories),
        ];
    }
}
