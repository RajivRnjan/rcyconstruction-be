<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_staff', function (Blueprint $table) {
            $table->string('month')->nullable();
            $table->decimal('prv_month_due_adv', 15, 2)->default(0);
            $table->decimal('total_working_day', 8, 2)->default(0);
            $table->decimal('current_month_salary', 15, 2)->default(0);
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->text('remark')->nullable();
        });

        Schema::table('site_subcontractors', function (Blueprint $table) {
            $table->date('date')->nullable();
            $table->integer('no_of_labour')->default(0);
            $table->text('work_details')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_staff', function (Blueprint $table) {
            $table->dropColumn(['month', 'prv_month_due_adv', 'total_working_day', 'current_month_salary', 'debit_amount', 'remark']);
        });

        Schema::table('site_subcontractors', function (Blueprint $table) {
            $table->dropColumn(['date', 'no_of_labour', 'work_details']);
        });
    }
};
