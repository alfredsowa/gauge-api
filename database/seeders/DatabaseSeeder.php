<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\MaterialCategory;
use App\Models\Product;
use App\Models\IntermediateGoodsCategory;
use App\Models\Production;
use App\Models\ProductType;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Database\Factories\EmailChangeFactory;
use Database\Factories\PurchaseFactory;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(3)->create();
        // Business::factory(5)->create();
        // Supplier::factory(10)->create();
        // ProductType::factory(7)->create();
        // ProductCategory::factory(7)->create();
        // MaterialCategory::factory(7)->create();
        // Purchase::factory(5)->create();
        // Product::factory(7)->create();
        // Employee::factory(7)->create();
        // Production::factory(5)->create();
        // Customer::factory(5)->create();
        Sale::factory(5)->create();
    }
}
