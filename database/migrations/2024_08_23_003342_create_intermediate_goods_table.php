<?php

use App\Models\Business;
use App\Models\IntermediateGoodsCategory;
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
        Schema::create('intermediate_goods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignIdFor(User::class,'added_by');
            $table->foreignIdFor(Business::class);
            $table->foreignIdFor(IntermediateGoodsCategory::class)->nullable();
            $table->integer('stock_quantity')->nullable()->default(0);
            $table->integer('min_stock_quantity')->nullable()->default(0);
            $table->text('image')->nullable();
            $table->text('description')->nullable();
            $table->string('unit_of_measurement')->nullable()->default('Piece');
            $table->decimal('labour_cost', 10, 2)->nullable()->default(0);
            $table->decimal('total_items', 10, 2)->nullable()->default(0);
            $table->string('storage_location')->nullable();
            $table->boolean('status')->nullable()->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intermediate_goods');
    }
};
