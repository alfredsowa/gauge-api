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
        Schema::table('businesses', function (Blueprint $table) {
            $table->string('industry')->nullable()->after('name');
            $table->string('business_type')->nullable()->after('industry');
            $table->string('business_size')->nullable()->after('business_type');
            $table->string('website',255)->nullable()->after('contact');
            $table->string('city',255)->nullable()->after('website');
            $table->string('tax_identification_number')->nullable()->after('city');
            $table->string('currency',255)->nullable()->after('country');
            $table->string('currency_symbol',255)->nullable()->after('currency');
            $table->string('language',255)->nullable()->after('currency');
            $table->string('timezone',255)->nullable()->after('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['industry','business_type','business_size','website',
            'city','tax_identification_number','currency','language','timezone']);
        });
    }
};
