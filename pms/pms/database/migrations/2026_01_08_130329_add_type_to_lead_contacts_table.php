<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            $table->enum('type', ['lead', 'client'])->default('lead')->after('id');
            $table->timestamp('converted_at')->nullable()->after('updated_at');
            $table->foreignId('converted_by')->nullable()->after('converted_at')->constrained('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('lead_contacts', function (Blueprint $table) {
            $table->dropColumn(['type', 'converted_at', 'converted_by']);
        });
    }
};
