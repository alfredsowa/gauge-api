<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function supplier(): BelongsTo{
        return $this->belongsTo(Supplier::class);
    }

    public function category(): BelongsTo{
        return $this->belongsTo(MaterialCategory::class,'material_category_id');
    }

    public function productions(): BelongsTo{
        return $this->belongsTo(ProductionMaterial::class,'material_id');
    }

    public function products(): BelongsTo{
        return $this->belongsTo(ProductsMaterial::class,'material_id');
    }

    public function business(): BelongsTo{
        return $this->belongsTo(Business::class);
    }

    public function purchases(): HasMany{
        return $this->hasMany(Purchase::class);
    }

    public function createdBy(): BelongsTo{
        return $this->belongsTo(User::class,'added_by');
    }


}
