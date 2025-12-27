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
        Schema::create('entity_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Personal, Work, Shared
            $table->json('user_ids')->nullable(); // Array of user IDs who have access
            $table->string('color')->nullable(); // For UI color coding
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entity_types');
    }
};
