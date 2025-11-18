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
            $table->uuid('id')->primary();
            $table->string('name'); // Translation key for system categories, actual name for user categories
            $table->enum('type', ['income', 'expense'])->index();
            $table->text('icon_svg'); // SVG code (25x25 pixels)
            $table->boolean('is_system')->default(false)->index();
            $table->uuid('user_id')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Composite index for efficient filtering
            $table->index(['user_id', 'type']);
            $table->index(['is_system', 'type']);
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
