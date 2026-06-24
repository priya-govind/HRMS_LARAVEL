<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::table('projects', function (Blueprint $table) {
        $table->mediumText('proj_desc')->nullable()->after('proj_name');
        $table->date('start_date')->nullable()->after('proj_desc');
        $table->date('end_date')->nullable()->after('start_date');
    });
}

public function down(): void
{
    Schema::table('projects', function (Blueprint $table) {
        $table->dropColumn(['proj_desc', 'start_date', 'end_date']);
    });
}

};
