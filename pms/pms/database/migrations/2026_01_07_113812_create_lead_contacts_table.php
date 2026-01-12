<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_contacts', function (Blueprint $table) {
            $table->id();

            // Contact Information
            $table->string('salutation')->nullable();
            $table->string('contact_name');
            $table->string('email')->unique();
            $table->string('mobile')->nullable();

            // Company Details
            $table->string('company_name')->nullable();
            $table->string('website')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            // Lead Source & Assignment
            $table->string('lead_source');
            $table->foreignId('lead_owner_id')->constrained('users'); // Lead owner
            $table->foreignId('added_by')->constrained('users'); // Who added the lead

            // Deal Information
            $table->boolean('create_deal')->default(false);
            $table->string('deal_name')->nullable();
            $table->decimal('deal_value', 15, 2)->nullable();
            $table->string('deal_currency')->nullable();
            $table->foreignId('deal_agent_id')->nullable()->constrained('users');
            $table->string('pipeline')->nullable();
            $table->string('deal_stage')->nullable();
            $table->string('deal_category')->nullable();
            $table->date('close_date')->nullable();
            $table->json('products')->nullable(); // store multiple products as JSON

            // Additional Information
            $table->string('status')->nullable();
            $table->string('industry')->nullable();
            $table->integer('lead_score')->default(50);
            $table->string('tags')->nullable(); // comma separated
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_contacts');
    }
};
