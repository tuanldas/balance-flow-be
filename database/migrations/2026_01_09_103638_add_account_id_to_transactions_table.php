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
        Schema::table('transactions', function (Blueprint $table) {
            // Add account_id column (required - every transaction must belong to an account)
            $table->foreignUuid('account_id')
                ->after('user_id')
                ->constrained()
                ->onDelete('restrict');

            // Add index for better query performance
            $table->index(['user_id', 'account_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Drop foreign key, index and column
            $table->dropForeign(['account_id']);
            $table->dropIndex(['user_id', 'account_id']);
            $table->dropColumn('account_id');
        });
    }
};
