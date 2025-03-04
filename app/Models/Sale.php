<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function addedBy() {
        return $this->belongsTo(User::class);
    }

    public function soldBy() {
        return $this->belongsTo(Employee::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
