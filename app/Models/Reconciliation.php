<?php

namespace App\Models;

use App\Casts\Json;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reconciliation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'categories' => 'array',
            'data' => Json::class
        ];
    }
}
