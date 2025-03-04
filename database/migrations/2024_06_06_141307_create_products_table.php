<?php

use App\Models\Business;
use App\Models\MaterialCategory;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Business::class);
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('wholesale_price', 10, 2);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->foreignIdFor(MaterialCategory::class)->nullable();
            $table->foreignIdFor(Supplier::class)->nullable();
            $table->foreignIdFor(User::class);
            $table->unsignedInteger('stock_quantity');
            $table->unsignedInteger('min_stock_quantity')->default(0);
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_produced')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->json('attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
