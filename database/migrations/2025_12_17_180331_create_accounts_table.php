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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // Primary owner
            $table->foreignId('entity_type_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('account_type', ['personal', 'shared', 'business'])->default('personal');
            $table->string('name'); // e.g., "ING Daily", "Bankwest Platinum"
            $table->string('account_number')->nullable();
            $table->string('institution')->nullable(); // e.g., "ING Australia", "Bankwest"
            $table->string('currency', 3)->default('AUD'); // AUD, USD, COP
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
