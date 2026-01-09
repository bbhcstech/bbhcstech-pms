<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('deal_name');
            $table->string('lead_name');
            $table->string('contact_details');
            $table->decimal('value', 15, 2)->default(0.00);
            $table->date('close_date');
            $table->date('next_follow_up')->nullable();
            $table->unsignedBigInteger('deal_agent_id')->nullable();
            $table->unsignedBigInteger('deal_stage_id');
            $table->unsignedBigInteger('deal_category_id')->nullable();
            $table->string('pipeline')->default('Sales Pipeline');
            $table->string('product')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deals');
    }
};
