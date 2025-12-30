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
         Schema::create('client_sub_categories', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // e.g., P1, P2, P3
        $table->unsignedBigInteger('client_category_id')->nullable();
        $table->foreign('client_category_id')->references('id')->on('client_categories')->onDelete('cascade');
        $table->timestamps();
       });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_sub_categories');
    }
};
