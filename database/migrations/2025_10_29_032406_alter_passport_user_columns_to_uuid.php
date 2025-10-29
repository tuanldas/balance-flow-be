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
        // oauth_access_tokens
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->index()->after('id');
        });

        // oauth_auth_codes
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->uuid('user_id')->index()->after('id');
        });

        // oauth_device_codes
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->uuid('user_id')->nullable()->index()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to bigint
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_access_tokens', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->index()->after('id');
        });

        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->foreignId('user_id')->index()->after('id');
        });

        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
        Schema::table('oauth_device_codes', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->index()->after('id');
        });
    }
};
