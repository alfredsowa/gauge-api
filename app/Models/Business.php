<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function users(): HasMany {
        return $this->hasMany(User::class);
    }

    public function suppliers(): HasMany {
        return $this->hasMany(Supplier::class);
    }

    public function product_categories(): HasMany {
        return $this->hasMany(IntermediateGoodsCategory::class);
    }

    public function product_types(): HasMany {
        return $this->hasMany(ProductType::class);
    }

    public function material_categories(): HasMany {
        return $this->hasMany(MaterialCategory::class);
    }

    public function overheads(): HasMany {
        return $this->hasMany(Overhead::class);
    }
}
