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
        Schema::create('head_office_incomes', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->foreignId('project_id')->constrained('master_sheets')->onDelete('cascade');
            $table->string('client_name')->nullable();
            $table->string('mode_of_payment')->nullable();
            $table->foreignId('account_id')->constrained('accounts')->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('head_office_incomes');
    }
};
