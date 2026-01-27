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
        Schema::table('users', function (Blueprint $table) {
            // Add login_allowed column with default 1 (allowed)
            $table->boolean('login_allowed')->default(1)->after('profile_image');

            // Also add email_notifications column if missing
            $table->boolean('email_notifications')->default(1)->after('login_allowed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['login_allowed', 'email_notifications']);
        });
    }
};
