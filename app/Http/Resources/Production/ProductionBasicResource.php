<?php

namespace App\Http\Resources\Production;

use App\Http\Resources\Employee\EmployeeBasicResource;
use App\Models\Employee;
use App\Models\IntermediateGood;
use App\Models\Material;
use App\Models\Product;
use App\Models\ProductionMaterial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ProductionBasicResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $assignee = Employee::withTrashed()->select('id', 'first_name', 'last_name','image','title')
                    ->where('id',$this->assignee_id)
                    ->where('business_id',$this->business_id)
                    ->first();
                    
        $product = Product::withTrashed()->select('id', 'name', 'stock_quantity','image')
                    ->where('id',$this->product_id)
                    ->where('business_id',$this->business_id)
                    ->first();
                    
        $intermediate_good = IntermediateGood::withTrashed()->select('id', 'name', 'stock_quantity','image')
                    ->where('id',$this->intermediate_good_id)
                    ->where('business_id',$this->business_id)
                    ->first();
                    
        $materials = DB::table('production_materials')
            ->join('productions', 'productions.id','=', 'production_materials.production_id')
            ->join('materials', 'materials.id','=', 'production_materials.material_id')
            ->select('production_materials.id', 'materials.name', 'materials.current_stock_level',
            'materials.image','materials.unit_of_measurement','production_materials.quantity','production_materials.cost')
            ->where('production_materials.production_id',$this->id)
            ->get();
                    
        $intermediate_goods = DB::table('production_materials')
            ->join('productions', 'productions.id','=', 'production_materials.production_id')
            ->join('intermediate_goods', 'intermediate_goods.id','=', 'production_materials.intermediate_good_id')
            ->select('production_materials.id', 'intermediate_goods.name', 'intermediate_goods.stock_quantity','intermediate_goods.id as intermediate_good_id',
            'intermediate_goods.image','intermediate_goods.unit_of_measurement','production_materials.quantity','production_materials.cost','intermediate_goods.labour_cost')
            ->where('production_materials.production_id',$this->id)
            ->get();

        $insufficient_materials = false;

        if ($this->type == 'product') {

            $product = Product::with(['materials','intermediateGoods'])->where('id',$this->product_id)->first();

            if($product) {
                foreach($product->materials as $used_material) {
                    $material = Material::find($used_material->material_id);
                    if($material->current_stock_level < $used_material->quantity * $this->quantity) {
                        $insufficient_materials = true; break;
                    }
                }

                foreach($product->intermediateGoods as $used_intermediate_good) {
                    $intermediate_good = IntermediateGood::find($used_intermediate_good->intermediate_good_id);
                    if($intermediate_good->stock_quantity < $used_intermediate_good->quantity * $this->quantity) {
                        $insufficient_materials = true; break;
                    }
                }
            }

            
        }
        elseif ($this->type == 'intermediate_good') {

            $intermediate_good = IntermediateGood::with(['materialsUsed'])->where('id',$this->intermediate_good_id)->first();

            if($intermediate_good) {

                foreach($intermediate_good->materialsUsed as $used_material) {
                    $material = Material::find($used_material->material_id);
                    if($material->current_stock_level < $used_material->quantity * $this->quantity) {
                        $insufficient_materials = true; break;
                    }
                }
            }

        }
        else {
            $used_materials = ProductionMaterial::where('production_id',$this->id)->get();
            foreach($used_materials as $used_material) {
                $material = Material::find($used_material->material_id);
                if($material->current_stock_level < $used_material->quantity * $this->quantity) {
                    $insufficient_materials = true; break;
                }
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'quantity' => $this->quantity,
            'labour_cost' => $this->labour_cost,
            'deadline_date' => $this->deadline_date,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'type' => $this->type,
            'category' => $this->category,
            'product_id' => $this->product_id,
            'intermediate_good_id' => $this->intermediate_good_id,
            'intermediate_good' => $intermediate_good,
            'product' => $product,
            'insufficient_materials' => $insufficient_materials,
            'estimated_hours' => $this->estimated_hours,
            'actual_hours' => $this->actual_hours,
            'is_material' => $this->is_material,
            'materials' => $materials,
            'intermediate_goods' => $intermediate_goods,
            'assignee' => $assignee
        ];
    }
}
