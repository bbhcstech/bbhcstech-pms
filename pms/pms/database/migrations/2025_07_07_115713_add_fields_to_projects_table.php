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
        Schema::table('projects', function (Blueprint $table) {
        $table->date('start_date')->nullable()->after('description');
        $table->date('deadline')->nullable()->after('start_date');
        $table->enum('status', ['not started', 'in progress', 'on hold', 'completed'])
              ->default('not started')
              ->after('deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        SSchema::table('projects', function (Blueprint $table) {
        $table->dropColumn(['start_date', 'deadline', 'status']);
         });
    }
};
