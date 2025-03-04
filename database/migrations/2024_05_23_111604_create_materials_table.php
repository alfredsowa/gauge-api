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
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('type');
            $table->foreignIdFor(User::class,'added_by');
            $table->foreignIdFor(Business::class);
            $table->foreignIdFor(MaterialCategory::class);
            $table->integer('current_stock_level')->nullable()->default(0);
            $table->integer('minimum_stock_level')->nullable()->default(0);
            $table->text('image')->nullable();
            $table->text('description')->nullable();
            $table->string('unit_of_measurement')->nullable();
            $table->decimal('cost_per_unit', 10, 2)->nullable()->default(0);
            $table->decimal('total_cost', 10, 2)->nullable()->default(0);
            $table->decimal('total_items', 10, 2)->nullable()->default(0);
            $table->string('storage_location')->nullable();
            $table->string('status')->nullable()->default('Draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
