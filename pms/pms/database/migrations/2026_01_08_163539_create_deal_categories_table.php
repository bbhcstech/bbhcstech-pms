<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deal_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default categories
        DB::table('deal_categories')->insert([
            ['name' => 'Quotation', 'slug' => 'quotation'],
            ['name' => 'New Business', 'slug' => 'new-business'],
            ['name' => 'Renewal', 'slug' => 'renewal'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('deal_categories');
    }
};
