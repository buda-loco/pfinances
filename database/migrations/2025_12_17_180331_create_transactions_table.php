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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('entity_type_id')->nullable()->constrained()->onDelete('set null');

            // Transaction details
            $table->string('external_id')->nullable()->unique(); // Original transaction ID from bank
            $table->date('transaction_date');
            $table->date('posted_date')->nullable();
            $table->string('description'); // Original bank description
            $table->text('user_description')->nullable(); // Custom user description
            $table->text('notes_and_codes')->nullable(); // "notes y codigos" - manual keyword tagging
            $table->string('location_tag')->nullable(); // "lugar tag"

            // Amount and currency
            $table->decimal('amount', 15, 2); // Original amount
            $table->string('currency', 3); // Original currency
            $table->decimal('amount_in_aud', 15, 2)->nullable(); // Converted amount
            $table->enum('credit_debit', ['credit', 'debit'])->nullable();

            // Categorization
            $table->string('code')->nullable(); // Auto-matched code (CAFE, RESTAU, etc.)
            $table->enum('income_outcome', ['income', 'outcome', 'internal'])->nullable();

            // Bank/merchant details
            $table->string('merchant_name')->nullable();
            $table->string('transaction_type')->nullable();
            $table->string('budget_category')->nullable(); // Frollo budget category

            // Project and work tracking
            $table->string('project')->nullable();
            $table->string('work_type')->nullable(); // "Tipo de trabajo"
            $table->string('work_code')->nullable();
            $table->string('client_type')->nullable(); // "Tipo de Cliente"

            // Owner tracking (Gupi, Buda, BYG, Bold, Oddjobs)
            $table->string('owner_automatic')->nullable();
            $table->string('owner_manual')->nullable();

            // Flags
            $table->boolean('is_manual')->default(false); // Manual entry vs imported
            $table->boolean('is_reconciled')->default(false);
            $table->boolean('is_included')->default(true); // "included" column

            // Additional metadata
            $table->json('metadata')->nullable(); // For extra fields

            $table->timestamps();

            // Indexes for performance
            $table->index(['account_id', 'transaction_date']);
            $table->index(['category_id', 'transaction_date']);
            $table->index('code');
            $table->index('transaction_date');
            $table->index('currency');
            $table->index(['owner_manual', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
