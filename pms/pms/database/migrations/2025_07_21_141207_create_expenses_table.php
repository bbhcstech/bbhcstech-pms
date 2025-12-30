<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('expenses', function (Blueprint $table) {
        $table->id();
        $table->string('item_name');
        $table->string('currency')->default('INR');
        $table->decimal('exchange_rate', 10, 2)->default(1);
        $table->decimal('price', 15, 2);
        $table->date('purchase_date');
        $table->unsignedBigInteger('employee_id')->nullable();
        $table->unsignedBigInteger('project_id')->nullable();
        $table->unsignedBigInteger('category_id')->nullable();
        $table->string('purchased_from')->nullable();
        $table->unsignedBigInteger('bank_account_id')->nullable();
        $table->text('description')->nullable();
        $table->string('bill')->nullable();
        $table->timestamps();

        // Foreign keys (optional)
        $table->foreign('employee_id')->references('id')->on('users')->nullOnDelete();
        $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        $table->foreign('category_id')->references('id')->on('expense_categories')->nullOnDelete();
        $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->nullOnDelete();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
