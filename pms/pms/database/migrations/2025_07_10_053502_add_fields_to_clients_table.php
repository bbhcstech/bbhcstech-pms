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
         Schema::table('clients', function (Blueprint $table) {
            // Account Details
            $table->string('salutation')->nullable();
            $table->string('password');
            $table->string('country')->nullable();
            $table->string('mobile')->nullable();
            $table->string('profile_picture')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->string('language')->nullable();
            $table->unsignedBigInteger('client_category_id')->nullable();
            $table->unsignedBigInteger('client_sub_category_id')->nullable();
            $table->boolean('login_allowed')->default(true);
            $table->boolean('email_notifications')->default(true);

            // Company Details
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_name')->nullable();
            $table->string('tax_number')->nullable(); // e.g. GST/VAT number
            $table->string('office_phone')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->unsignedBigInteger('added_by')->nullable(); // admin/user ID
            $table->text('company_address')->nullable();
            $table->text('shipping_address')->nullable();
            $table->text('note')->nullable();
            $table->string('company_logo')->nullable();

            // Foreign keys (optional)
            // $table->foreign('client_category_id')->references('id')->on('client_categories');
            // $table->foreign('client_sub_category_id')->references('id')->on('client_sub_categories');
            // $table->foreign('added_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'salutation', 'password', 'country', 'mobile', 'profile_picture',
                'gender', 'language', 'client_category_id', 'client_sub_category_id',
                'login_allowed', 'email_notifications', 'company_name', 'website',
                'tax_name', 'tax_number', 'office_phone', 'city', 'state', 'postal_code',
                'added_by', 'company_address', 'shipping_address', 'note', 'company_logo',
            ]);
        });
    }
};
