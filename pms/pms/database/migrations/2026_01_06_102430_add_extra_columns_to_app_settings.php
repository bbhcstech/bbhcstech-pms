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
         Schema::table('app_settings', function (Blueprint $table) {

            if (!Schema::hasColumn('app_settings', 'page')) {
                $table->string('page')->nullable()->after('section');
            }

            if (!Schema::hasColumn('app_settings', 'placeholder')) {
                $table->string('placeholder')->nullable()->after('unit');
            }

});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            //
        });
    }
};
