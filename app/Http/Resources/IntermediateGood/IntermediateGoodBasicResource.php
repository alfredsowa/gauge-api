<?php

namespace App\Http\Resources\IntermediateGood;

use App\Models\IntermediateGoodsMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IntermediateGoodBasicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $materials = DB::table('intermediate_goods_materials')
            ->join('intermediate_goods', 'intermediate_goods.id','=', 'intermediate_goods_materials.intermediate_good_id')
            ->join('materials', 'materials.id','=', 'intermediate_goods_materials.material_id')
            ->select('intermediate_goods_materials.id', 'materials.name', 'materials.current_stock_level','materials.cost_per_unit',
            'materials.image','materials.unit_of_measurement','intermediate_goods_materials.quantity','intermediate_goods_materials.cost')
            ->where('intermediate_goods_materials.intermediate_good_id',$this->id)
            ->get();

        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'material_cost' => 0,
            'labour_cost' => $this->labour_cost,
            'stock_quantity' => $this->stock_quantity,
            'is_reusable_after_damaged' => $this->is_reusable_after_damaged,
            'min_stock_quantity' => $this->min_stock_quantity,
            'intermediate_goods_category_id' => $this->intermediate_goods_category_id,
            'materials_used' => $this->materialsUsed->count(),
            'materials' => $materials,
            'used_materials' => IntermediateGoodMaterialsResource::collection(IntermediateGoodsMaterial::where('intermediate_good_id',$this->id)->get()),
            'image' => $this->image,
            'status' => $this->status,
            'updated_at' => $this->updated_at,
        ];
    }
}
