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
        Schema::table('daily_report_staff', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });

        Schema::table('daily_report_staff', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('staff_salaries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_report_staff', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });

        Schema::table('daily_report_staff', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('site_staff')->onDelete('cascade');
        });
    }
};
