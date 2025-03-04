<?php

use App\Models\Business;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Business::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Employee::class);
            $table->foreignIdFor(Customer::class);
            $table->string('sale_type')->default('retail'); // e.g., retail, wholesale, etc.
            $table->string('sales_channel')->default('in-person'); // e.g., in-person, website, social media, etc.
            $table->dateTime('sale_date_time'); // Date and time of the sale
            $table->integer('quantity')->default(1); // Details of the sale (items, quantities, prices, etc.)
            $table->decimal('total_amount_paid', 10, 2)->default(0); // Total amount of the sale
            $table->string('payment_status')->default('pending'); // e.g., paid, pending, etc.
            $table->string('payment_method')->default('cash'); // e.g., credit card, cash, etc.
            $table->decimal('selling_price', 10, 2)->nullable()->default(0); // Details of the sale (items, quantities, prices, etc.)
            $table->string('order_status')->nullable(); // e.g., completed, canceled, refunded
            $table->string('invoice_number')->nullable(); // Unique invoice number
            $table->text('delivery_details')->nullable(); // Details for delivery if applicable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
