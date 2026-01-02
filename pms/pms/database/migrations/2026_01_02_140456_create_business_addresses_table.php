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
        Schema::create('business_addresses', function (Blueprint $table) {
        $table->id();
        $table->string('location');      // Kolkata
        $table->text('address');         // Full address
        $table->string('country');       // India
        $table->string('tax_name')->nullable();
        $table->boolean('is_default')->default(false);
        $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_addresses');
    }
};
