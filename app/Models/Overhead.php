<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overhead extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'title',
        'cost',
    ];

    public function business() {
        return $this->belongsTo(Business::class);
    }
}
