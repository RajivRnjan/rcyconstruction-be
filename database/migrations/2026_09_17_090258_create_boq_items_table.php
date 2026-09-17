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
        Schema::create('boq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_sheet_id')->constrained('master_sheets')->onDelete('cascade');
            $table->string('item_name');
            $table->decimal('est_qnt', 10, 2)->default(0);
            $table->string('unit')->nullable();
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('work_done_qty', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boq_items');
    }
};
