<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->enum('marital_status', ['single', 'married'])->nullable();
            $table->text('address')->nullable();
            $table->text('about')->nullable();
            $table->string('country')->nullable();
            $table->string('language')->nullable();
            $table->string('slack_id')->nullable();
            $table->boolean('email_notify')->default(true);
            $table->boolean('google_calendar')->default(false);
            $table->string('profile_image')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mobile', 'gender', 'dob', 'marital_status', 'address',
                'about', 'country', 'language', 'slack_id',
                'email_notify', 'google_calendar', 'profile_image'
            ]);
        });
    }
};
