<?php

use App\Models\Business;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Production;
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
        Schema::create('productions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Business::class);
            $table->foreignIdFor(Employee::class,'assignee_id');
            $table->string('title');
            $table->string('priority')->default('normal');
            $table->string('status')->default('backlog')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity')->nullable()->default(1);
            $table->decimal('labour_cost', 8, 2)->nullable()->default(0);
            $table->date('deadline_date')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('type')->nullable();
            $table->foreignIdFor(Product::class)->nullable();
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->foreignIdFor(User::class);
            $table->boolean('used')->nullable()->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Schema::create('employee_production', function (Blueprint $table) {
        //     $table->foreignIdFor(Employee::class);
        //     $table->foreignIdFor(Production::class);
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productions');
    }
};
