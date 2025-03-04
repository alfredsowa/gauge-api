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
            $table->decimal('estimated_hours',8,2)->after('labour_cost')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('intermediate_goods', function (Blueprint $table) {
            $table->dropColumn(['estimated_hours']);
        });
    }
};
