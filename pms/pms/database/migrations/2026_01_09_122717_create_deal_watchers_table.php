<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deal_watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['deal_id', 'user_id']); // Prevent duplicate watchers
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deal_watchers');
    }
};
