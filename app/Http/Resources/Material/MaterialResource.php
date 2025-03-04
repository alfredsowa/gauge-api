<?php

namespace App\Http\Resources\Material;

use App\Models\ProductionMaterial;
use App\Models\ProductsMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Supplier\SupplierResource;

class MaterialResource extends JsonResource
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
            'description'=>$this->description,
            'image'=>$this->image,
            'material_category_id'=>$this->material_category_id,
            'added_by'=>$this->added_by,
            'cost_per_unit'=>$this->cost_per_unit,
            'total_cost'=>$this->total_cost,
            'current_stock_level'=>$this->current_stock_level,
            'minimum_stock_level'=>$this->minimum_stock_level,
            'unit_of_measurement'=>$this->unit_of_measurement,
            'storage_location'=>$this->storage_location,
            'deletable'=> !($count_productions > 0 || $count_products > 0),
            'status'=>$this->status,
            'is_component'=>$this->is_component,
            'production_id'=>$this->production_id,
            'is_reusable_after_damaged'=>$this->is_reusable_after_damaged,
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'category'=>MaterialCategoryResource::make($this->category),
        ];
    }
}
