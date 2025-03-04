<?php

use App\Models\Business;
use App\Models\Material;
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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Business::class);
            $table->foreignIdFor(Supplier::class);
            $table->foreignIdFor(Material::class);
            $table->foreignIdFor(User::class,'added_by');
            $table->string('status')->default('Supplied'); // Status of the order (e.g., Draft, Supplied)
            $table->date('purchase_date'); // Date when the purchase was made
            $table->text('purchase_details')->nullable();
            $table->decimal('quantity', 10, 2); 
            $table->decimal('unit_price', 10, 2); 
            $table->decimal('amount_paid', 10, 2); // Payment amount
            $table->decimal('tax', 10, 2)->nullable(); // Tax amount
            $table->decimal('discounts', 10, 2)->nullable(); // Discounts applied
            $table->decimal('shipping', 10, 2)->nullable(); // Shipping cost
            $table->string('invoice_number')->nullable(); 
            $table->string('invoice_upload')->nullable(); // Path to the uploaded invoice file
            $table->text('notes')->nullable(); // Additional notes
            $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
