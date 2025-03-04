<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntermediateGoodsMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['intermediate_good_id', 'material_id', 'quantity', 'cost'];
}
