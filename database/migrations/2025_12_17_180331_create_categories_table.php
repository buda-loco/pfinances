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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "🔄 Internal Transfer", "☕ Cafe"
            $table->string('code')->unique(); // e.g., "XXINTER", "CAFE", "RESTAU"
            $table->foreignId('parent_id')->nullable()->constrained('categories')->onDelete('cascade'); // Category Group
            $table->enum('category_type', ['income', 'expense', 'transfer'])->default('expense');
            $table->string('frollo_category')->nullable(); // Original Frollo category name
            $table->decimal('daily_budget', 10, 2)->nullable();
            $table->decimal('weekly_budget', 10, 2)->nullable();
            $table->decimal('monthly_budget', 10, 2)->nullable();
            $table->string('color')->nullable(); // Hex color for UI
            $table->string('icon')->nullable(); // Icon class or emoji
            $table->text('keywords')->nullable(); // Comma-separated keywords for auto-matching
            $table->integer('order')->default(0); // For custom sorting
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('code');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
