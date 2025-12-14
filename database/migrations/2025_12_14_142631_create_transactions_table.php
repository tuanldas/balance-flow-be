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
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('category_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->string('merchant_name')->nullable();
            $table->dateTime('transaction_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->timestamps();

            $table->index('user_id');
            $table->index('category_id');
            $table->index('transaction_date');
            $table->index('status');
            $table->index(['user_id', 'transaction_date']);
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
