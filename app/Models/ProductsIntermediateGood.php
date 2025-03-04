<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsIntermediateGood extends Model
{
    use HasFactory;
    protected $table = 'products_intermediate_goods';

    protected $fillable = ['product_id', 'intermediate_good_id', 'quantity'];
}
