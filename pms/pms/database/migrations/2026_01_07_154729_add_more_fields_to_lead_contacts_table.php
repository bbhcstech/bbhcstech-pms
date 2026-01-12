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
        Schema::table('lead_contacts', function (Blueprint $table) {
            // Add new columns for contact information
            // $table->string('salutation')->nullable()->after('id');
            // $table->string('mobile')->nullable()->after('email');

            // Add new columns for company information
            // $table->string('website')->nullable()->after('company_name');
            // $table->string('phone')->nullable()->after('website');
            // $table->text('address')->nullable()->after('phone');
            // $table->string('city')->nullable()->after('address');
            // $table->string('state')->nullable()->after('city');
            // $table->string('country')->nullable()->after('state');
            // $table->string('postal_code')->nullable()->after('country');
            // $table->string('industry')->nullable()->after('postal_code');

            // Add new columns for lead source & status
            // $table->string('lead_source')->nullable()->after('industry');
            // $table->string('status')->default('new')->after('lead_source');
            // $table->integer('lead_score')->default(0)->after('status');
            // $table->text('tags')->nullable()->after('lead_score');

            // Add new columns for deal information
            // $table->boolean('create_deal')->default(false)->after('tags');
            // $table->string('deal_name')->nullable()->after('create_deal');
            // $table->decimal('deal_value', 15, 2)->nullable()->after('deal_name');
            // $table->string('deal_currency')->default('INR')->after('deal_value');
            // $table->unsignedBigInteger('deal_agent_id')->nullable()->after('deal_currency');
            // $table->string('pipeline')->nullable()->after('deal_agent_id');
            // $table->string('deal_stage')->nullable()->after('pipeline');
            // $table->string('deal_category')->nullable()->after('deal_stage');
            // $table->date('close_date')->nullable()->after('deal_category');
            // $table->json('products')->nullable()->after('close_date');

            // // Add new column for description
            // $table->text('description')->nullable()->after('products');

            // // Add foreign key for deal_agent_id
            // $table->foreign('deal_agent_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            // Drop the columns in reverse order
            $table->dropForeign(['deal_agent_id']);
            $table->dropColumn([
                'salutation',
                'mobile',
                'website',
                'phone',
                'address',
                'city',
                'state',
                'country',
                'postal_code',
                'industry',
                'lead_source',
                'status',
                'lead_score',
                'tags',
                'create_deal',
                'deal_name',
                'deal_value',
                'deal_currency',
                'deal_agent_id',
                'pipeline',
                'deal_stage',
                'deal_category',
                'close_date',
                'products',
                'description'
            ]);
        });
    }
};
