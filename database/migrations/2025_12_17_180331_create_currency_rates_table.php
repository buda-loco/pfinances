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
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency', 3); // e.g., "USD"
            $table->string('to_currency', 3); // e.g., "AUD"
            $table->decimal('rate', 10, 6); // Exchange rate
            $table->date('effective_date'); // Date this rate is effective
            $table->timestamps();

            $table->index(['from_currency', 'to_currency', 'effective_date']);
            $table->unique(['from_currency', 'to_currency', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
