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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->nullable()->constrained()->onDelete('cascade'); // null = all accounts
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('entity_type_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('AUD');
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'yearly'])->default('monthly');
            $table->date('period_start');
            $table->date('period_end');

            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'period_start', 'period_end']);
            $table->index(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
