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
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('account_type_id')->constrained()->onDelete('restrict');
            $table->string('name', 255);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('currency', 3)->default('VND');
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable(); // Hex color format #RRGGBB
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Indexes for performance
            $table->index('user_id');
            $table->index('account_type_id');
            $table->index(['user_id', 'is_active']);
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
