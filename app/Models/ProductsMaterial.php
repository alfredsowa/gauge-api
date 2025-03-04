<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'material_id', 'quantity', 'cost'];

    public function material() {
        return $this->belongsTo(Material::class);
    }
}
