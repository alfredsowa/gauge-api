<?php

namespace App\Http\Resources\Product;

use App\Http\Controllers\Api\ProductsController;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductBasicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $getCost = new ProductsController();
        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'supplier_id' => $this->supplier_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'user_id' => $this->user_id,
            'description' => $this->description,
            'price' => $this->price,
            'wholesale_price' => $this->wholesale_price,
            'wholesale_markup' => $this->wholesale_markup,
            'retail_markup' => $this->retail_markup,
            'use_manual_pricing' => $this->use_manual_pricing,
            'labour_cost' => $this->labour_cost,
            'production_cost' => $this->production_cost,
            'discount_price' => $this->discount_price,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_quantity' => $this->min_stock_quantity,
            'product_category_id' => $this->product_category_id,
            'materials_used' => $this->materials->count()+$this->intermediateGoods->count(),
            'sku' => $this->sku,
            'product_costs' => $getCost->getProductMaterialCostArray($request,$this->id),
            'barcode' => $this->barcode,
            'image' => $this->image,
            'is_produced' => $this->is_produced,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'updated_at' => $this->updated_at,
        ];
    }
}
