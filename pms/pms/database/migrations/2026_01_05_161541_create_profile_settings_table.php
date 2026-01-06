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
    Schema::create('profile_settings', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();      // first_name
        $table->string('label');              // First Name
        $table->string('type');               // text, select, radio, textarea, date, file
        $table->json('options')->nullable();  // select / radio options
        $table->boolean('required')->default(0);
        $table->boolean('visible')->default(1);
        $table->integer('order')->default(0);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_settings');
    }
};
