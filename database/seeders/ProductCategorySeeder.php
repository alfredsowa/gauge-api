<?php

namespace Database\Seeders;

use App\Models\IntermediateGoodsCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IntermediateGoodsCategory::factory(7)->create();
    }
}
