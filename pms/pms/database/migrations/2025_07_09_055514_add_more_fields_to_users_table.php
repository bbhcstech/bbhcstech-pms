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
            $table->string('designation')->nullable()->after('mobile');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('designation');
            $table->date('dob')->nullable()->after('gender');
            $table->enum('marital_status', ['single', 'married'])->nullable()->after('dob');
            $table->text('address')->nullable()->after('marital_status');
            $table->text('about')->nullable()->after('address');
            $table->string('country')->nullable()->after('about');
            $table->string('language')->nullable()->after('country');
            $table->string('slack_id')->nullable()->after('language');
            $table->boolean('email_notify')->default(1)->after('slack_id');
            $table->boolean('google_calendar')->default(0)->after('email_notify');
            $table->string('profile_image')->nullable()->after('google_calendar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'designation',
                'gender',
                'dob',
                'marital_status',
                'address',
                'about',
                'country',
                'language',
                'slack_id',
                'email_notify',
                'google_calendar',
                'profile_image'
            ]);
        });
    }
};
