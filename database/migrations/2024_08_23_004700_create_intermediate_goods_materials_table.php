<?php

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
        Schema::create('intermediate_goods_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\IntermediateGood::class);
            $table->foreignIdFor(\App\Models\Material::class);
            $table->decimal('quantity')->default(0);
            $table->decimal('cost')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('intermediate_goods_materials');
    }
};
