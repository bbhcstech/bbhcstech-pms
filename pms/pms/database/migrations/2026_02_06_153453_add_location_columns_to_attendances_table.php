<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocationColumnsToAttendancesTable extends Migration
{
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Clock-in location
            $table->decimal('clock_in_latitude', 10, 8)->nullable()->after('clock_in');
            $table->decimal('clock_in_longitude', 11, 8)->nullable()->after('clock_in_latitude');
            $table->string('clock_in_address')->nullable()->after('clock_in_longitude');

            // Clock-out location
            $table->decimal('clock_out_latitude', 10, 8)->nullable()->after('clock_out');
            $table->decimal('clock_out_longitude', 11, 8)->nullable()->after('clock_out_latitude');
            $table->string('clock_out_address')->nullable()->after('clock_out_longitude');

            // Additional location tracking
            $table->string('work_from_type')->default('office')->change(); // office/wfh/field
            $table->decimal('total_hours', 5, 2)->nullable()->after('clock_out');
        });
    }

    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'clock_in_latitude',
                'clock_in_longitude',
                'clock_in_address',
                'clock_out_latitude',
                'clock_out_longitude',
                'clock_out_address',
                'total_hours'
            ]);
        });
    }
}
