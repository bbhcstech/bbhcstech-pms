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

            // Add 'page' safely
            if (!Schema::hasColumn('app_settings', 'page')) {
                if (Schema::hasColumn('app_settings', 'section')) {
                    $table->string('page')->nullable()->after('section');
                } else {
                    $table->string('page')->nullable(); // just add at the end
                }
            }

            // Add 'placeholder' safely
            if (!Schema::hasColumn('app_settings', 'placeholder')) {
                if (Schema::hasColumn('app_settings', 'unit')) {
                    $table->string('placeholder')->nullable()->after('unit');
                } else {
                    $table->string('placeholder')->nullable();
                }
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_settings', function (Blueprint $table) {
            if (Schema::hasColumn('app_settings', 'page')) {
                $table->dropColumn('page');
            }
            if (Schema::hasColumn('app_settings', 'placeholder')) {
                $table->dropColumn('placeholder');
            }
        });
    }
};
