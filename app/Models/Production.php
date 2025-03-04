<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Production extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function assignee(): BelongsTo {
        return $this->belongsTo(Employee::class,'assignee_id');
    }
    
    public function history(): HasMany {
        return $this->hasMany(ProductionHistory::class);
    }
    
    public function materials(): HasMany {
        return $this->hasMany(ProductionMaterial::class);
    }
    
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    // public static function getRecentProductions($count) {
    //     return $query->where('business_id',auth()->user()->business_id)
    //     ->orderByDesc('updated_at')->take($count);


    //     // return $this->belongsTo(Business::class);
    // }
}
