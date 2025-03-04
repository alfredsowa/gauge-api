<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function category() {
        return $this->belongsTo(IntermediateGoodsCategory::class);
    }

    public function business() {
        return $this->belongsTo(Business::class);
    }

    public function supplier() {
        return $this->belongsTo(Supplier::class);
    }

    public function materials() {
        return $this->hasMany(ProductsMaterial::class);
    }

    public function intermediateGoods() {
        return $this->hasMany(ProductsIntermediateGood::class);
    }

}
