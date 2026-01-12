<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_contracts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique();
            $table->string('subject');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('set null');
            $table->text('description')->nullable();
            $table->enum('type', ['normal', 'special', 'fixed_price', 'time_material'])->default('normal');
            $table->decimal('contract_value', 15, 2);
            $table->string('currency')->default('INR');
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'completed'])->default('draft');
            $table->text('terms')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->date('signed_date')->nullable();
            $table->string('signed_by')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
