<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leave_policies', function (Blueprint $table) {
            $table->id();
            $table->integer('annual_leaves')->default(18);
            $table->boolean('pro_rate_enabled')->default(true);
            $table->date('fiscal_year_start')->default('2024-04-01');
            $table->date('fiscal_year_end')->default('2025-03-31');
            $table->boolean('allow_carry_forward')->default(false);
            $table->integer('max_carry_forward')->nullable();
            $table->decimal('leave_monetary_value', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_policies');
    }
};
