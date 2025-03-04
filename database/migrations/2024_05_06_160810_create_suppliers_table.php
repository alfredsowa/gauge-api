<?php

use App\Models\Business;
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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Business::class);
            $table->string('contact_person',255);
            $table->string('company_name',255);
            $table->string('contact_detail',255);
            $table->string('location',255);
            $table->text('note')->nullable();
            $table->float('total_spending')->default(0)->nullable();
            $table->integer('total_orders')->default(0)->nullable();
            $table->date('last_order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
