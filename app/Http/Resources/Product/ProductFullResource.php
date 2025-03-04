<?php

namespace App\Http\Resources\Product;

use App\Http\Controllers\Api\ProductsController;
use App\Http\Resources\Supplier\SupplierFormResource;
use App\Models\IntermediateGood;
use App\Models\ProductsIntermediateGood;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProductFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $materials = DB::table('products_materials')
            ->join('products', 'products.id','=', 'products_materials.product_id')
            ->join('materials', 'materials.id','=', 'products_materials.material_id')
            ->select('products_materials.id', 'materials.name', 'materials.current_stock_level','materials.cost_per_unit',
            'materials.image','materials.unit_of_measurement','products_materials.quantity','products_materials.cost')
            ->where('products_materials.product_id',$this->id)
            ->get();

        $intermediate_goods = DB::table('products_intermediate_goods')
            ->join('products', 'products.id','=', 'products_intermediate_goods.product_id')
            ->join('intermediate_goods', 'intermediate_goods.id','=', 'products_intermediate_goods.intermediate_good_id')
            ->select('products_intermediate_goods.id', 'intermediate_goods.name', 'intermediate_goods.stock_quantity','intermediate_goods.labour_cost',
            'intermediate_goods.image','intermediate_goods.unit_of_measurement','products_intermediate_goods.quantity')
            ->where('products_intermediate_goods.product_id',$this->id)
            ->get();

        $production_history = DB::table('productions')
            ->join('employees', 'employees.id','=', 'productions.assignee_id')
            ->select('productions.id as production_id', 'productions.title', 'productions.status','productions.quantity as production_quantity',
            'productions.start_date','productions.completed_at','employees.first_name','employees.last_name')
            ->where('productions.product_id',$this->id)
            ->get();

            $getCost = new ProductsController();

        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'supplier' => SupplierFormResource::make(Supplier::find($this->supplier_id)),
            'user_id' => $this->user_id,
            'added_by' => User::withTrashed()->select(['name','firstname'])->where('id',$this->user_id)->first(),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'wholesale_price' => $this->wholesale_price,
            'wholesale_markup' => $this->wholesale_markup,
            'retail_markup' => $this->retail_markup,
            'use_manual_pricing' => $this->use_manual_pricing,
            'labour_cost' => $this->labour_cost,
            'production_cost' => $this->production_cost,
            'discount_price' => $this->discount_price,
            'supplier_id' => $this->supplier_id,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_quantity' => $this->min_stock_quantity,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'image' => $this->image,
            'is_produced' => $this->is_produced,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'attributes' => $this->attributes,
            'materials' => $materials,
            'product_costs' => $getCost->getProductMaterialCostArray($request,$this->id),
            'intermediate_goods' => $intermediate_goods,
            'productions' => $production_history,
            'used_intermediate_goods' => ProductIntermediateGoodVitalResource::collection($this->intermediateGoods),
            // 'used_intermediate_goods' => ProductIntermediateGoodVitalResource::collection(IntermediateGood::whereIn('id',$this->intermediateGoods->pluck('intermediate_good_id')->toArray())->get()),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
