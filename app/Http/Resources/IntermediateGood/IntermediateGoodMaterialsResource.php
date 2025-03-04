<?php

namespace App\Http\Resources\IntermediateGood;

use App\Http\Resources\Material\MaterialsVitalResource;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IntermediateGoodMaterialsResource extends JsonResource
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
            'intermediate_good_id' => $this->intermediate_good_id,
            'quantity' => $this->quantity,
            'material' => MaterialsVitalResource::make(Material::find($this->material_id)),
        ];
    }
}
