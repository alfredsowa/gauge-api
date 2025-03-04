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
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('wholesale_markup',3,2)->after('wholesale_price')->default(0)->nullable();
            $table->decimal('retail_markup',3,2)->after('wholesale_markup')->default(0)->nullable();
            $table->boolean('use_manual_pricing')->after('retail_markup')->default(false)->nullable();
            $table->decimal('labour_cost',8,2)->after('wholesale_price')->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['labour_cost','wholesale_markup','retail_markup','use_manual_pricing']);
        });
    }
};
