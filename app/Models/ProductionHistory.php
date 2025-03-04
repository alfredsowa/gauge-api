<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionHistory extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'production_history';

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function production() {
        return $this->belongsTo(Production::class);
    }
}
