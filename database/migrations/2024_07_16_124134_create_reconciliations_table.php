<?php

use App\Models\Business;
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
        Schema::create('reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Business::class);
            $table->foreignIdFor(User::class);
            $table->string('title')->nullable();
            $table->date('period');
            $table->string('type');
            $table->text('categories')->nullable();
            $table->json('data')->nullable();
            $table->boolean('closed')->nullable()->default(false);
            $table->boolean('paused')->nullable()->default(false);
            $table->dateTime('closed_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reconciliations');
    }
};
