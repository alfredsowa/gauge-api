<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function business(){
        return $this->belongsTo(Business::class);
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }

    public function material(){
        return $this->belongsTo(Material::class);
    }

    public function addedBy(){
        return $this->belongsTo(User::class);
    }
}
