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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            // Requester details
            $table->unsignedBigInteger('requester_id')->nullable(); // Client or Employee
            $table->enum('requester_type', ['client', 'employee'])->default('client');

            // Requester name will now be fetched via relationship, so store only ID
            $table->string('requester_name');

            // Group & agent/project details
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->unsignedBigInteger('project_id')->nullable();

            // New structured fields
            $table->unsignedBigInteger('type_id')->nullable(); // instead of string 'type'
            $table->string('subject');
            $table->text('description');
            $table->string('attachment')->nullable();

            // Additional new fields from Blade form
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->string('channel')->nullable();
            $table->string('tags')->nullable(); // comma-separated

            $table->timestamps();

            // Optionally add foreign keys if you want (commented for now)
            // $table->foreign('requester_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('agent_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
        });
        
     
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
