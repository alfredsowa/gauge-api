<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMaterial extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function materials() {
        return $this->belongsTo(Material::class);
    }

    public function production() {
        return $this->belongsTo(Production::class);
    }

    public function intermediateGoods() {
        return $this->belongsTo(IntermediateGood::class);
    }
}
