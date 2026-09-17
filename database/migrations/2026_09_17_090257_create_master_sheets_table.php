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
        Schema::create('master_sheets', function (Blueprint $table) {
            $table->id();
            $table->string('project_name');
            $table->string('boq_name')->nullable();
            $table->decimal('site_exp', 15, 2)->default(0);
            $table->decimal('np', 15, 2)->default(0);
            $table->decimal('agreement_value', 15, 2)->default(0);
            $table->decimal('upto_date_bill_value', 15, 2)->default(0);
            $table->decimal('balance_work_value', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_sheets');
    }
};
