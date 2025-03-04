<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IntermediateGood extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function category() {
        return $this->belongsTo(IntermediateGoodsCategory::class);
    }

    public function business() {
        return $this->belongsTo(Business::class);
    }

//    public function supplier() {
//        return $this->belongsTo(Supplier::class);
//    }

    public function materialsUsed() {
        return $this->hasMany(IntermediateGoodsMaterial::class);
    }

    public function productions() {
        return $this->hasMany(Production::class);
    }

    public function productionHistory() {
        return $this->hasMany(ProductionMaterial::class,'intermediate_good_id','id');
    }


}
