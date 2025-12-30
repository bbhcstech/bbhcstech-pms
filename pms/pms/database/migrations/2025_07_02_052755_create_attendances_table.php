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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date'); // Required for grid & month view
            $table->enum('status', ['present', 'absent', 'holiday', 'late', 'half_day', 'leave'])->default('absent');

            $table->time('clock_in')->nullable();  // e.g., 09:58
            $table->time('clock_out')->nullable(); // e.g., 18:02

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
