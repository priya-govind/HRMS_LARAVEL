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
          Schema::table('attendance', function (Blueprint $table) {
              $table->Integer('timesheet_slot')->unsigned()->default(0); 
              $table->Text('early_checkout_reason')->nullable()->default(null);
               });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
