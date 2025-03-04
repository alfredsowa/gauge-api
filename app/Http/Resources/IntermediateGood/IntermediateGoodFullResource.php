<?php

namespace App\Http\Resources\IntermediateGood;

use App\Http\Resources\Business\BusinessListResource;
use App\Http\Resources\Supplier\SupplierFormResource;
use App\Models\Business;
use App\Models\IntermediateGoodsCategory;
use App\Models\IntermediateGoodsMaterial;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IntermediateGoodFullResource extends JsonResource
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

        $production_history = DB::table('productions')
            ->join('employees', 'employees.id','=', 'productions.assignee_id')
            ->select('productions.id as production_id', 'productions.title', 'productions.status','productions.quantity as production_quantity',
            'productions.start_date','productions.completed_at','employees.first_name','employees.last_name')
            ->where('productions.intermediate_good_id',$this->id)
            ->get();

        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'user_id' => $this->user_id,
            'added_by' => User::withTrashed()->select(['name','firstname'])->where('id',$this->user_id)->first(),
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'stock_quantity' => $this->stock_quantity,
            'min_stock_quantity' => $this->min_stock_quantity,
            'intermediate_goods_category_id' => $this->intermediate_goods_category_id,
            'image' => $this->image,
            'labour_cost' => $this->labour_cost,
            'is_reusable_after_damaged' => $this->is_reusable_after_damaged,
            'status' => $this->status,
            'productions' => $production_history,
            'used_materials' => IntermediateGoodMaterialsResource::collection(IntermediateGoodsMaterial::where('intermediate_good_id',$this->id)->get()),
            'materials' => $materials,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
