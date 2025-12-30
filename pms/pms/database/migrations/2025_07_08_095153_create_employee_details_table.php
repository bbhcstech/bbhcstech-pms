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
        Schema::create('employee_details', function (Blueprint $table) {
        $table->id();
    
        // Explicitly define foreign key as unsignedBigInteger
        $table->unsignedBigInteger('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    
        $table->unsignedBigInteger('designation_id')->nullable();
        $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
    
        $table->unsignedBigInteger('department_id')->nullable();
        $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
    
        $table->string('employee_id')->unique();
        $table->string('salutation')->nullable();
        $table->string('country')->nullable();
        $table->string('mobile')->nullable();
        $table->string('gender')->nullable();
        $table->date('joining_date');
        $table->date('dob')->nullable();
        $table->unsignedBigInteger('reporting_to')->nullable();
        $table->foreign('reporting_to')->references('id')->on('users')->onDelete('set null');
    
        $table->string('language')->nullable();
        $table->unsignedBigInteger('user_role')->nullable(); // for future role table
        $table->text('address')->nullable();
        $table->text('about')->nullable();
        $table->boolean('login_allowed')->default(true);
        $table->boolean('email_notifications')->default(true);
        $table->decimal('hourly_rate', 10, 2)->nullable();
        $table->string('slack_member_id')->nullable();
        $table->text('skills')->nullable();
        $table->date('probation_end_date')->nullable();
        $table->date('notice_start_date')->nullable();
        $table->date('notice_end_date')->nullable();
        $table->string('employment_type')->nullable();
        $table->string('marital_status')->nullable();
        $table->text('business_address');
    
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_details');
    }
};
