<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deal_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6B7280');
            $table->integer('order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Insert default stages
        DB::table('deal_stages')->insert([
            ['name' => 'Additional', 'color' => '#EF4444', 'order' => 1, 'is_default' => true],
            ['name' => 'Qualified', 'color' => '#F59E0B', 'order' => 2, 'is_default' => true],
            ['name' => 'Initial Contact', 'color' => '#10B981', 'order' => 3, 'is_default' => true],
            ['name' => 'Schedule Appointment', 'color' => '#3B82F6', 'order' => 4, 'is_default' => true],
            ['name' => 'Concreted', 'color' => '#8B5CF6', 'order' => 5, 'is_default' => true],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('deal_stages');
    }
};
