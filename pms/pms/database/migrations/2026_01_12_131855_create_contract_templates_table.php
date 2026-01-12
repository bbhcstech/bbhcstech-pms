<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_contract_templates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->text('content');
            $table->enum('type', ['normal', 'special', 'fixed_price', 'time_material'])->default('normal');
            $table->decimal('default_value', 15, 2)->nullable();
            $table->string('currency')->default('INR');
            $table->integer('duration_days')->default(365);
            $table->text('terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_templates');
    }
};
