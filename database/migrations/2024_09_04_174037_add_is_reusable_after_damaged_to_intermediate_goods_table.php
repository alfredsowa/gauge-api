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
        Schema::table('intermediate_goods', function (Blueprint $table) {
            $table->boolean('is_reusable_after_damaged')->default(false)->after('min_stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intermediate_goods', function (Blueprint $table) {
            $table->dropColumn('is_reusable_after_damaged');
        });
    }
};
