<?php

namespace App\Http\Resources\Material;

use App\Http\Resources\Supplier\SupplierResource;
use App\Models\ProductionMaterial;
use App\Models\ProductsMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialsVitalResource extends JsonResource
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
            'name'=>$this->name,
            'image'=>$this->image,
            'cost_per_unit'=>$this->cost_per_unit,
            'current_stock_level'=>$this->current_stock_level,
            'minimum_stock_level'=>$this->minimum_stock_level,
            'unit_of_measurement'=>$this->unit_of_measurement,
            'status'=>$this->status,
        ];
    }
}
