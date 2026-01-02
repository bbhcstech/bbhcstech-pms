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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id(); // int(11) AUTO_INCREMENT PRIMARY KEY

            $table->string('setting_key', 100)
                  ->nullable()
                  ->index();

            $table->text('setting_value')
                  ->nullable();

            $table->string('setting_group', 50)
                  ->nullable()
                  ->default('general');

            $table->string('setting_type', 50)
                  ->nullable()
                  ->default('text');

            $table->integer('display_order')
                  ->nullable()
                  ->default(0);

            $table->timestamp('created_at')
                  ->useCurrent();

            $table->timestamp('updated_at')
                  ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
