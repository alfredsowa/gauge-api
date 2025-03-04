<?php

namespace App\Http\Resources\Product;

use App\Http\Resources\IntermediateGood\IntermediateGoodMaterialsResource;
use App\Http\Resources\Material\MaterialsVitalResource;
use App\Models\IntermediateGoodsMaterial;
use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProductIntermediateGoodVitalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $intermediate_goods = DB::table('products_intermediate_goods')
            ->join('products', 'products.id','=', 'products_intermediate_goods.product_id')
            ->join('intermediate_goods', 'intermediate_goods.id','=', 'products_intermediate_goods.intermediate_good_id')
            ->select('products_intermediate_goods.id', 'intermediate_goods.status','intermediate_goods.name','intermediate_goods.slug', 
            'intermediate_goods.stock_quantity','intermediate_goods.labour_cost','intermediate_goods.min_stock_quantity',
            'intermediate_goods.image','intermediate_goods.unit_of_measurement','products_intermediate_goods.quantity')
            ->where('products_intermediate_goods.product_id',$this->product_id)
            ->where('products_intermediate_goods.intermediate_good_id',$this->intermediate_good_id)
            ->first();

        // $materials = DB::

        if($intermediate_goods === null) {
            return [];
        }

        return [
            'id' => $this->id,
            'name' => $intermediate_goods->name,
            'slug' => $intermediate_goods->slug,
            'labour_cost' => $intermediate_goods->labour_cost,
            'unit_of_measurement' => $intermediate_goods->unit_of_measurement,
            'quantity' => $intermediate_goods->quantity,
            'stock_quantity' => $intermediate_goods->stock_quantity,
            'min_stock_quantity' => $intermediate_goods->min_stock_quantity,
            // 'used_materials' => MaterialsVitalResource::collection(Material::whereIn('id',$this->intermediateGoods->pluck('intermediate_good_id')->toArray())),
            'used_materials' => IntermediateGoodMaterialsResource::collection(IntermediateGoodsMaterial::where('intermediate_good_id',$this->intermediate_good_id)->get()),
            'image' => $intermediate_goods->image,
            'image' => $intermediate_goods->image,
            'status' => $intermediate_goods->status,
        ];
    }
}
