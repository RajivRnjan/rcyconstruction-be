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
        Schema::create('site_incharges', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->foreignId('project_id')->constrained('master_sheets')->onDelete('cascade');
            $table->string('name');
            $table->decimal('opening_bal', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->string('debit_account')->nullable();
            $table->decimal('exp', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_incharges');
    }
};
