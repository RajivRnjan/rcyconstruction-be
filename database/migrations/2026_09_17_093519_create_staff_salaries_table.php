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
        Schema::create('staff_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('month')->nullable();
            $table->string('name');
            $table->decimal('salary', 15, 2)->default(0);
            $table->decimal('prv_month_due_adv', 15, 2)->default(0);
            $table->decimal('total_working_day', 5, 2)->default(0);
            $table->decimal('current_month_salary', 15, 2)->default(0);
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->text('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_salaries');
    }
};
