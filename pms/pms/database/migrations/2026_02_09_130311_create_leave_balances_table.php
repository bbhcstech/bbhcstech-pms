<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->integer('allocated_leaves');
            $table->integer('used_leaves')->default(0);
            $table->integer('remaining_leaves');
            $table->integer('carried_forward')->default(0);
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'year']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_balances');
    }
};
