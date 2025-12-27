<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            // SQLite restriction: cannot modify enum column directly easily.
            // Best approach for SQLite: Add new column, copy data (optional), drop old.
            // But since this is dev, we can just drop the column and add it back as string.
            // Note: Data in account_type will be lost if we drop.
            // Alternative: Change it.

            // We'll try to just change it first, if not we usually need DB::statement.
            // But standard migrations might fail on SQLite enums.

            // Let's assume we can just "add" ownership first.
            if (!Schema::hasColumn('accounts', 'ownership')) {
                $table->string('ownership')->default('buda')->after('account_type');
            }
        });

        // Changing enum to string in SQLite is complex. 
        // We will perform a raw statement to disable constraints, create temp, copy, drop, rename?
        // Actually, just creating a new migration that redefines the table is often cleaner for "Code experiments".
        // But let's try standard Schema modification.

        // Since the previous migration defined it as ENUM, and SQLite checks that.
        // We need to remove that check. 
        // Laravel's change() method works for some types but ENUM in SQLite is distinct.

        // Let's use a workaround: make column string change.
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('account_type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropColumn('ownership');
            // Reverting account_type is hard, we leave it as string.
        });
    }
};
