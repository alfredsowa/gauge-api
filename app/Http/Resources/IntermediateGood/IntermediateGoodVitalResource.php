<?php

namespace App\Http\Resources\IntermediateGood;

use App\Models\IntermediateGoodsMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IntermediateGoodVitalResource extends JsonResource
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
            'slug' => $this->slug,
            'labour_cost' => $this->labour_cost,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_quantity' => $this->min_stock_quantity,
            'used_materials' => IntermediateGoodMaterialsResource::collection(IntermediateGoodsMaterial::where('intermediate_good_id',$this->id)->get()),
            'image' => $this->image,
            'status' => $this->status,
            'is_reusable_after_damaged' => $this->is_reusable_after_damaged,
        ];
    }
}
