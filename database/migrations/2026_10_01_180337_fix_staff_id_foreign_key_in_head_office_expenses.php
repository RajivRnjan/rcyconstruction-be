<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('staff_salaries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->dropForeign(['staff_id']);
        });
        
        Schema::table('head_office_expenses', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('site_staff')->nullOnDelete();
        });
    }
};
