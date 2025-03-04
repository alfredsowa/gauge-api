<?php

namespace App\Http\Resources\Material;

use App\Http\Resources\Supplier\SupplierResource;
use App\Models\ProductionMaterial;
use App\Models\ProductsMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialsListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $count_productions = ProductionMaterial::where('material_id',$this->id)->count();
        $count_products = ProductsMaterial::where('material_id',$this->id)->count();

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'code'=>$this->code,
            'type'=>$this->type,
            'image'=>$this->image,
            'material_category_id'=>$this->material_category_id,
            'cost_per_unit'=>$this->cost_per_unit,
            'total_cost'=>$this->total_cost,
            'current_stock_level'=>$this->current_stock_level,
            'minimum_stock_level'=>$this->minimum_stock_level,
            'unit_of_measurement'=>$this->unit_of_measurement,
            'status'=>$this->status,
            'is_component'=>$this->is_component,
            'deletable'=> !($count_productions > 0 || $count_products > 0),
            'production_id'=>$this->production_id,
            'is_reusable_after_damaged'=>$this->is_reusable_after_damaged,
            'updated_at'=>$this->updated_at,
            'category'=>MaterialCategoryResource::make($this->category),
        ];
    }
}
