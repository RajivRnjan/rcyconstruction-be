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
        Schema::table('sites', function (Blueprint $table) {
            $table->string('boq_name')->nullable();
            $table->decimal('site_exp', 15, 2)->default(0);
            $table->decimal('np', 15, 2)->default(0);
            $table->decimal('agreement_value', 15, 2)->default(0);
            $table->decimal('upto_date_bill_value', 15, 2)->default(0);
            $table->decimal('balance_work_value', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['boq_name', 'site_exp', 'np', 'agreement_value', 'upto_date_bill_value', 'balance_work_value']);
        });
    }
};
