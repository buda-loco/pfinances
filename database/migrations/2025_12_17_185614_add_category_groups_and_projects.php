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
        // Category Groups - Bundle multiple categories together
        Schema::create('category_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Food & Dining", "Transportation", "Travel"
            $table->string('code')->unique(); // e.g., "FOOD", "TRANSPORT"
            $table->text('description')->nullable();
            $table->string('color')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add group_id to categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('parent_id')->constrained('category_groups')->onDelete('set null');
        });

        // Projects - Group expenses across categories
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Bali Holiday 2024", "New Camera Equipment"
            $table->string('code')->unique(); // e.g., "BALI24", "CAMGEAR"
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('color')->nullable();
            $table->enum('status', ['planning', 'active', 'completed', 'archived'])->default('active');
            $table->timestamps();
        });

        // Add project_id to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('category_id')->constrained('projects')->onDelete('set null');
        });

        // Tagging Rules - Regex-based auto-categorization
        Schema::create('tagging_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Uber/Taxi Rule"
            $table->text('pattern'); // Regex pattern, e.g., "/uber|taxi|rideshare/i"
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('field', ['description', 'merchant_name', 'notes_and_codes'])->default('description');
            $table->integer('priority')->default(0); // Higher priority rules run first
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Update accounts table to add ownership
        Schema::table('accounts', function (Blueprint $table) {
            $table->enum('ownership', ['buda', 'gupi', 'shared'])->default('buda')->after('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('ownership');
        });

        Schema::dropIfExists('tagging_rules');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropColumn('project_id');
        });

        Schema::dropIfExists('projects');

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });

        Schema::dropIfExists('category_groups');
    }
};
