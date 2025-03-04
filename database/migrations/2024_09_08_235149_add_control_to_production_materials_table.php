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
        Schema::table('production_materials', function (Blueprint $table) {
            $table->boolean('control')->default(true)->after('is_accounted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_materials', function (Blueprint $table) {
            $table->dropColumn(['control']);
        });
    }
};
